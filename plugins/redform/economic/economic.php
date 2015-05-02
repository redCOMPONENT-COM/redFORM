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

		return $this->rfCreateInvoice($cartId);
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
		$data = $this->getCartDetails($cartId);

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
		$invoiceData['isvat'] = $data->vat ? 1 : 0;
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
				$producthandle = $this->createProduct($item);
				$line = array();
				$line['InvoiceHandle'] = $invoice;
				$line['ProductHandle'] = $producthandle;
				$line['Number'] = $i++;
				$line['Description'] = $item->label;
				$line['Quantity'] = 1;
				$line['UnitNetPrice'] = $item->price + $item->vat;
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
	 * creates product from price item object
	 *
	 * @param   object  $priceitem  price item
	 *
	 * @return object product handle
	 */
	public function createProduct($priceitem)
	{
		$helper = $this->getClient();
		$eco = array();

		$matches = null;

		if (preg_match("/([0-9]+)-(.*)/", $priceitem->sku, $matches))
		{
			$eco['productgroup'] = $matches[1];
			$eco['number'] = $matches[2];
		}
		else
		{
			$eco['number'] = $priceitem->sku;
		}

		$eco['product_name'] = $priceitem->label;
		$eco['product_price'] = $priceitem->price;
		$resHandle = $helper->Product_FindByNumber($priceitem->id);

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

	public function paymentReceived($submit_key)
	{
	}

	public function rfBookInvoice($paymentrequest_id)
	{
		$db = &Jfactory::getDBO();

		$query = ' SELECT b.*, b.fullname as name, pr.*, b.uniqueid '
			. ' FROM #__rwf_payments_requests AS pr '
			. ' INNER JOIN #__rwf_billings AS b ON pr.submit_key = b.submit_key '
			. ' WHERE pr.id = ' . $db->Quote($paymentrequest_id);
		$db->setQuery($query);
		$data = $db->loadObject();

		if (!$data)
		{
			JError::raiseWarning(0, JText::_('PLG_REDFORM_INTEGRATION_ECONOMIC_ERROR_NO_BILLING_INFO'));
			return false;
		}

		if ($data->booked)
		{
			JError::raiseNotice(0, JText::_('PLG_REDFORM_INTEGRATION_ECONOMIC_ALREADY_BOOKED'));
			return false;
		}
		$data->amount = $data->total + $data->vat;

		$helper = $this->getClient();
		$invoice = $helper->CurrentInvoice_FindByOtherReference($data->submit_key . '-' . $paymentrequest_id);
		if (!$invoice || !count($invoice))
		{
			$invoice = $this->rfCreateInvoice($paymentrequest_id);
			if (!$invoice)
			{
				return false;
			}
		}
		else
		{
			$invoice = $invoice[0];
		}
		$bookingData = get_object_vars($data);

		$bookingData['invoiceHandle'] = $invoice->Id;
		$bookingData['currency_code'] = $data->currency;
		$bookingData['vat'] = $data->vat;
		$invoiceHandle = $helper->bookInvoice($bookingData);

		if ($invoiceHandle)
		{
			$query = ' UPDATE #__rwf_payments_requests SET booked = 1 '
				. ' WHERE id = ' . $db->Quote($paymentrequest_id);
			$db->setQuery($query);
			if (!$data = $db->query())
			{
				Jerror::raiseWarning(0, JText::_('PLG_REDFORM_INTEGRATION_ECONOMIC_ERROR_UPDATING_BOOKED_STATUS'));
			}

			$query = sprintf(' INSERT INTO #__rwf_invoices (submit_key, paymentrequest_id, name, reference, booked, date) '
				. ' VALUES (%s, %s, %s, %s, %d, NOW())',
				$db->Quote($data->submit_key),
				$db->Quote($paymentrequest_id),
				$db->Quote(JText::_('Invoice')),
				$db->Quote($invoiceHandle->Number),
				1
			);
			$db->setQuery($query);
			if (!$data = $db->query())
			{
				Jerror::raiseWarning(0, JText::_('PLG_REDFORM_INTEGRATION_ECONOMIC_ERROR_UPDATING_BOOKED_STATUS'));
			}

			// send email to billing address
			$this->_rfSendInvoiceEmail($invoiceHandle->Number);
		}
		return (bool) $invoiceHandle;
	}

	public function rfTurnInvoice($reference)
	{
		$db = &Jfactory::getDBO();

		$helper = $this->getClient();
		$invoicehandle = array('Number' => $reference);
		$data = $helper->Invoice_GetData(array('entityHandle' => $invoicehandle));
		if (!$data)
		{
			JError::raiseNotice(0, JText::_('PLG_REDFORM_INTEGRATION_ECONOMIC_INVOICE_NOT_FOUND'));
			return false;
		}
		$lines = $helper->Invoice_GetLines(array('invoiceHandle' => $invoicehandle));

//		echo '<pre>';print_r($data); echo '</pre>';exit;
		$currentInvoiceHandle = $helper->client->CurrentInvoice_Create(array('debtorHandle' => $data->DebtorHandle))->CurrentInvoice_CreateResult;
		$helper->client->CurrentInvoice_SetCurrency(array('currentInvoiceHandle' => $currentInvoiceHandle, 'valueHandle' => $data->CurrencyHandle));
		$helper->client->CurrentInvoice_SetTermOfPayment(array('currentInvoiceHandle' => $currentInvoiceHandle, 'valueHandle' => $data->TermOfPaymentHandle));
		$helper->client->CurrentInvoice_SetIsVatIncluded(array('currentInvoiceHandle' => $currentInvoiceHandle, 'value' => $data->IsVatIncluded));
		$helper->client->CurrentInvoice_SetTextLine1(array('currentInvoiceHandle' => $currentInvoiceHandle, 'value' => $data->TextLine1));
		$helper->client->CurrentInvoice_SetOtherReference(array('currentInvoiceHandle' => $currentInvoiceHandle, 'value' => $data->OtherReference . '-rev'));

		foreach ($lines as $l)
		{
			$ldata = $helper->client->InvoiceLine_GetData(array('entityHandle' => $l))->InvoiceLine_GetDataResult;
			$ldata->Quantity = -($ldata->Quantity);
			$ldata->InvoiceHandle = $currentInvoiceHandle;
			$ldata->Id = $currentInvoiceHandle->Id;
			$ldata->Number = $l->Number;
			$ldata->TotalMargin = 0;
			$ldata->MarginAsPercent = 0;
			$helper->client->CurrentInvoiceLine_CreateFromData(array('data' => $ldata));
		}

		// book it
		$bookingData = array();
		$bookingData['invoiceHandle'] = $currentInvoiceHandle->Id;
		$invoiceHandle = $helper->bookInvoice($bookingData);

		if ($invoiceHandle)
		{
			// find out paymentrequest_id
			$query = ' SELECT paymentrequest_id '
				. ' FROM #__rwf_invoices '
				. ' WHERE reference = ' . $db->Quote($reference);
			$this->_db->setQuery($query);
			$res = $this->_db->loadResult();

			$query = sprintf(' INSERT INTO #__rwf_invoices (paymentrequest_id, name, reference, note, booked, date) '
				. ' VALUES (%s, %s, %s, %s,%d, NOW())',
				$db->Quote($res),
				$db->Quote(JText::_('Turned')),
				$db->Quote($invoiceHandle->Number),
				$db->Quote(JText::_('Turned invoice') . ' ' . $reference),
				1
			);
			$db->setQuery($query);
			if (!$db->query())
			{
				Jerror::raiseWarning(0, JText::_('PLG_REDFORM_INTEGRATION_ECONOMIC_ERROR_STORING_TURNED_INVOICE_ROW'));
			}

			// set invoice as turned
			$query = ' UPDATE #__rwf_invoices AS i SET turned = ' . $db->Quote($invoiceHandle->Number) . ' WHERE reference = ' . $db->Quote($reference);
			$this->_db->setQuery($query);
			if (!$db->query())
			{
				Jerror::raiseWarning(0, JText::_('PLG_REDFORM_INTEGRATION_ECONOMIC_ERROR_UPDATING_TURNED_STATUS'));
			}
		}

		// send email to billing address
		$this->_rfSendInvoiceEmail($invoiceHandle->Number);

		return (bool) true;
	}

	public function rfUpdateBilling($data, $additems, $removeitems)
	{
		$helper = $this->getClient();

		if (!$data->booked) // edit the current invoice
		{
			$invoice = false;
			if ($data->invoice)
			{
				// check that the invoice exists in e-conomic
				$invoices = $helper->CurrentInvoice_GetAll();
				foreach ((array) $invoices as $i)
				{
					if ($i->Id == $data->invoice)
					{
						$invoice = $i;
						break;
					}
				}

				if ($invoice)
				{
					// remove all lines
					$lines = $helper->CurrentInvoice_GetLines(array('currentInvoiceHandle' => $invoice));
					foreach ($lines as $l)
					{
						$helper->client->CurrentInvoiceLine_Delete(array('currentInvoiceLineHandle' => $l));
					}
				}
			}

			if (!$invoice)
			{
				$debtorhandle = $this->getDebtor($data);

				$invoiceData = get_object_vars($data);
				//$invoice = $helper->CurrentInvoice_Create($debtorhandle);
				$invoiceData['currency_code'] = $data->currency;
				$invoiceData['debtorHandle'] = $debtorhandle->Number;
				$invoiceData['vatzone'] = 'EU';
				$invoiceData['isvat'] = $data->vatexempt ? 0 : 1;
				$invoiceData['user_info_id'] = $data->email;
				$invoiceData['name'] = $data->fullname;

				$invoice = $helper->createInvoice($invoiceData);
				$helper->CurrentInvoice_SetOtherReference(array('currentInvoiceHandle' => $invoice, 'value' => $data->submit_key));
			}

			$priceitems = RedFormCore::getSubmissionPriceItems($data->submit_key);
			$i = 1;
			foreach ($priceitems as $item)
			{
				$producthandle = $this->createProduct($item);
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
			if ($invoice)
			{
				// update billing
				$table = &JTable::getInstance('billings', 'RedformTable');
				$table->load($data->id);
				$table->invoice = $invoice->Id;
				$table->store();
			}

			return $invoice;
		}
		else // already booked, so create an invoice with just the difference
		{
			$debtorhandle = $this->getDebtor($data);

			$invoiceData = get_object_vars($data);
			//$invoice = $helper->CurrentInvoice_Create($debtorhandle);
			$invoiceData['currency_code'] = $data->currency;
			$invoiceData['debtorHandle'] = $debtorhandle->Number;
			$invoiceData['vatzone'] = 'EU';
			$invoiceData['isvat'] = $data->vatexempt ? 0 : 1;
			$invoiceData['user_info_id'] = $data->email;
			$invoiceData['name'] = $data->fullname;

			$invoice = $helper->createInvoice($invoiceData);
			$helper->CurrentInvoice_SetOtherReference(array('currentInvoiceHandle' => $invoice, 'value' => $data->submit_key));

			$i = 1;
			foreach ($additems as $item)
			{
				$producthandle = $this->createProduct($item);
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
			foreach ($removeitems as $item)
			{
				$producthandle = $this->createProduct($item);
				$line = array();
				$line['InvoiceHandle'] = $invoice;
				$line['ProductHandle'] = $producthandle;
				$line['Number'] = $i++;
				$line['Description'] = $item->label;
				$line['Quantity'] = -1;
				$line['UnitNetPrice'] = $item->price;
				$line['DiscountAsPercent'] = 0;
				$line['UnitCostPrice'] = 0;
				$line['TotalMargin'] = 0;
				$line['MarginAsPercent'] = 0;

				$newCurrentInvoiceLineHandle = $helper->CurrentInvoiceLine_CreateFromData(array('data' => $line));
			}

			// book it
			$bookingData = array();
			$bookingData['invoiceHandle'] = $invoice->Id;
			$invoiceHandle = $helper->bookInvoice($bookingData);

			if ($invoiceHandle)
			{
				$db = &Jfactory::getDBO();

				$query = sprintf(' INSERT INTO #__rwf_invoices (submit_key, name, reference, note, booked, date) '
					. ' VALUES (%s, %s, %s, %s,%d, NOW())',
					$db->Quote($data->submit_key),
					$db->Quote(JText::_('Difference')),
					$db->Quote($invoiceHandle->Number),
					$db->Quote(JText::_('Price changed') . ' ' . $reference),
					1
				);
				$db->setQuery($query);
				if (!$db->query())
				{
					Jerror::raiseWarning(0, JText::_('PLG_REDFORM_INTEGRATION_ECONOMIC_ERROR_UPDATING_PRICE_CHANGE'));
				}

				// send email to billing address
				$this->_rfSendInvoiceEmail($invoiceHandle->Number);
			}
		}
	}

	public function rfGetUserInvoices(&$invoices, $userid = null)
	{
		if (!$userid)
		{
			$user = &JFactory::getUser();
		}
		else
		{
			$user = &JFactory::getUser($userid);
		}
		if (!$user->get('id'))
		{
			return false;
		}

		$query = ' SELECT b.* '
			. ' FROM #__rwf_billings AS b '
			. ' WHERE b.user_id = ' . $this->_db->Quote($user->get('id'))
			. '   AND b.invoice > 0 '
			. ' GROUP BY b.id '
			. ' ORDER BY b.id DESC';

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		foreach ($res as $r)
		{
			$invoices[] = $r;
		}
		return true;
	}

	public function rfGetPdfInvoice(&$pdf, $submit_key, $reference)
	{
		if (!$submit_key || !$reference)
		{
			return false;
		}

		// make sure the invoice reference matches the key (security)
		$query = ' SELECT i.id, i.reference, i.booked '
			. ' FROM #__rwf_invoices AS i '
			. ' INNER JOIN #__rwf_payments_requests AS pr on pr.id = i.paymentrequest_id '
			. ' WHERE pr.submit_key = ' . $this->_db->Quote($submit_key)
			. '   AND i.reference = ' . $this->_db->Quote($reference);
		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();
		if (!$res)
		{
			JError::raiseError('403', JText::_('COM_REDFORM_ECONOMIC_INVOICE_ACCESS_NOT_ALLOWED'));
			return false;
		}

		$helper = $this->getClient();
		if ($res->booked)
		{
			$path = $this->_rfStoreInvoice($res->reference);
			$pdf = file_get_contents($path);
		}
		else
		{ // not stored locally as long as not booked
			$pdf = $helper->CurrentInvoice_GetPdf(array('Id' => $reference));
		}

		return true;
	}

	/**
	 * store invoices
	 *
	 * @param   int  $invoiceId  invoice id
	 *
	 * @return string path on success, false otherwise
	 */
	public function _rfStoreInvoice($invoiceId)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		/* Get the file path for file upload */
		$query = ' SELECT c.value '
			. ' FROM #__rwf_configuration AS c '
			. ' WHERE name = ' . $this->_db->Quote('filelist_path');
		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();

		$filepath = $res->value;
		if (empty($filepath))
		{
			$filepath = JPATH_ROOT . DS . 'media' . DS . 'redform';
		}

		$fullpath = $filepath . DS . 'invoices';
		if (!JFolder::exists($fullpath))
		{
			if (!JFolder::create($fullpath))
			{
				JError::raiseWarning(0, JText::_('CANNOT_CREATE_FOLDER') . ': ' . $fullpath);
				$status = false;
				return false;
			}
		}

		if (JFile::exists($fullpath . DS . 'invoice' . $invoiceId . '.pdf'))
		{
			return $fullpath . DS . 'invoice' . $invoiceId . '.pdf';
		}

		$helper = $this->getClient();
		$pdf = $helper->Invoice_GetPdf(array('Number' => $invoiceId));
		if ($pdf)
		{
			$res = JFile::write($fullpath . DS . 'invoice' . $invoiceId . '.pdf', $pdf);
			return $fullpath . DS . 'invoice' . $invoiceId . '.pdf';
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
	public function _rfSendInvoiceEmail($invoiceId)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$app = &Jfactory::getApplication();

		$query = ' SELECT b.* '
			. ' FROM #__rwf_billings AS b '
			. ' INNER JOIN #__rwf_payments_requests AS pr ON pr.submit_key = b.submit_key '
			. ' INNER JOIN #__rwf_invoices AS i ON i.paymentrequest_id = pr.id '
			. ' WHERE i.reference = ' . $this->_db->Quote($invoiceId)
			. ' ORDER BY b.id DESC ';
		$this->_db->setQuery($query);
		$data = $this->_db->loadObject();

		if (!$data)
		{
			JError::raiseWarning(0, JText::_('PLG_REDFORM_INTEGRATION_ECONOMIC_SEND_INVOICE_ERROR_UNKOWN_INVOICE'));
			return false;
		}

		//	make sure the invoice is stored indeed
		$path = $this->_rfStoreInvoice($invoiceId);
		if (!$path)
		{
			return false;
		}

		$mailer = &JFactory::getMailer();

		jimport('joomla.mail.helper');
		/* Start the mailer object */
		$mailer = &JFactory::getMailer();
		$mailer->isHTML(true);
		$mailer->From = $app->getCfg('mailfrom');
		$mailer->FromName = $app->getCfg('sitename');
		$mailer->AddReplyTo(array($app->getCfg('mailfrom'), $app->getCfg('sitename')));
		$mailer->addAttachment($path);
		$mailer->setSubject(JText::sprintf('PLG_REDFORM_INTEGRATION_ECONOMIC_SEND_INVOICE_SUBJECT', $data->title));
		$mailer->setBody(JText::sprintf('PLG_REDFORM_INTEGRATION_ECONOMIC_SEND_INVOICE_BODY'));
		$mailer->addRecipient($data->email);
		$mailer->send();
	}

	/**
	 * Get cart details, including billing and items and previous invoices
	 *
	 * @param   int  $cartId  cart id
	 *
	 * @return mixed
	 */
	private function getCartDetails($cartId)
	{
		$cart = $this->getCart($cartId);
		$cart->paymentRequests = $this->getPaymentRequests($cartId);
		$cart->billing = $this->getBilling($cartId);
		$cart->invoices = $this->getInvoices($cartId);
		$cart->title = $this->getCartTitle($cart);

		return $cart;
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
	 * @param   int  $cartId  cart id
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
					$cartDetails->submit_key,
					$integrationDetails
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

				if (!$this->_db->execute())
				{
					throw new RuntimeException(JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $this->_db->stderr(true)));
				}
			}
		}
	}
}
