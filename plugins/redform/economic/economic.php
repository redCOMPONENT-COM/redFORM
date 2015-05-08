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
	 * @var object
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
	 * @param   string  $cartReference  cart reference
	 *
	 * @return array|bool
	 */
	public function onAfterPaymentVerified($cartReference)
	{
		$cartId = $this->getCartIdFromReference($cartReference);

		try
		{
			return $this->rfCreateInvoice($cartId);
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			RdfHelperLog::simpleLog(sprintf('E-conomic error for cart reference %s: %s', $cartReference, $e->getMessage()));
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
		$res = $this->db->loadObjectList();

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
		$this->getCartDetails($invoice->cart_id);

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
	 * @param   int  $cartId  cart id
	 *
	 * @return array|bool
	 */
	private function rfCreateInvoice($cartId)
	{
		$this->cart = $this->getCartDetails($cartId);
		$data = $this->cart;

		if (!$data || $data->invoices)
		{
			// Do not create if already invoiced
			return false;
		}

		$helper = $this->getClient();

		$debtorhandle = $this->getDebtor($data);

		$invoiceData = array();
		$invoiceData['currency_code'] = $data->currency;
		$invoiceData['debtorHandle'] = $debtorhandle->Number;
		$invoiceData['vatzone'] = 'EU';
		$invoiceData['isvat'] = $data->vat > 0 ? 1 : 0;
		$invoiceData['user_info_id'] = $data->billing->email;
		$invoiceData['name'] = $data->billing->fullname;
		$invoiceData['text'] = $data->title;

		$invoice = $helper->createInvoice($invoiceData);

		if (!$invoice)
		{
			JError::raiseWarning(0, JText::_('PLG_REDFORM_INTEGRATION_ECONOMIC_ERROR_CREATING_INVOICE'));

			return false;
		}

		$helper->CurrentInvoice_SetOtherReference(array('currentInvoiceHandle' => $invoice, 'value' => $data->reference . '-' . $cartId));

		$i = 1;
		$total = 0;

		foreach ($data->paymentRequests as $pr)
		{
			foreach ($pr->items as $item)
			{
				$total += $item->price;
				$producthandle = $this->getProduct($item);
				$line = array();
				$line['InvoiceHandle'] = $invoice;
				$line['ProductHandle'] = $producthandle;
				$line['Number'] = $i++;
				$line['Description'] = $item->label;
				$line['Quantity'] = 1;
				$line['UnitNetPrice'] = $item->price;
				$line['DiscountAsPercent'] = 0;
				$line['UnitCostPrice'] = 0;
				$line['TotalMargin'] = 0;
				$line['MarginAsPercent'] = 0;

				$newCurrentInvoiceLineHandle = $helper->CurrentInvoiceLine_CreateFromData(array('data' => $line));
			}
		}

		if ($invoice)
		{
			// Update billing
			RTable::addIncludePath(__DIR__ . '/lib/table');
			$table = RTable::getInstance('Invoice', 'RedformTable');
			$table->cart_id = $data->id;
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
	 * @param   object  $data  data
	 *
	 * @return array
	 */
	private function getDebtor($data)
	{
		$helper = $this->getClient();
		$eco = array();

		// Check if the debtor already exists
		$debtorids = $helper->Debtor_FindByEmail(array('email' => $data->billing->email));

		if ($debtorids)
		{
			$eco['Number'] = $debtorids[0];
		}
		else
		{
			$eco['Number'] = 0;
		}

		$contact_name = (empty($data->billing->fullname) ? $data->billing->email : $data->billing->fullname);

		$eco['currency_code'] = $data->currency;
		$eco['vatzone'] = 'EU';
		$eco['email'] = $data->billing->email;

		if ($data->billing->iscompany)
		{
			$eco['name'] = (empty($data->billing->company) ? $contact_name : $data->billing->company);
		}
		else
		{
			$eco['name'] = $contact_name;
		}

		$eco['phone'] = $data->billing->phone;
		$eco['address'] = $data->billing->address;
		$eco['zipcode'] = $data->billing->zipcode;
		$eco['city'] = $data->billing->city;
		$eco['country'] = $data->billing->country;
		$eco['vatnumber'] = $data->billing->vatnumber;
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
	 * @return bool
	 */
	private function bookInvoice($invoiceNumber)
	{
		$data = $this->getCartDetails();

		$bookingData = array();
		$bookingData['amount'] = $data->price + $data->vat;
		$bookingData['invoiceHandle'] = $invoiceNumber;
		$bookingData['currency_code'] = $data->currency;
		$bookingData['vat'] = $data->vat;
		$bookingData['name'] = $data->billing->fullname;
		$bookingData['uniqueid'] = $data->reference;
		$invoiceHandle = $this->getClient()->bookInvoice($bookingData);

		if ($invoiceHandle)
		{
			$query = $this->db->getQuery(true);

			$query->update('#__rwf_invoice')
				->set('booked = 1')
				->set('reference = ' . $this->db->Quote($invoiceHandle->Number))
				->where('cart_id = ' . $this->db->Quote($data->id));

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

		return (bool) $invoiceHandle;
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

		$helper = $this->getClient();
		$pdf = $helper->Invoice_GetPdf(array('Number' => $invoiceReference));

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
		$data = $this->getCartDetails();

		// Make sure the invoice is stored indeed
		$path = $this->rfStoreInvoice($invoiceId);

		if (!$path)
		{
			return false;
		}

		/* Start the mailer object */
		$mailer = JFactory::getMailer();
		$mailer->From = $app->getCfg('mailfrom');
		$mailer->FromName = $app->getCfg('sitename');
		$mailer->AddReplyTo(array($app->getCfg('mailfrom'), $app->getCfg('sitename')));
		$mailer->addAttachment($path);
		$mailer->setSubject(JText::sprintf('PLG_REDFORM_ECONOMIC_SEND_INVOICE_SUBJECT', $data->title));
		$mailer->MsgHTML(JText::sprintf('PLG_REDFORM_ECONOMIC_SEND_INVOICE_BODY'));
		$mailer->addRecipient($data->billing->email);
		$mailer->send();
	}

	/**
	 * Get cart details, including billing and items and previous invoices
	 *
	 * @param   int  $cartId  cart id
	 *
	 * @return mixed
	 */
	private function getCartDetails($cartId = 0)
	{
		if ((!$this->cart) && $cartId)
		{
			$cart = $this->getCart($cartId);
			$cart->paymentRequests = $this->getPaymentRequests($cartId);
			$cart->billing = $this->getBilling($cartId);
			$cart->invoices = $this->getInvoices($cartId);
			$cart->title = $this->getCartTitle($cart);

			$this->cart = $cart;
		}

		return $this->cart;
	}

	/**
	 * Get cart id from reference
	 *
	 * @param   string  $reference  cart reference
	 *
	 * @return mixed
	 */
	private function getCartIdFromReference($reference)
	{
		$query = $this->db->getQuery(true);

		$query->select('c.id')
			->from('#__rwf_cart AS c')
			->where('reference = ' . $this->db->quote($reference));

		$this->db->setQuery($query);
		$res = $this->db->loadResult();

		return $res;
	}

	/**
	 * Return cart row from database
	 *
	 * @param   int  $cartId  cart id
	 *
	 * @return mixed
	 */
	private function getCart($cartId)
	{
		$query = $this->db->getQuery(true);

		$query->select('c.*')
			->from('#__rwf_cart AS c')
			->where('id = ' . $cartId);

		$this->db->setQuery($query);
		$res = $this->db->loadObject();

		return $res;
	}

	/**
	 * Return Payment requests and their items
	 *
	 * @param   int  $cartId  cart id
	 *
	 * @return mixed
	 */
	private function getPaymentRequests($cartId)
	{
		// Get Payment requests
		$query = $this->db->getQuery(true);

		$query->select('pr.*, s.integration, s.submit_key')
			->from('#__rwf_payment_request AS pr')
			->join('INNER', '#__rwf_cart_item AS ci ON ci.payment_request_id = pr.id')
			->join('INNER', '#__rwf_submitters AS s ON s.id = pr.submission_id')
			->where('ci.cart_id = ' . $cartId);

		$this->db->setQuery($query);
		$requests = $this->db->loadObjectList('id');

		// Get Payment requests items
		$query = $this->db->getQuery(true);

		$query->select('pri.*')
			->from('#__rwf_payment_request_item AS pri')
			->join('INNER', '#__rwf_payment_request AS pr ON pr.id = pri.payment_request_id')
			->join('INNER', '#__rwf_cart_item AS ci ON ci.payment_request_id = pr.id')
			->where('ci.cart_id = ' . $cartId);

		$this->db->setQuery($query);
		$requestsItems = $this->db->loadObjectList();

		foreach ($requestsItems as $item)
		{
			if (!isset($requests[$item->payment_request_id]->items))
			{
				$requests[$item->payment_request_id]->items = array();
			}

			$requests[$item->payment_request_id]->items[] = $item;
		}

		return $requests;
	}

	/**
	 * Return billing info row from database
	 *
	 * @param   int  $cartId  cart id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	private function getBilling($cartId)
	{
		$query = $this->db->getQuery(true);

		$query->select('b.*')
			->from('#__rwf_billinginfo AS b')
			->where('b.cart_id = ' . $cartId);

		$this->db->setQuery($query);
		$res = $this->db->loadObject();

		if (!$res->email)
		{
			throw new Exception('E-conomic: billing email is required');
		}

		return $res;
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
	 * @param   int  $cartDetails  cart details
	 *
	 * @return mixed
	 */
	private function getCartTitle($cartDetails)
	{
		JPluginHelper::importPlugin('redform_integration');
		$dispatcher = JDispatcher::getInstance();

		foreach ($cartDetails->paymentRequests as $pr)
		{
			if (!$pr->integration)
			{
				continue;
			}

			$integrationDetails = null;
			$dispatcher->trigger('getRFSubmissionPaymentDetailFields',
				array(
					$pr->integration,
					$pr->submit_key,
					&$integrationDetails
				)
			);

			if ($integrationDetails)
			{
				return $integrationDetails->title;
			}
		}

		return $this->params->get('default_cart_title', 'Payment for cart reference ' . $cartDetails->reference);
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
