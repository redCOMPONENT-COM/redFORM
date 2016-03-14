<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.gaselkmd
 *
 * @copyright   Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
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
 * SMSgateway.dk integration
 *
 * @package     Redform.Plugin
 * @subpackage  Redform.gaselkmd
 * @since       3.0
 */
class plgRedformSmsgatewaydk extends JPlugin
{
	private $answers;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   2.0
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Send notification by email on submission
	 *
	 * @param   object  $formSubmission  form submission result
	 *
	 * @return void
	 */
	public function onAfterRedformSavedSubmission($formSubmission)
	{
		if (isset($formSubmission->posts) && count($formSubmission->posts))
		{
			foreach ($formSubmission->posts as $post)
			{
				$this->sendNotificationMessage($post['sid']);
			}
		}
	}

	/**
	 * Callback
	 *
	 * @param   string  $plugin       plugin name
	 * @param   array   &$updatedIds  updated ids
	 *
	 * @return void
	 */
	public function onConfirm($plugin, &$updatedIds)
	{
		if (!($plugin == 'smsgatewaydk'))
		{
			return;
		}

		$input = JFactory::getApplication()->input;
		$message = $input->get('SMS');
		$phoneNumber = $input->get('FROM');
		RdfHelperLog::simpleLog('received message on smsgatewaydk (mobile: ' . $phoneNumber . '):' . $message);

		if (!preg_match('/([0-9]+)[\s]*ok/i', $message, $matches))
		{
			RdfHelperLog::simpleLog('Smsgatewaydk error: incorrect format');
			$this->sendMessage($phoneNumber, $this->params->get('error'));

			return;
		}

		$sid = $matches[1];

		if ($this->isConfirmed($sid))
		{
			$this->sendMessage($phoneNumber, JText::_('PLG_REDFORM_SMSGATEWAYDK_ERROR_ALREADY_CONFIRMED'));

			return;
		}

		$this->confirmSid($sid, $phoneNumber);
		$this->sendMessage($phoneNumber, $this->params->get('confirmation'));

		$updatedIds[] = $sid;
	}

	/**
	 * For testing
	 *
	 * @return void
	 */
	public function onAjaxTestsendsms()
	{
		$input = JFactory::getApplication()->input;
		$message = $input->get('message', 'A simple test with æøå barbaric characters');
		$number = $input->get('mobile');

		if ($number)
		{
			$this->sendMessage($number, $message);
		}
	}

	/**
	 * Confirm sid
	 *
	 * @param   int     $sid          submitter id
	 * @param   string  $phoneNumber  number used to confirm
	 *
	 * @return bool
	 */
	private function confirmSid($sid, $phoneNumber)
	{
		$db = JFactory::getDbo();
		$date = JFactory::getDate()->toSql();

		$query = $db->getQuery(true);

		$query->update('#__rwf_submitters')
			->set('confirmed_date = ' . $db->quote($date))
			->set('confirmed_type = ' . $db->quote('sms'))
			->set('confirmed_ip = ' . $db->quote($phoneNumber))
			->where('id = ' . $db->quote($sid));

		$db->setQuery($query);
		$db->execute();

		JPluginHelper::importPlugin('redform');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onSubmissionConfirmed', array(array($sid)));

		return true;
	}

	/**
	 * Check if sid already confirmed
	 *
	 * @param   int  $sid  submitter id
	 *
	 * @return boolean
	 */
	private function isConfirmed($sid)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from('#__rwf_submitters')
			->where('confirmed_date = 0')
			->where('id = ' . $db->quote($sid));

		$db->setQuery($query);

		return $db->loadResult() == $sid ? false : true;
	}

	/**
	 * Send notification message
	 *
	 * @param   int  $sid  submitter id
	 *
	 * @return void
	 */
	private function sendNotificationMessage($sid)
	{
		try
		{
			$answers = $this->getAnswers($sid);

			$formFields = $this->params->get('forms');

			if (!is_array($formFields) || !in_array($answers->getFormId(), $formFields))
			{
				return;
			}

			$notification = $this->params->get('notification');

			$replacer = new RdfHelperTagsreplace(null, $answers);
			$notification = $replacer->replace($notification, array('[confirmid]' => $sid));

			$phone = $this->getPhone($answers);

			$this->sendMessage($phone, $notification);
		}
		catch (Exception $e)
		{
			RdfHelperLog::simpleLog('SMSGateway.dk notification error: ' . $e->getMessage());
		}
	}

	/**
	 * Get phone number
	 *
	 * @param   RdfAnswers  $answers  answers
	 *
	 * @return string
	 *
	 * @throws InvalidArgumentException
	 */
	private function getPhone($answers)
	{
		$phoneFields = $this->params->get('phoneFieldIds');

		if (!is_array($phoneFields))
		{
			throw new InvalidArgumentException('Phone fields not defined');
		}

		foreach ($answers->getFields() as $field)
		{
			if (in_array($field->field_id, $phoneFields))
			{
				return $field->value;
			}
		}

		throw new InvalidArgumentException('No phone number found');
	}

	/**
	 * Clean and check phone number
	 *
	 * @param   string  $phoneNumber  phone number
	 *
	 * @return string
	 *
	 * @throws InvalidArgumentException
	 */
	private function cleanPhone($phoneNumber)
	{
		$cleaned = str_replace(array(' ', '+'), '', $phoneNumber);

		if (!preg_match('/^[0-9]+$/', $cleaned))
		{
			throw new InvalidArgumentException('Invalid phone number: ' . $phoneNumber);
		}

		if (strlen($cleaned) == 8)
		{
			$cleaned = '+45' . $cleaned;
		}

		return $cleaned;
	}

	/**
	 * Send the sms
	 *
	 * @param   string  $phoneNumber  phone number
	 * @param   string  $message      message to send
	 *
	 * @return void
	 *
	 * @throws RunTimeException
	 */
	private function sendMessage($phoneNumber, $message)
	{
		$phoneNumber = $this->cleanPhone($phoneNumber);

		$url = "http://restapi.smsgateway.dk/v2/message.json?apikey=" . $this->params->get('apikey');

		$json = json_encode(
				array(
					"message" => array(
						"recipients" => $phoneNumber,
						"sender" => $this->params->get('from'),
						"message" => $message,
						"charset" => "UTF-8"
					)
				)
			);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);

		if (!$response)
		{
			RdfHelperLog::simpleLog('SMSGateway error: ' . curl_error($ch));
			throw new RuntimeException('SMSGateway error');
		}

		$jsonResponse = json_decode($response);

		if ($jsonResponse->status != 200)
		{
			RdfHelperLog::simpleLog('SMSGateway error: ' . $response);
			throw new RuntimeException('SMSGateway error: ' . $response);
		}

		RdfHelperLog::simpleLog('SMSGateway.dk message sent: ' . json_encode($message));
		RdfHelperLog::simpleLog('SMSGateway.dk message response: ' . $response);
	}

	/**
	 * return answers
	 *
	 * @param   int  $sid  submission id
	 *
	 * @return RdfAnswers
	 */
	private function getAnswers($sid)
	{
		$rfcore = new RdfCore;

		return $rfcore->getSidAnswers($sid);
	}
}
