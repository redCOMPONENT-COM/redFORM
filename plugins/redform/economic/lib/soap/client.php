<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.economic
 *
 * @copyright   Copyright (C) 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

/**
 * Economic soap client class
 *
 * @package     Redform.Plugin
 * @subpackage  Redform.economic
 * @since       3.0
 */
class RedformeconomicSoapClient
{
	private $_conn = false;

	private $error = 0;

	private $errorMsg = null;

	private $client = null;

	private $LayoutHandle;

	private $account = null;

	/**
	 * @var string service url
	 */
	private $url = 'https://www.e-conomic.com/secure/api1/EconomicWebservice.asmx?WSDL';

	/**
	 * @var JRegistry
	 */
	private $params;

	/**
	 * Contructor
	 *
	 * @param   JRegistry  $params  parameters from plugin
	 */
	public function __construct($params)
	{
		$this->params = $params;
		$this->connect();
		$this->authenticate();
	}

	/**
	 * getter
	 *
	 * @param   string  $name  property
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'client':
				return $this->client;
		}
	}


	/**
	 * connect
	 *
	 * @return void
	 */
	private function connect()
	{
		try
		{
			$this->client = new SoapClient($this->url, array("trace" => 1, "exceptions" => 1));
		}
		catch (Exception $exception)
		{
			throw new RuntimeException("Unable to connect soap client: " . $exception->getMessage());
		}

	}

	/**
	 * Authenticate
	 *
	 * @return void
	 */
	private function authenticate()
	{
		try
		{
			$conn = array(
				'agreementNumber' => $this->params->get('economic_agreement_number', ''),
				'userName' => $this->params->get('economic_username', ''),
				'password' => $this->params->get('economic_password', '')
			);
			$this->_conn = $this->client->Connect($conn);
		}
		catch (Exception $exception)
		{
			throw new RuntimeException("e-conomic user is not authenticated. Access denied");
		}
	}

	/**
	 * Method to find debtor number in economic
	 *
	 * @param   array  $d  debtor number
	 *
	 * @return array
	 */
	public function Debtor_FindByNumber($d)
	{
		$handle = $this->client->Debtor_FindByNumber(array('number' => $d['user_id']))->Debtor_FindByNumberResult;

		return $handle;
	}

	/**
	 * Method to find debtor number in economic
	 *
	 * @param   array  $d  debtor number
	 *
	 * @return array
	 */
	public function Debtor_FindByEmail($d)
	{
		$Handle = $this->client->Debtor_FindByEmail(array('email' => $d['email']))->Debtor_FindByEmailResult;

		if ($Handle && isset($Handle->DebtorHandle))
		{
			if (!is_array($Handle->DebtorHandle))
			{
				return array($Handle->DebtorHandle->Number);
			}
			else
			{
				$res = array();

				foreach ($Handle->DebtorHandle as $obj)
				{
					$res[] = $obj->Number;
				}

				return $res;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * find debtor by email and debtor group matching settings
	 *
	 * @param   string  $email  email
	 *
	 * @return int debtor number or 0 if not found
	 */
	public function findDebtorByEmail($email)
	{
		$debtors = $this->Debtor_FindByEmail(array('email' => $email));

		if (!$debtors)
		{
			return false;
		}

		// Match group id
		$group = trim($this->params->get('economic_debtor_group'));

		foreach ($debtors as $id)
		{
			$gp = $this->Debtor_GetDebtorGroup($id);

			if ($gp == $group)
			{
				return $id;
			}
		}

		return 0;
	}

	/**
	 * Method to find debtor invoices in economic
	 *
	 * @param   array  $d  handle
	 *
	 * @return array
	 */
	public function Debtor_GetInvoices($d)
	{
		$Handle = $this->client->Debtor_GetInvoices($d)->Debtor_GetInvoicesResult;

		if ($Handle && isset($Handle->InvoiceHandle))
		{
			if (!is_array($Handle->InvoiceHandle))
			{
				return array($Handle->InvoiceHandle);
			}
			else
			{
				return $Handle->InvoiceHandle;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * returns debtor info
	 *
	 * @param   object  $handle  handle
	 *
	 * @return object
	 */
	public function Debtor_GetData($handle)
	{
		$res = $this->client->Debtor_GetData(array('entityHandle' => $handle))->Debtor_GetDataResult;

		return $res;
	}

	/**
	 * Method to get invoice data in economic
	 *
	 * @param   object  $d  handle
	 *
	 * @return array
	 */
	public function Invoice_GetData($d)
	{
		$Handle = $this->client->Invoice_GetData($d)->Invoice_GetDataResult;

		return $Handle;
	}

	/**
	 * Method to get invoice data in economic
	 *
	 * @param   object  $d  handle
	 *
	 * @return array
	 */
	public function Invoice_GetLines($d)
	{
		$Handle = $this->client->Invoice_GetLines($d)->Invoice_GetLinesResult;

		if ($Handle->InvoiceLineHandle)
		{
			if (is_array($Handle->InvoiceLineHandle))
			{
				return $Handle->InvoiceLineHandle;
			}
			else
			{
				return array($Handle->InvoiceLineHandle);
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to get invoice data in economic
	 *
	 * @param   object  $d  handle
	 *
	 * @return array
	 */
	public function CurrentInvoice_GetData($d)
	{
		$Handle = $this->client->CurrentInvoice_GetData($d)->CurrentInvoice_GetDataResult;

		if ($Handle)
		{
			return $Handle;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to get all current invoice in economic
	 *
	 * @return array
	 */
	public function CurrentInvoice_GetAll()
	{
		$Handle = $this->client->CurrentInvoice_GetAll()->CurrentInvoice_GetAllResult;

		if ($Handle)
		{
			if ($Handle->CurrentInvoiceHandle && is_array($Handle->CurrentInvoiceHandle))
			{
				return $Handle->CurrentInvoiceHandle;
			}
			elseif ($Handle->CurrentInvoiceHandle)
			{
				return array($Handle->CurrentInvoiceHandle);
			}

			return false;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to get current invoice lines in economic
	 *
	 * @param   array  $d  handle
	 *
	 * @return array
	 */
	public function CurrentInvoice_GetLines($d)
	{
		$Handle = $this->client->CurrentInvoice_GetLines($d)->CurrentInvoice_GetLinesResult;

		if ($Handle->CurrentInvoiceLineHandle)
		{
			if (is_array($Handle->CurrentInvoiceLineHandle))
			{
				return $Handle->CurrentInvoiceLineHandle;
			}
			else
			{
				return array($Handle->CurrentInvoiceLineHandle);
			}
		}
		else
		{
			return false;
		}
	}


	/**
	 * Method to delete an invoice pdf of a current invoice
	 *
	 * @param   array  $invoiceHandle  handle
	 *
	 * @return bool
	 */
	public function CurrentInvoice_Delete($invoiceHandle)
	{
		$this->client->CurrentInvoice_Delete(array('currentInvoiceHandle' => $invoiceHandle));

		return true;
	}

	/**
	 * Method to get debtor group
	 *
	 * @return array
	 *
	 * @throws RuntimeException
	 */
	public function getDebtorGroup()
	{
		$params = $this->params;

		$id = trim($params->get('economic_debtor_group'));

		if ($id)
		{
			$res = $this->client->DebtorGroup_FindByNumber(array('number' => $id));

			if ($res && isset($res->DebtorGroup_FindByNumberResult))
			{
				return $res->DebtorGroup_FindByNumberResult;
			}
			else
			{
				RdfHelperLog::simpleLog(Jtext::_('PLG_ECONOMIC_DEBTOR_GROUP_NOT_FOUND_IN_ECONOMIC_USING_FIRST'));
			}
		}

		// If not found, take default value
		$debtorGroupHandles = $this->client->debtorGroup_GetAll()->DebtorGroup_GetAllResult;

		if ($debtorGroupHandles)
		{
			if (is_array($debtorGroupHandles->DebtorGroupHandle))
			{
				return $debtorGroupHandles->DebtorGroupHandle[0];
			}
			else
			{
				return $debtorGroupHandles->DebtorGroupHandle;
			}
		}
		else
		{
			throw new RuntimeException(Jtext::_('PLG_ECONOMIC_NO_DEBTOR_GROUP_IN_ECONOMIC'));
		}
	}

	/**
	 * Method to get term of payment
	 *
	 * @return array
	 */
	public function getTermOfPayment()
	{
		$termofresultall = $this->client->TermOfPayment_GetAll()->TermOfPayment_GetAllResult;
		$termofpayments = $termofresultall->TermOfPaymentHandle;

		for ($i = 0; $i < count($termofpayments); $i++)
		{
			if ($termofpayments[$i]->Id)
			{
				$termofpayment = new stdclass;
				$termofpayment->Id = $termofpayments[$i]->Id;

				return $termofpayment;
			}

			$termofpayment = $termofpayments[$i];
		}

		return $termofpayment;
	}

	/**
	 * Method to get layout template
	 *
	 * @return array
	 */
	public function getLayoutTemplate()
	{
		if ($this->LayoutHandle)
		{
			return $this->LayoutHandle;
		}

		$params = $this->params;

		$name = trim($params->get('economic_template'));

		if (empty($name))
		{
			return $this->_useDefaultTemplate();
		}

		$res = $this->client->TemplateCollection_FindByName(array('name' => $name))->TemplateCollection_FindByNameResult;

		if (!$res || !isset($res->TemplateCollectionHandle))
		{
			RdfHelperLog::simpleLog(JText::_('PLG_ECONOMIC_TEMPLATE_NOT_FOUND_IN_ECONOMIC_USING_FIRST_FOUND_AS_DEFAULT'));

			return $this->_useDefaultTemplate();
		}

		if (is_array($res->TemplateCollectionHandle))
		{
			$this->LayoutHandle = $res->TemplateCollectionHandle[0];
		}
		else
		{
			$this->LayoutHandle = $res->TemplateCollectionHandle;
		}

		return $this->LayoutHandle;
	}

	/**
	 * Get default template
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	protected function _useDefaultTemplate()
	{
		$res = $this->client->TemplateCollection_GetAll()->TemplateCollection_GetAllResult;

		if (!$res || !isset($res->TemplateCollectionHandle))
		{
			throw new Exception(JText::_('PLG_ECONOMIC_NO_TEMPLATE_FOUND_IN_ECONOMIC'));
		}

		if (is_array($res->TemplateCollectionHandle))
		{
			$this->LayoutHandle = $res->TemplateCollectionHandle[0];
		}
		else
		{
			$this->LayoutHandle = $res->TemplateCollectionHandle;
		}

		return $this->LayoutHandle;
	}

	/**
	 * Method to store debtor in economic
	 *
	 * @param   array  $d  handle
	 *
	 * @return array
	 */
	public function storeDebtor($d)
	{
		$DebtorGroupHandle = $this->getDebtorGroup();
		$TermOfPaymentHandle = $this->getTermOfPayment();

		$CurrencyHandle = new stdclass;
		$CurrencyHandle->Code = $d['currency_code'];

		if (!$d['Number'])
		{
			$number = $this->Debtor_GetNextAvailableNumber();
		}
		else
		{
			$number = $d['Number'];
		}

		$Handle = new stdclass;
		$Handle->Number = $number;

		$LayoutHandle = $this->getLayoutTemplate();
		$VatZone = $d['vatzone'];
		$AttentionHandle = new stdclass;
		$AttentionHandle->Id = 1;

		$userinfo = array
		(
			'Handle' => $Handle,
			'Number' => $number,
			'DebtorGroupHandle' => $DebtorGroupHandle,
			'Name' => $d['name'],
			'VatZone' => $VatZone,
			'CurrencyHandle' => $CurrencyHandle,
			'IsAccessible' => 1,
			'Email' => $d['email'],
			'TermOfPaymentHandle' => $TermOfPaymentHandle,
			'LayoutHandle' => $LayoutHandle

		);

		if (isset($d['phone']))
		{
			$userinfo['TelephoneAndFaxNumber'] = $d['phone'];
		}

		if (isset($d['address']))
		{
			$userinfo['Address'] = $d['address'];
		}

		if (isset($d['zipcode']))
		{
			$userinfo['PostalCode'] = $d['zipcode'];
		}

		if (isset($d['city']))
		{
			$userinfo['City'] = $d['city'];
		}

		if (isset($d['country']))
		{
			$userinfo['Country'] = $d['country'];
		}

		if (isset($d['vatnumber']))
		{
			$userinfo['VatNumber'] = $d['vatnumber'];
		}

		if ($d['Number'])
		{
			$newDebtorHandle = $this->client->Debtor_UpdateFromData(array("data" => $userinfo))->Debtor_UpdateFromDataResult;
		}
		else
		{
			$newDebtorHandle = $this->client->Debtor_CreateFromData(array("data" => $userinfo))->Debtor_CreateFromDataResult;
		}

		return $newDebtorHandle;
	}

	/**
	 * Method to find product id by number in economic
	 *
	 * @param   int  $id  product number
	 *
	 * @return int
	 */
	public function Product_FindByNumber($id)
	{
		$Handle = $this->client->Product_FindByNumber(array('number' => $id));

		if ($Handle && isset($Handle->Product_FindByNumberResult))
		{
			return $Handle->Product_FindByNumberResult->Number;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Method to get product group
	 *
	 * @param   array  $d  handle
	 *
	 * @return array
	 */
	public function getProductGroup($d)
	{
		if (isset($d['productgroup']))
		{
			// Try the product group from the product
			$res = $this->client->ProductGroup_FindByNumber(array('number' => $d['productgroup']))->ProductGroup_FindByNumberResult;

			if ($res && isset($res->Number))
			{
				return $res;
			}
		}
		elseif ($number = $this->params->get('default_product_group'))
		{
			// Try default product group
			$res = $this->client->ProductGroup_FindByNumber(array('number' => $number))->ProductGroup_FindByNumberResult;

			if ($res && isset($res->Number))
			{
				return $res;
			}
		}

		// Get any product group...
		$productGroupHandles = $this->client->ProductGroup_GetAll()->ProductGroup_GetAllResult->ProductGroupHandle;

		for ($i = 0; $i < count($productGroupHandles); $i++)
		{
			if (!$productGroupHandles[$i]->Number)
			{
				$productGroupHandle = new stdclass;
				$productGroupHandle->Number = $productGroupHandles[$i];

				return $productGroupHandle;
			}

			return $productGroupHandles[$i];
		}

		return $productGroupHandles;
	}

	/**
	 * Method to store product in economic
	 *
	 * @param   array  $d  handle
	 *
	 * @return array
	 */
	public function storeProduct($d)
	{
		$productGroupHandle = $this->getProductGroup($d);

		if (!$d['handle'])
		{
			$handle = $d['number'];
		}
		else
		{
			$handle = $d['handle'];
		}

		$Handle = new stdclass;
		$Handle->Number = $handle;

		$prdinfo = array
		(
			'Handle' => $Handle,
			'Number' => $d['number'],
			'ProductGroupHandle' => $productGroupHandle,
			'Name' => $d['product_name'],
			'BarCode' => '22222',
			'SalesPrice' => intval($d['product_price']),
			'CostPrice' => intval($d['product_price']),
			'RecommendedPrice' => intval($d['product_price']),
			'Volume' => 0,
			'IsAccessible' => 1,
			'InStock' => 1
		);

		if ($d['handle'])
		{
			$newProductNumber = $this->client->Product_UpdateFromData(array('data' => $prdinfo))->Product_UpdateFromDataResult;
		}
		else
		{
			$newProductNumber = $this->client->Product_CreateFromData(array('data' => $prdinfo))->Product_CreateFromDataResult;
		}

		return $newProductNumber;
	}

	/**
	 * Method to get debtor contact handle
	 *
	 * @param   array  $d  handle
	 *
	 * @return array
	 */
	public function getDebtorContactHandle($d)
	{
		$contacts = $this->client->DebtorContact_FindByExternalId(array('externalId' => $d ['user_info_id']))->DebtorContact_FindByExternalIdResult;

		if (isset($contacts->DebtorContactHandle) && count($contacts->DebtorContactHandle) > 0)
		{
			if (is_array($contacts->DebtorContactHandle))
			{
				for ($i = 0; $i < count($contacts->DebtorContactHandle); $i++)
				{
					if ($contacts->DebtorContactHandle[$i]->Id)
					{
						$contactHandle = new stdclass;
						$contactHandle->Id = $contacts->DebtorContactHandle[$i]->Id;

						return $contactHandle;
					}
				}
			}
			else
			{
				$contactHandle = new stdclass;
				$contactHandle->Id = $contacts->DebtorContactHandle->Id;

				return $contactHandle;
			}
		}
		else
		{
			$contactHandle = $this->DebtorContact_Create($d);

			return $contactHandle;
		}
	}

	/**
	 * Method to create debtor contact in economic
	 *
	 * @param   array  $d  handle
	 *
	 * @return array
	 */
	public function DebtorContact_Create($d)
	{
		$Id = $d['user_info_id'];
		$Handle = new stdclass;
		$Handle->Id = $Id;

		$debtorHandle = new stdclass;
		$debtorHandle->Number = $d['debtorHandle'];

		$userinfo = array
		(
			'Handle' => $Handle,
			'DebtorHandle' => $debtorHandle,
			'Id' => $Id,
			'Number' => $Id,
			'Name' => $d['name'],
			'Email' => $d['email'],
			'ExternalId' => $Id,
			'IsToReceiveEmailCopyOfOrder' => 0,
			'IsToReceiveEmailCopyOfInvoice' => 0
		);

		$contactHandle = $this->client->DebtorContact_CreateFromData(array('data' => $userinfo))->DebtorContact_CreateFromDataResult;

		return $contactHandle;
	}

	/**
	 * Method to create debtor contact in economic
	 *
	 * @param   array  $data  data
	 *
	 * @return array
	 */
	public function DebtorContact_CreateFromData($data)
	{
		$userinfo = array
		(
			'DebtorHandle' => $data['DebtorHandle'],
			'Name' => $data['name'],
			'Email' => $data['email'],
			'Number' => 1,
			'IsToReceiveEmailCopyOfOrder' => 0,
			'IsToReceiveEmailCopyOfInvoice' => 0
		);
		$contactHandle = $this->client->DebtorContact_CreateFromData(array('data' => $userinfo))->DebtorContact_CreateFromDataResult;

		return $contactHandle;
	}

	/**
	 * Method to create invoice in economic
	 *
	 * @param   array  $d  handle
	 *
	 * @return array
	 */
	public function createInvoice($d)
	{
		$CurrencyHandle = new stdclass;
		$CurrencyHandle->Code = $d['currency_code'];

		$debtorHandle = new stdclass;
		$debtorHandle->Number = $d['debtorHandle'];

		$TermOfPaymentHandle = $this->getTermOfPayment();

		$isvat = $d['isvat'];

		$invoiceHandle = $this->client->CurrentInvoice_Create(array('debtorHandle' => $debtorHandle))->CurrentInvoice_CreateResult;
		$this->client->CurrentInvoice_SetCurrency(array('currentInvoiceHandle' => $invoiceHandle, 'valueHandle' => $CurrencyHandle));

		$this->client->CurrentInvoice_SetTermOfPayment(array('currentInvoiceHandle' => $invoiceHandle, 'valueHandle' => $TermOfPaymentHandle));

		$this->client->CurrentInvoice_SetIsVatIncluded(array('currentInvoiceHandle' => $invoiceHandle, 'value' => $isvat));

		if ($d['text'])
		{
			$this->client->CurrentInvoice_SetTextLine1(array('currentInvoiceHandle' => $invoiceHandle, 'value' => $d['text']));
		}

		return $invoiceHandle;
	}

	/**
	 * Method to create invoice line in economic
	 *
	 * @param   array  $d  data
	 *
	 * @return array
	 */
	public function createInvoiceLine($d)
	{
		$order_item_id = $d['order_item_id'];
		$invoiceHandle = new stdclass;
		$invoiceHandle->Id = $d['invoiceHandle'];

		$Handle = new stdclass;
		$Handle->Id = $order_item_id;
		$Handle->Number = $order_item_id;

		$ProductHandle = new stdclass;
		$ProductHandle->Number = $d ['product_number'];

		$info = array
		(
			'Handle' => $Handle,
			'InvoiceHandle' => $invoiceHandle,
			'Number' => $order_item_id,
			'Id' => $order_item_id,
			'Description' => $d['product_name'],
			'DeliveryDate' => $d['delivery_date'],
			'ProductHandle' => $ProductHandle,
			'UnitNetPrice' => $d['product_price'],
			'Quantity' => $d['product_quantity'],
			'DiscountAsPercent' => 0,
			'UnitCostPrice' => $d['product_price'],
			'TotalMargin' => $d['product_price'],
			'TotalNetAmount' => $d['product_price'],
			'MarginAsPercent' => 1
		);
		$invoiceLineHandle = $this->client->CurrentInvoiceLine_CreateFromData(array('data' => $info))->CurrentInvoiceLine_CreateFromDataResult;

		return $invoiceLineHandle;
	}

	/**
	 * Method to book invoice
	 *
	 * @param   array  $d  data
	 *
	 * @return array
	 */
	public function bookInvoice($d)
	{
		$currentInvoiceHandle = new stdclass;
		$currentInvoiceHandle->Id = $d['invoiceHandle'];
		$debtorHandle = $this->CurrentInvoice_GetDebtor($currentInvoiceHandle);
		$d['debtorHandle'] = $debtorHandle->Number;
		$invoiceHandle = $this->CurrentInvoice_Book($d);

		if ($invoiceHandle)
		{
			// Cashbook entry
			if (isset($d['amount']) && $d['amount'] > 0)
			{
				$this->createCashbookEntry($d, $invoiceHandle);
			}
		}

		return $invoiceHandle;
	}

	/**
	 * Method to find current book invoice number in economic
	 *
	 * @param   array  $d  data
	 *
	 * @return array
	 */
	public function CurrentInvoice_BookWithNumber($d)
	{
		$invoiceHandle = new stdclass;
		$invoiceHandle->Id = $d['invoiceHandle'];

		$bookHandle = $this->client->CurrentInvoice_BookWithNumber(array('currentInvoiceHandle' => $invoiceHandle, 'number' => $d['invoiceHandle']))->CurrentInvoice_BookWithNumberResult;

		return $bookHandle;
	}

	/**
	 * Method to find current book invoice number in economic
	 *
	 * @param   array  $d  data
	 *
	 * @return array
	 */
	public function CurrentInvoice_Book($d)
	{
		$currentInvoiceHandle = new stdclass;
		$currentInvoiceHandle->Id = $d['invoiceHandle'];

		$invoiceHandle = $this->client->CurrentInvoice_Book(array('currentInvoiceHandle' => $currentInvoiceHandle))->CurrentInvoice_BookResult;

		return $invoiceHandle;
	}

	/**
	 * Method to get pdf invoice
	 *
	 * @param   array  $invoiceHandle  invoice Handle
	 *
	 * @return array
	 */
	public function Invoice_GetPdf($invoiceHandle)
	{
		$pdf = $this->client->Invoice_GetPdf(array('invoiceHandle' => $invoiceHandle))->Invoice_GetPdfResult;

		return $pdf;
	}

	/**
	 * Method to get Invoice By Other Reference
	 *
	 * @param   array  $reference  data
	 *
	 * @return array
	 */
	public function Invoice_FindByOtherReference($reference)
	{
		$res = $this->client->Invoice_FindByOtherReference(array('otherReference' => $reference))->Invoice_FindByOtherReferenceResult;

		if ($res && isset($res->InvoiceHandle))
		{
			if (is_array($res->InvoiceHandle))
			{
				return $res->InvoiceHandle[0];
			}
			else
			{
				return $res->InvoiceHandle;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to get Invoice By number
	 *
	 * @param   int  $number  number
	 *
	 * @return array
	 */
	public function Invoice_FindByNumber($number)
	{
		$res = $this->client->Invoice_FindByNumber(array('number' => $number));

		if ($res->Invoice_FindByNumberResult)
		{
			return $res->Invoice_FindByNumberResult;
		}
		else
		{
			return false;
		}
	}

	/**
	 * returns handle of debtor associated to invoice
	 *
	 * @param   array  $handle  data
	 *
	 * @return int|boolean
	 */
	public function Invoice_GetDebtor($handle)
	{
		if (!is_object($handle))
		{
			$handle = array('invoiceHandle' => $handle);
		}
		else
		{
			$handle = array('invoiceHandle' => array('Number' => $handle));
		}

		$res = $this->client->Invoice_GetDebtor($handle);

		if ($res->Invoice_GetDebtorResult)
		{
			return $res->Invoice_GetDebtorResult;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to get pdf of a current invoice
	 *
	 * @param   array  $invoiceHandle  data
	 *
	 * @return mixed
	 */
	public function CurrentInvoice_GetPdf($invoiceHandle)
	{
		$pdf = $this->client->CurrentInvoice_GetPdf(array('currentInvoiceHandle' => $invoiceHandle))->CurrentInvoice_GetPdfResult;

		return $pdf;
	}

	/**
	 * Method to create cash book entry in economic
	 *
	 * @param   array  $d           data
	 * @param   array  $bookHandle  book handle
	 *
	 * @return array
	 */
	public function createCashbookEntry($d, $bookHandle)
	{
		if ($this->error)
		{
			return $this->errorMsg;
		}

		$cashBookHandle = new stdclass;
		$cashBookHandle->Number = $this->params->get('economic_payment_cashbook_id', 2);

		$debtorHandle = new stdclass;
		$debtorHandle->Number = $d['debtorHandle'];

		$contraAccountHandle = new stdclass;
		$contraAccountHandle->Number = $this->params->get('economic_contra_account', 1200);

		$CurrencyHandle = new stdclass;
		$CurrencyHandle->Code = $d['currency_code'];
		$text = 'INV (' . $bookHandle->Number . ') CUST (' . $d['name'] . ')';

		if (isset($d['uniqueid']) && !empty($d['uniqueid']))
		{
			$text .= " - " . $d['uniqueid'];
		}

		$cashBookEntryHandle = $this->client->CashBookEntry_CreateDebtorPayment(
			array('cashBookHandle' => $cashBookHandle,
				'debtorHandle' => $debtorHandle,
				'contraAccountHandle' => $contraAccountHandle)
		)->CashBookEntry_CreateDebtorPaymentResult;
		$this->client->CashBookEntry_SetAmount(array('cashBookEntryHandle' => $cashBookEntryHandle, 'value' => (0 - $d['amount'])));
		$this->client->CashBookEntry_SetDebtorInvoiceNumber(array('cashBookEntryHandle' => $cashBookEntryHandle, 'value' => $bookHandle->Number));
		$this->client->CashBookEntry_SetText(array('cashBookEntryHandle' => $cashBookEntryHandle, 'value' => $text));
		$this->client->CashBookEntry_SetCurrency(array('cashBookEntryHandle' => $cashBookEntryHandle, 'valueHandle' => $CurrencyHandle));

		if ($this->params->get('economic_default_currency') == $d ['currency_code'])
		{
			$this->client->CashBookEntry_SetAmountDefaultCurrency(array('cashBookEntryHandle' => $cashBookEntryHandle, 'value' => (0 - $d['amount'])));
		}

		$this->client->CashBook_Book(array('cashBookHandle' => $cashBookHandle));
	}

	/**
	 * get contra account handle associated to specified account
	 *
	 * @param   array  $accountHandle  data
	 *
	 * @return array account
	 */
	public function Account_GetContraAccount($accountHandle)
	{
		$res = $this->client->Account_GetContraAccount(array('accountHandle' => $accountHandle))->Account_GetContraAccountResult;

		return $res;
	}

	/**
	 * Get next available debtor number
	 *
	 * @return mixed
	 */
	public function Debtor_GetNextAvailableNumber()
	{
		$id = $this->client->Debtor_GetNextAvailableNumber()->Debtor_GetNextAvailableNumberResult;

		return $id;
	}

	/**
	 * returns debtor group number
	 *
	 * @param   int  $debtor_number  debtor number
	 *
	 * @return int
	 */
	public function Debtor_GetDebtorGroup($debtor_number)
	{
		$res = $this->client->Debtor_GetDebtorGroup(array('debtorHandle' => array('Number' => $debtor_number)));

		if ($res)
		{
			return $res->Debtor_GetDebtorGroupResult->Number;
		}

		return 0;
	}

	/**
	 * create current invoice
	 *
	 * @param   array  $debtorhandle  debtorhandle
	 *
	 * @return int
	 */
	public function CurrentInvoice_Create($debtorhandle)
	{
		$res = $this->client->CurrentInvoice_Create(array('debtorHandle' => $debtorhandle))->CurrentInvoice_CreateResult;

		return $res;
	}

	/**
	 * create invoice from data
	 *
	 * @param   array  $data  data
	 *
	 * @return int
	 */
	public function CurrentInvoice_CreateFromData($data)
	{
		$res = $this->client->CurrentInvoice_CreateFromData($data)->CurrentInvoice_CreateFromDataResult;

		return $res;
	}

	/**
	 * Create invoice line
	 *
	 * @param   array  $invoiceHandle  invoice handlle
	 *
	 * @return mixed
	 */
	public function CurrentInvoiceLine_Create($invoiceHandle)
	{
		$res = $this->client->CurrentInvoiceLine_Create(array('invoiceHandle' => $invoiceHandle))->CurrentInvoiceLine_CreateResult;

		return $res;
	}

	/**
	 * Create current invoice line from data
	 *
	 * @param   array  $data  data
	 *
	 * @return mixed
	 */
	public function CurrentInvoiceLine_CreateFromData($data)
	{
		$res = $this->client->CurrentInvoiceLine_CreateFromData($data)->CurrentInvoiceLine_CreateFromDataResult;

		return $res;
	}

	/**
	 * Create current invoice line set product
	 *
	 * @param   array  $invoicelineHandle  invoice handlle
	 * @param   array  $productHandle      product handlle
	 *
	 * @return mixed
	 */
	public function CurrentInvoiceLine_SetProduct($invoicelineHandle, $productHandle)
	{
		$data = array('currentInvoiceLineHandle' => $invoicelineHandle, 'valueHandle' => $productHandle);
		$this->client->CurrentInvoiceLine_SetProduct($data);

		return true;
	}

	/**
	 * Set invoice other reference
	 *
	 * @param   array  $data  data
	 *
	 * @return bool
	 */
	public function CurrentInvoice_SetOtherReference($data)
	{
		$this->client->CurrentInvoice_SetOtherReference($data);

		return true;
	}

	/**
	 * return handles to current invoices matching other reference
	 *
	 * @param   string  $reference  reference
	 *
	 * @return mixed false or array
	 */
	public function CurrentInvoice_FindByOtherReference($reference)
	{
		$Handle = $this->client->CurrentInvoice_FindByOtherReference(array('otherReference' => $reference))->CurrentInvoice_FindByOtherReferenceResult;

		if ($Handle && isset($Handle->CurrentInvoiceHandle))
		{
			if (!is_array($Handle->CurrentInvoiceHandle))
			{
				return array($Handle->CurrentInvoiceHandle);
			}
			else
			{
				$res = array();

				foreach ($Handle->CurrentInvoiceHandle as $obj)
				{
					$res[] = $obj;
				}

				return $res;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to get invoice debtor handle
	 *
	 * @param   int  $invoice_id  invoice id
	 *
	 * @return handle
	 */
	public function CurrentInvoice_GetDebtor($invoice_id)
	{
		$Handle = $this->client->CurrentInvoice_GetDebtor(array('currentInvoiceHandle' => $invoice_id));

		if ($Handle && isset($Handle->CurrentInvoice_GetDebtorResult))
		{
			return $Handle->CurrentInvoice_GetDebtorResult;
		}
		else
		{
			return false;
		}
	}
}
