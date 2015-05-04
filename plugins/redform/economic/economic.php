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
	private $_db = null;

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

		$this->client = new RedformeconomicSoapClient($this->params);
		$this->_db = JFactory::getDbo();
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
	 * Get the client
	 *
	 * @return RedformeconomicSoapClient
	 */
	private function getClient()
	{
		return $this->client;
	}

	/**
	 * Create invoice from cart
	 *
	 * @param   int  $cartId  cart id
	 *
	 * @return array|bool
	 */
	public function rfCreateInvoice($cartId)
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
	public function getDebtor($data)
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
	public function getProduct($priceitem)
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
	 * @param   int  $invoiceId  invoice id
	 *
	 * @return bool
	 */
	public function bookInvoice($invoiceId)
	{
		$data = $this->getCartDetails();

		$bookingData = array();
		$bookingData['amount'] = $data->price + $data->vat;
		$bookingData['invoiceHandle'] = $invoiceId;
		$bookingData['currency_code'] = $data->currency;
		$bookingData['vat'] = $data->vat;
		$bookingData['name'] = $data->billing->fullname;
		$bookingData['uniqueid'] = $data->reference;
		$invoiceHandle = $this->client->bookInvoice($bookingData);

		if ($invoiceHandle)
		{
			$query = $this->_db->getQuery(true);

			$query->update('#__rwf_invoice')
				->set('booked = 1')
				->set('reference = ' . $this->_db->Quote($invoiceHandle->Number))
				->where('cart_id = ' . $this->_db->Quote($data->id));

			$this->_db->setQuery($query);

			if (!$this->_db->execute())
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
	 * @param   int  $invoiceId  invoice id
	 *
	 * @return string path on success, false otherwise
	 */
	public function rfStoreInvoice($invoiceId)
	{
		$fullpath = JPATH_ROOT . '/media/redform/invoices';

		if (!JFolder::exists($fullpath))
		{
			if (!JFolder::create($fullpath))
			{
				throw new RuntimeException(JText::_('CANNOT_CREATE_FOLDER') . ': ' . $fullpath);
			}
		}

		if (JFile::exists($fullpath . '/invoice' . $invoiceId . '.pdf'))
		{
			return $fullpath . '/invoice' . $invoiceId . '.pdf';
		}

		$helper = $this->getClient();
		$pdf = $helper->Invoice_GetPdf(array('Number' => $invoiceId));

		if ($pdf)
		{
			JFile::write($fullpath . '/invoice' . $invoiceId . '.pdf', $pdf);

			return $fullpath . '/invoice' . $invoiceId . '.pdf';
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
	public function rfSendInvoiceEmail($invoiceId)
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
	public function getCartIdFromReference($reference)
	{
		$query = $this->_db->getQuery(true);

		$query->select('c.id')
			->from('#__rwf_cart AS c')
			->where('reference = ' . $this->_db->quote($reference));

		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();

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
		$query = $this->_db->getQuery(true);

		$query->select('c.*')
			->from('#__rwf_cart AS c')
			->where('id = ' . $cartId);

		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();

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
		$query = $this->_db->getQuery(true);

		$query->select('pr.*, s.integration, s.submit_key')
			->from('#__rwf_payment_request AS pr')
			->join('INNER', '#__rwf_cart_item AS ci ON ci.payment_request_id = pr.id')
			->join('INNER', '#__rwf_submitters AS s ON s.id = pr.submission_id')
			->where('ci.cart_id = ' . $cartId);

		$this->_db->setQuery($query);
		$requests = $this->_db->loadObjectList('id');

		// Get Payment requests items
		$query = $this->_db->getQuery(true);

		$query->select('pri.*')
			->from('#__rwf_payment_request_item AS pri')
			->join('INNER', '#__rwf_payment_request AS pr ON pr.id = pri.payment_request_id')
			->join('INNER', '#__rwf_cart_item AS ci ON ci.payment_request_id = pr.id')
			->where('ci.cart_id = ' . $cartId);

		$this->_db->setQuery($query);
		$requestsItems = $this->_db->loadObjectList();

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
		$query = $this->_db->getQuery(true);

		$query->select('b.*')
			->from('#__rwf_billinginfo AS b')
			->where('b.cart_id = ' . $cartId);

		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();

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
		$query = $this->_db->getQuery(true);

		$query->select('i.*')
			->from('#__rwf_invoice AS i')
			->where('i.cart_id = ' . $cartId);

		$this->_db->setQuery($query);

		try
		{
			$res = $this->_db->loadObjectList();
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
				$this->_db->setQuery($query);
				$this->_db->execute();
			}
		}
	}
}
