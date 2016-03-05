<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.pdfinvoice
 *
 * @copyright   Copyright (C) 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

$redformLoader = JPATH_LIBRARIES . '/redform/bootstrap.php';

if (!file_exists($redformLoader))
{
	throw new Exception(JText::_('COM_REDFORM_LIB_INIT_FAILED'), 404);
}

include_once $redformLoader;

// Bootstraps redFORM
RdfBootstrap::bootstrap();

/**
 * Class plgRedformPdfinvoice
 *
 * @package     Redform.Plugin
 * @subpackage  Redform.pdfinvoice
 * @since       3.0
 */
class plgRedformPdfinvoice extends JPlugin
{
	private $db = null;

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
			RdfHelperLog::simpleLog(sprintf('PDF invoice error for cart id %s: %s', $cartId, $e->getMessage()));
		}
	}

	/**
	 * Create invoice from payment request
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
			RdfHelperLog::simpleLog(sprintf('PDF invoice error for payment request id %s: %s', $paymentrequestId, $e->getMessage()));
		}
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
			RdfHelperLog::simpleLog(sprintf('PDF invoice error for cart id %s: %s', $table->cart_id, $e->getMessage()));
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
	public function onAjaxTurninvoice()
	{
		$app = JFactory::getApplication();
		$invoiceId = $app->input->getInt('id', 0);
		$reference = $app->input->get('reference');
		$return = $app->input->get('return');

		$invoice = $this->confirmReference($invoiceId, $reference);
		$this->getCartDetails($invoice->cart_id);

		$this->turnInvoice($invoiceId, $reference);

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

		if (!$path = $this->rfGetInvoice($reference))
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
	public function onAjaxInstallPdfinvoice()
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
						JText::sprintf('PLG_REDFORM_INTEGRATION_PDFINVOICE_INSTALL_REPLACE_FILE', $htmlTemplatePath . $relPath)
					);
				}
				else
				{
					JFolder::create(dirname($htmlTemplatePath . $relPath));

					JFactory::getApplication()->enqueueMessage(
						JText::sprintf('PLG_REDFORM_INTEGRATION_PDFINVOICE_INSTALL_ADD_FILE', $htmlTemplatePath)
					);
				}

				if (!JFile::copy($file, $htmlTemplatePath . $relPath))
				{
					$error = error_get_last();
					JFactory::getApplication()->enqueueMessage($error['message'], 'error');
				}
			}
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
		if (!$this->cart = $this->getCartDetails($cartId))
		{
			throw new Exception('Cart details not found');
		}

		$data = $this->cart;

		if ($data->invoices && !$force)
		{
			// Do not create if already invoiced
			return false;
		}

		$text = '<pre>' . print_r($data, true) . '</pre>';

		$reference = uniqid();
		$invoice = $this->rfStoreInvoice($reference, $text);

		// Update table
		$table = RTable::getInstance('Invoice', 'RedformTable');
		$table->cart_id = $data->id;
		$table->date = JFactory::getDate()->toSql();
		$table->reference = $reference;
		$table->store();

		return $invoice;
	}

	/**
	 * Book an invoice
	 *
	 * @param   int     $invoiceId  invoice id
	 * @param   string  $reference  invoice reference
	 *
	 * @return bool
	 */
	private function turnInvoice($invoiceId, $reference)
	{
		$cart = $this->getCartDetails();

		$text = '<pre>' . 'TURNED: ' . print_r($cart, true) . '</pre>';

		$turnedReference = uniqid();
		$this->rfStoreInvoice($turnedReference, $text);

		// Add new table row
		$table = RTable::getInstance('Invoice', 'RedformTable');
		$table->cart_id = $cart->id;
		$table->date = JFactory::getDate()->toSql();
		$table->reference = $turnedReference;
		$table->store();

		// Updated turned invoice
		$table = RTable::getInstance('Invoice', 'RedformTable');
		$table->reset();
		$table->load(array('reference' => $reference));
		$table->turned = $invoiceId;
		$table->store();

		return (bool) true;
	}

	/**
	 * store invoice
	 *
	 * @param   string  $invoiceReference  invoice reference
	 * @param   string  $invoiceData       invoice data to write to file
	 *
	 * @return string path on success, false otherwise
	 */
	private function rfStoreInvoice($invoiceReference, $invoiceData)
	{
		$relPath = $this->params->get('invoices_folder', 'images/pdfinvoice/invoices');
		$relPath = ($relPath[0] == '/' || $relPath[0] == '\\') ? $relPath : '/' . $relPath;
		$fullpath = JPATH_ROOT . $relPath;

		if (!JFolder::exists($fullpath))
		{
			if (!JFolder::create($fullpath))
			{
				throw new RuntimeException(JText::_('CANNOT_CREATE_FOLDER') . ': ' . $fullpath);
			}
		}

		$app = JFactory::getApplication();

		require_once JPATH_LIBRARIES . '/tcpdf/tcpdf.php';
		$pdf = new TCPDF;
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('times', '', 12);
		$pdf->setHeaderFont(array('times', '', 10));
		$pdf->SetAuthor($app->getCfg('sitename'));
		$pdf->SetCreator($app->getCfg('sitename'));
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetMargins(8, 8, 8);
		$pdf->SetTitle(str_replace('{id}', $invoiceReference, $this->params->get('pdfinvoice_header', 'invoice-{id}')));

		// Add a page
		$pdf->AddPage();

		$pdf->writeHTML($invoiceData);
		$pdf->Output($fullpath . '/invoice' . $invoiceReference . '.pdf', 'F');

		return $fullpath . '/invoice' . $invoiceReference . '.pdf';
	}

	/**
	 * get invoice
	 *
	 * @param   string  $invoiceReference  invoice reference
	 *
	 * @return string path on success, false otherwise
	 */
	private function rfGetInvoice($invoiceReference)
	{
		$relPath = $this->params->get('invoices_folder', 'images/pdfinvoice/invoices');
		$relPath = ($relPath[0] == '/' || $relPath[0] == '\\') ? $relPath : '/' . $relPath;
		$fullpath = JPATH_ROOT . $relPath;

		return $fullpath . '/invoice' . $invoiceReference . '.pdf';
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
		$path = $this->rfGetInvoice($invoiceId);

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
		$mailer->setSubject(JText::sprintf('PLG_REDFORM_PDFINVOICE_SEND_INVOICE_SUBJECT', $data->title));
		$mailer->MsgHTML(JText::sprintf('PLG_REDFORM_PDFINVOICE_SEND_INVOICE_BODY'));
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

		if (!$res)
		{
			$sid = $this->getSubmissionId($cartId);
			$res = $this->getASubmissionBilling($sid);
		}

		if (!$res->email)
		{
			throw new Exception('E-conomic: billing email is required');
		}

		return $res;
	}

	/**
	 * Get submission id associated to cart
	 *
	 * @param   int  $cartId  cart id
	 *
	 * @return mixed
	 */
	private function getSubmissionId($cartId)
	{
		// No billing, try to find one from previous pr for this sid
		$query = $this->db->getQuery(true)
			->select('pr.submission_id')
			->from('#__rwf_payment_request AS pr')
			->join('INNER', '#__rwf_cart_item AS ci ON ci.payment_request_id = pr.id')
			->where('ci.cart_id = ' . $cartId);

		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	/**
	 * Get any billing associated to sid
	 *
	 * @param   int  $submissionId  submission id
	 *
	 * @return mixed
	 */
	private function getASubmissionBilling($submissionId)
	{
		$query = $this->db->getQuery(true);

		$query->select('b.*')
			->from('#__rwf_billinginfo AS b')
			->join('INNER', '#__rwf_cart_item AS ci ON ci.cart_id = b.cart_id')
			->join('INNER', '#__rwf_payment_request AS pr ON pr.id = ci.payment_request_id')
			->where('pr.submission_id = ' . $submissionId)
			->order('pr.id DESC');

		$this->db->setQuery($query);
		$res = $this->db->loadObject();

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
}
