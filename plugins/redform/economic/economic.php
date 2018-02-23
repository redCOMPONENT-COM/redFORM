<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.economic
 *
 * @copyright   Copyright (C) 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

RLoader::registerPrefix('Redformeconomic', __DIR__ . '/lib');

$redformLoader = JPATH_LIBRARIES . '/redform/bootstrap.php';

if (!file_exists($redformLoader))
{
	throw new Exception(JText::_('COM_REDFORM_LIB_INIT_FAILED'), 404);
}

include_once $redformLoader;

// Bootstraps redFORM
RdfBootstrap::bootstrap();

/**
 * Class plgRedformEconomic
 *
 * @package     Redform.Plugin
 * @subpackage  Redform.economic
 * @since       3.0
 */
class plgRedformEconomic extends JPlugin
{
	private $db = null;

	/**
	 * @var RedformeconomicSoapClient
	 */
	private $client = null;

	/**
	 * @var RdfEntityCart
	 */
	private $cart = null;

	/**
	 * constructor
	 *
	 * @param   object  $subject  subject
	 * @param   array   $params   params
	 */
	public function __construct($subject, $params)
	{
		parent::__construct($subject, $params);
		$this->loadLanguage();

		$this->db = JFactory::getDbo();
	}

	/**
	 * Handle onAfterPaymentVerified event
	 *
	 * @param   string  $cartId  cart id
	 *
	 * @return array|bool
	 */
	public function onAfterPaymentVerified($cartId)
	{
		try
		{
			return $this->rfCreateInvoice($cartId);
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			RdfHelperLog::simpleLog(sprintf('E-conomic error for cart id %s: %s', $cartId, $e->getMessage()));
		}
	}

	/**
	 * Create invoice from payment request
	 *
	 * use: index.php?option=com_ajax&group=redform&plugin=Createinvoice&format=raw&id=<prid>
	 *
	 * @return void
	 */
	public function onAjaxCreateinvoice()
	{
		$paymentrequestId = JFactory::getApplication()->input->getInt('id', 0);
		$force = JFactory::getApplication()->input->getInt('force', 0);

		try
		{
			$query = $this->db->getQuery(true);

			$query->select('c.id')
				->from('#__rwf_cart AS c')
				->join('INNER', '#__rwf_cart_item AS ci ON ci.cart_id = c.id')
				->where('ci.payment_request_id = ' . $paymentrequestId);

			$this->db->setQuery($query);
			$cartId = $this->db->loadResult();

			return $this->rfCreateInvoice($cartId, $force);
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			RdfHelperLog::simpleLog(sprintf('E-conomic error for payment request id %s: %s', $paymentrequestId, $e->getMessage()));
		}
	}

	public function onAjaxTestConnection()
	{
		try
		{
			$this->getClient();
			echo 'ok';
		}
		catch (Exception $e)
		{
			echo $e;
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Handle onPaymentAfterSave event from backend
	 *
	 * @param   string  $context  context
	 * @param   object  $table    table data
	 * @param   bool    $isNew    is new
	 *
	 * @return array|bool
	 */
	public function onPaymentAfterSave($context, $table, $isNew)
	{
		try
		{
			if (strstr($context, 'com_redform') && $table->paid && $isNew)
			{
				return $this->rfCreateInvoice($table->cart_id);
			}
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			RdfHelperLog::simpleLog(sprintf('E-conomic error for cart id %s: %s', $table->cart_id, $e->getMessage()));
		}
	}

	/**
	 * Handle onAfterPaymentVerified event
	 *
	 * @param   array  $submitterIds  submitter Ids
	 * @param   array  &$invoices     invoices
	 *
	 * @return bool
	 */
	public function onGetSubmittersInvoices(array $submitterIds, &$invoices)
	{
		if (empty($submitterIds))
		{
			return true;
		}

		JArrayHelper::toInteger($submitterIds);

		$query = $this->db->getQuery(true)
			->select('i.*, ci.payment_request_id, pr.submission_id')
			->from('#__rwf_invoice AS i')
			->join('INNER', '#__rwf_cart_item AS ci ON ci.cart_id = i.cart_id')
			->join('INNER', '#__rwf_payment_request AS pr ON pr.id = ci.payment_request_id')
			->where('pr.submission_id IN(' . implode(", ", $submitterIds) . ')');

		$this->db->setQuery($query);

		try
		{
			$res = $this->db->loadObjectList();
		}
		catch (Exception $e)
		{
			$this->updateDb();
			$res = $this->db->loadObjectList();
		}

		$invoices = array();

		foreach ($res as $result)
		{
			if (!isset($invoices[$result->submission_id]))
			{
				$invoices[$result->submission_id] = array();
			}

			if (!isset($invoices[$result->submission_id][$result->payment_request_id]))
			{
				$invoices[$result->submission_id][$result->payment_request_id] = array();
			}

			$invoices[$result->submission_id][$result->payment_request_id][] = $result;
		}

		return true;
	}

	/**
	 * Handle onAjaxGetpdf event
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function onAjaxBook()
	{
		$app = JFactory::getApplication();
		$invoiceId = $app->input->getInt('id', 0);
		$reference = $app->input->get('reference');
		$return = $app->input->get('return');

		$invoice = $this->confirmReference($invoiceId, $reference);
		$this->getCart($invoice->cart_id);

		$this->bookInvoice($reference);

		if ($return)
		{
			$app->redirect(base64_decode($return));
		}
		else
		{
			$app->redirect('index.php?option=com_redform&view=submitters');
		}
	}

	/**
	 * Handle onAjaxGetpdf event
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function onAjaxTurninvoice()
	{
		$app = JFactory::getApplication();
		$invoiceId = $app->input->getInt('id', 0);
		$reference = $app->input->get('reference');
		$return = $app->input->get('return');

		$invoice = $this->confirmReference($invoiceId, $reference);
		$this->getCart($invoice->cart_id);

		$this->turnInvoice($reference);

		if ($return)
		{
			$app->redirect(base64_decode($return));
		}
		else
		{
			$app->redirect('index.php?option=com_redform&view=submitters');
		}
	}

	/**
	 * Handle onAjaxGetpdf event
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function onAjaxGetpdf()
	{
		$app = JFactory::getApplication();
		$invoiceId = $app->input->getInt('id', 0);
		$reference = $app->input->get('reference');

		$this->confirmReference($invoiceId, $reference);

		if (!$path = $this->rfStoreInvoice($reference))
		{
			throw new Exception('Invoice not stored');
		}

		$filename = 'invoice_' . $reference . '.pdf';
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Content-Transfer-Encoding: binary');
		header('Accept-Ranges: bytes');
		@readfile($path);

		$app->close();
	}

	/**
	 * Install overrides and sql
	 *
	 * @return void
	 */
	public function onAjaxInstallEconomic()
	{
		$htmlTemplatePath = JPATH_ADMINISTRATOR . '/templates/' . JFactory::getApplication()->getTemplate() . '/html';

		try
		{
			$files = JFolder::files(__DIR__ . '/overrides/admin/html', null, true, true);

			foreach ($files as $file)
			{
				$relPath = $this->getRelPath(__DIR__ . '/overrides/admin/html', $file);

				if (file_exists($htmlTemplatePath . $relPath))
				{
					rename($htmlTemplatePath . $relPath, $htmlTemplatePath . $relPath . JFactory::getDate()->format('_Y_m_d_h_i'));

					JFactory::getApplication()->enqueueMessage(
						JText::sprintf('PLG_REDFORM_INTEGRATION_ECONOMIC_INSTALL_REPLACE_FILE', $htmlTemplatePath . $relPath)
					);
				}
				else
				{
					JFolder::create(dirname($htmlTemplatePath . $relPath));

					JFactory::getApplication()->enqueueMessage(
						JText::sprintf('PLG_REDFORM_INTEGRATION_ECONOMIC_INSTALL_ADD_FILE', $htmlTemplatePath)
					);
				}

				if (!JFile::copy($file, $htmlTemplatePath . $relPath))
				{
					$error = error_get_last();
					JFactory::getApplication()->enqueueMessage($error['message'], 'error');
				}
			}

			$this->updateDb();
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage());
		}

		JFactory::getApplication()->redirect($_SERVER['HTTP_REFERER']);
	}

	private function getRelPath($basePath, $file)
	{
		return substr($file, strlen($basePath));
	}

	/**
	 * Check that ivoice id matches reference
	 *
	 * @param   int     $invoiceId  invoice id
	 * @param   string  $reference  reference
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	private function confirmReference($invoiceId, $reference)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__rwf_invoice')
			->where('reference = ' . $db->quote($reference))
			->where('id = ' . $invoiceId);

		$db->setQuery($query);

		if (!$invoice = $db->loadObject())
		{
			throw new Exception('Invoice not found');
		}

		return $invoice;
	}

	/**
	 * Get the client
	 *
	 * @return RedformeconomicSoapClient
	 */
	private function getClient()
	{
		if (!$this->client)
		{
			$this->client = new RedformeconomicSoapClient($this->params);
		}

		return $this->client;
	}

	/**
	 * Create invoice from cart
	 *
	 * @param   int   $cartId  cart id
	 * @param   bool  $force   force creation even if there is already a reference for this cart
	 *
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	private function rfCreateInvoice($cartId, $force = false)
	{
		if (!$this->cart = $this->getCart($cartId))
		{
			throw new Exception('Cart details not found');
		}

		$cart = $this->cart;

		if ($this->getInvoices($cartId) && !$force)
		{
			// Do not create if already invoiced
			return false;
		}

		$helper = $this->getClient();

		$debtorhandle = $this->getDebtor($cart);

		$invoiceData = array();
		$invoiceData['currency_code'] = $cart->currency;
		$invoiceData['debtorHandle'] = $debtorhandle->Number;
		$invoiceData['vatzone'] = 'EU';
		$invoiceData['isvat'] = $cart->vat > 0 ? 1 : 0;
		$invoiceData['user_info_id'] = $cart->getBilling()->email;
		$invoiceData['name'] = $cart->getBilling()->fullname;
		$invoiceData['text'] = $this->getCartTitle();

		$invoice = $helper->createInvoice($invoiceData);

		if (!$invoice)
		{
			JError::raiseWarning(0, JText::_('PLG_REDFORM_INTEGRATION_ECONOMIC_ERROR_CREATING_INVOICE'));

			return false;
		}

		$helper->CurrentInvoice_SetOtherReference(
			array(
				'currentInvoiceHandle' => $invoice,
				'value' => ($cart->invoice_id ?: $cart->id)
			)
		);

		$i = 1;

		foreach ($cart->getPaymentRequests() as $pr)
		{
			foreach ($pr->getItems() as $item)
			{
				$producthandle = $this->getProduct($item);
				$line = array();
				$line['InvoiceHandle'] = $invoice;
				$line['ProductHandle'] = $producthandle;
				$line['Number'] = $i++;
				$line['Description'] = $item->label;
				$line['Quantity'] = $item->price > 0 ? 1 : - 1;
				$line['UnitNetPrice'] = abs($item->price);
				$line['DiscountAsPercent'] = 0;
				$line['UnitCostPrice'] = 0;
				$line['TotalMargin'] = 0;
				$line['MarginAsPercent'] = 0;

				$newCurrentInvoiceLineHandle = $helper->CurrentInvoiceLine_CreateFromData(array('data' => $line));
			}
		}

		if ($invoice)
		{
			// Update table
			RTable::addIncludePath(__DIR__ . '/lib/table');
			$table = RTable::getInstance('Invoice', 'RedformTable');
			$table->cart_id = $cart->id;
			$table->date = JFactory::getDate()->toSql();
			$table->reference = $invoice->Id;
			$table->store();
		}

		if ($this->params->get('book_invoice'))
		{
			$this->bookInvoice($invoice->Id);
		}

		return $invoice;
	}

	/**
	 * Get Debtor, creating if needed
	 *
	 * @param   RdfEntityCart  $cart  cart
	 *
	 * @return array
	 */
	private function getDebtor($cart)
	{
		$helper = $this->getClient();
		$eco = array();

		// Check if the debtor already exists
		$debtorids = $helper->Debtor_FindByEmail(array('email' => $cart->getBilling()->email));

		if ($debtorids)
		{
			$eco['Number'] = $debtorids[0];
		}
		else
		{
			$eco['Number'] = 0;
		}

		$contact_name = $cart->getBilling()->fullname ?: $cart->getBilling()->email;

		$eco['currency_code'] = $cart->currency;
		$eco['vatzone'] = 'EU';
		$eco['email'] = $cart->getBilling()->email;

		if ($this->params->get('force_company_as_debtor') || $cart->getBilling()->iscompany)
		{
			$eco['name'] = $cart->getBilling()->company ?: $contact_name;
		}
		else
		{
			$eco['name'] = $contact_name;
		}

		$eco['phone'] = $cart->getBilling()->phone;
		$eco['address'] = $cart->getBilling()->address;
		$eco['zipcode'] = $cart->getBilling()->zipcode;
		$eco['city'] = $cart->getBilling()->city;
		$eco['country'] = $cart->getBilling()->country;
		$eco['vatnumber'] = $cart->getBilling()->vatnumber;
		$ecodebtorNumber = $helper->storeDebtor($eco);

		return $ecodebtorNumber;
	}

	/**
	 * get product from price item object
	 *
	 * @param   object  $priceitem  price item
	 *
	 * @return object product handle
	 */
	private function getProduct($priceitem)
	{
		$helper = $this->getClient();
		$eco = array();

		$matches = null;

		if (preg_match("/([0-9]+)-(.*)/", $priceitem->sku, $matches))
		{
			$eco['productgroup'] = $matches[1];
			$eco['number'] = $matches[2];
		}
		elseif ($priceitem->sku)
		{
			$eco['number'] = $priceitem->sku;
		}
		else
		{
			$eco['number'] = $priceitem->id;
		}

		$eco['product_name'] = $priceitem->label;
		$eco['product_price'] = $priceitem->price;
		$resHandle = $helper->Product_FindByNumber($eco['number']);

		if ($resHandle)
		{
			$eco['handle'] = $resHandle;
		}
		else
		{
			$eco['handle'] = 0;
		}

		$ecoProductNumber = $helper->storeProduct($eco);

		return $ecoProductNumber;
	}

	/**
	 * Book an invoice
	 *
	 * @param   string  $invoiceNumber  invoice number
	 *
	 * @return object invoice handle
	 */
	private function bookInvoice($invoiceNumber)
	{
		$cart = $this->getCart();

		$bookingData = array();
		$bookingData['amount'] = $cart->price + $cart->vat;
		$bookingData['invoiceHandle'] = $invoiceNumber;
		$bookingData['currency_code'] = $cart->currency;
		$bookingData['vat'] = $cart->vat;
		$bookingData['name'] = $cart->getBilling()->fullname;
		$bookingData['uniqueid'] = ($cart->getIntegrationInfo()->uniqueid ?: $cart->reference) . ' / ' . $cart->id;
		$invoiceHandle = $this->getClient()->bookInvoice($bookingData);

		if ($invoiceHandle)
		{
			$query = $this->db->getQuery(true);

			$query->update('#__rwf_invoice')
				->set('booked = 1')
				->set('reference = ' . $this->db->Quote($invoiceHandle->Number))
				->where('reference = ' . $this->db->Quote($invoiceNumber));

			$this->db->setQuery($query);

			if (!$this->db->execute())
			{
				throw new RuntimeException(JText::_('PLG_REDFORM_INTEGRATION_ECONOMIC_ERROR_UPDATING_BOOKED_STATUS'));
			}

			$this->rfStoreInvoice($invoiceHandle->Number);

			if ($this->params->get('send_invoice'))
			{
				$this->rfSendInvoiceEmail($invoiceHandle->Number);
			}
		}

		return $invoiceHandle;
	}

	/**
	 * Book an invoice
	 *
	 * @param   string  $reference  invoice number
	 *
	 * @return bool
	 */
	private function turnInvoice($reference)
	{
		$client = $this->getClient();
		$cart = $this->getCart();

		$invoicehandle = array('Number' => $reference);
		$data = $client->Invoice_GetData(array('entityHandle' => $invoicehandle));

		if (!$data)
		{
			throw new RuntimeException(JText::_('PLG_REDFORM_INTEGRATION_ECONOMIC_INVOICE_NOT_FOUND'));
		}

		$lines = $client->Invoice_GetLines(array('invoiceHandle' => $invoicehandle));

		$currentInvoiceHandle = $client->CurrentInvoice_Create($data->DebtorHandle);
		$client->client->CurrentInvoice_SetCurrency(array('currentInvoiceHandle' => $currentInvoiceHandle, 'valueHandle' => $data->CurrencyHandle));
		$client->client->CurrentInvoice_SetTermOfPayment(array('currentInvoiceHandle' => $currentInvoiceHandle, 'valueHandle' => $data->TermOfPaymentHandle));
		$client->client->CurrentInvoice_SetIsVatIncluded(array('currentInvoiceHandle' => $currentInvoiceHandle, 'value' => $data->IsVatIncluded));
		$client->client->CurrentInvoice_SetTextLine1(array('currentInvoiceHandle' => $currentInvoiceHandle, 'value' => $data->TextLine1));
		$client->client->CurrentInvoice_SetOtherReference(array('currentInvoiceHandle' => $currentInvoiceHandle, 'value' => $data->OtherReference . '-rev'));

		foreach ($lines as $l)
		{
			$ldata = $client->client->InvoiceLine_GetData(array('entityHandle' => $l))->InvoiceLine_GetDataResult;
			$ldata->Quantity = - ($ldata->Quantity);
			$ldata->InvoiceHandle = $currentInvoiceHandle;
			$ldata->Id = $currentInvoiceHandle->Id;
			$ldata->Number = $l->Number;
			$ldata->TotalMargin = 0;
			$ldata->MarginAsPercent = 0;
			$client->CurrentInvoiceLine_CreateFromData(array('data' => $ldata));
		}

		// Add new table row
		RTable::addIncludePath(__DIR__ . '/lib/table');
		$table = RTable::getInstance('Invoice', 'RedformTable');
		$table->cart_id = $cart->id;
		$table->date = JFactory::getDate()->toSql();
		$table->reference = $currentInvoiceHandle->Id;
		$table->store();

		// Book it
		$bookedHandle = $this->bookInvoice($currentInvoiceHandle->Id);

		if ($bookedHandle)
		{
			// Updated turned invoice
			$table = RTable::getInstance('Invoice', 'RedformTable');
			$table->reset();
			$table->load(array('reference' => $reference));
			$table->turned = $bookedHandle->Number;
			$table->store();
		}

		return (bool) true;
	}

	/**
	 * store invoices
	 *
	 * @param   string  $invoiceReference  invoice reference
	 *
	 * @return string path on success, false otherwise
	 */
	private function rfStoreInvoice($invoiceReference)
	{
		$relPath = $this->params->get('invoices_folder', 'images/economic/invoices');
		$relPath = ($relPath[0] == '/' || $relPath[0] == '\\') ? $relPath : '/' . $relPath;
		$fullpath = JPATH_ROOT . $relPath;

		if (!JFolder::exists($fullpath))
		{
			if (!JFolder::create($fullpath))
			{
				throw new RuntimeException(JText::_('CANNOT_CREATE_FOLDER') . ': ' . $fullpath);
			}
		}

		if (JFile::exists($fullpath . '/invoice' . $invoiceReference . '.pdf'))
		{
			return $fullpath . '/invoice' . $invoiceReference . '.pdf';
		}

		$client = $this->getClient();
		$pdf = $client->Invoice_GetPdf(array('Number' => $invoiceReference));

		if ($pdf)
		{
			JFile::write($fullpath . '/invoice' . $invoiceReference . '.pdf', $pdf);

			return $fullpath . '/invoice' . $invoiceReference . '.pdf';
		}

		return false;
	}

	/**
	 * send invoice to email from billing address
	 *
	 * @param   int  $invoiceId  invoiceId
	 *
	 * @return boolean
	 */
	private function rfSendInvoiceEmail($invoiceId)
	{
		$app = JFactory::getApplication();
		$cart = $this->getCart();

		// Make sure the invoice is stored indeed
		$path = $this->rfStoreInvoice($invoiceId);

		if (!$path)
		{
			return false;
		}

		/* Start the mailer object */
		$mailer = RdfHelper::getMailer();
		$mailer->From = $app->getCfg('mailfrom');
		$mailer->FromName = $app->getCfg('sitename');
		$mailer->AddReplyTo(array($app->getCfg('mailfrom'), $app->getCfg('sitename')));
		$mailer->addAttachment($path);
		$mailer->setSubject(JText::sprintf('PLG_REDFORM_ECONOMIC_SEND_INVOICE_SUBJECT', $this->getCartTitle()));
		$mailer->MsgHTML(JText::sprintf('PLG_REDFORM_ECONOMIC_SEND_INVOICE_BODY'));
		$mailer->addRecipient($cart->getBilling()->email);
		$mailer->send();
	}

	/**
	 * Return cart row from database
	 *
	 * @param   int  $cartId  cart id
	 *
	 * @return RdfEntityCart
	 */
	private function getCart($cartId = 0)
	{
		if (!$this->cart || ($cartId && $this->cart->id != $cartId))
		{
			$this->cart = RdfEntityCart::load($cartId);
		}

		return $this->cart;
	}

	/**
	 * Return Invoices info row from database
	 *
	 * @param   int      $cartId         cart id
	 * @param   boolean  $createOnError  create table if not found
	 *
	 * @return mixed
	 */
	private function getInvoices($cartId, $createOnError = true)
	{
		$query = $this->db->getQuery(true);

		$query->select('i.*')
			->from('#__rwf_invoice AS i')
			->where('i.cart_id = ' . $cartId);

		$this->db->setQuery($query);

		try
		{
			$res = $this->db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			// Try to create table, if allowed
			if ($createOnError)
			{
				$this->updateDb();

				return $this->getInvoices($cartId, false);
			}

			// Rethrow
			throw $e;
		}

		return $res;
	}

	/**
	 * Return a title for invoice
	 *
	 * @return mixed
	 */
	private function getCartTitle()
	{
		if ($this->cart)
		{
			return $this->cart->getIntegrationInfo()->title ?
				$this->cart->getIntegrationInfo()->title :
				$this->params->get('default_cart_title', 'Payment for cart reference ' . $this->cart->reference);
		}

		return false;
	}

	/**
	 * Create the invoice table
	 *
	 * @return void
	 */
	private function updateDb()
	{
		// Create an array of queries from the sql file
		$buffer = file_get_contents(__DIR__ . '/sql/economic.sql');
		$queries = JInstallerHelper::splitSql($buffer);

		if (count($queries) == 0)
		{
			// No queries to process
			return;
		}

		// Process each query in the $queries array (split out of sql file).
		foreach ($queries as $query)
		{
			$query = trim($query);

			if ($query != '' && $query{0} != '#')
			{
				$this->db->setQuery($query);
				$this->db->execute();
			}
		}
	}
}
