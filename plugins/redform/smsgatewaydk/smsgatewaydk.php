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
		$message = $input->get('TEXT');
		$phoneNumber = $input->get('MOBILE');

		if (!preg_match('/^([0-9]+)[\s]*OK/', $message, $matches))
		{
			$this->sendMessage($phoneNumber, $this->params->get('error'));

			return;
		}

		$sid = $matches[1];
		$this->confirmSid($sid);
		$this->sendMessage($phoneNumber, $this->params->get('confirmation'));

		$updatedIds[] = $sid;
	}

	/**
	 * Confirm sid
	 *
	 * @param   int  $sid  submitter id
	 *
	 * @return bool
	 */
	private function confirmSid($sid)
	{
		$db = JFactory::getDbo();
		$date = JFactory::getDate()->toSql();

		$query = $db->getQuery(true);

		$query->update('#__rwf_submitters')
			->set('confirmed_date = ' . $db->quote($date))
			->set('confirmed_type = ' . $db->quote('sms'))
			->where('id = ' . $db->quote($sid));

		$db->setQuery($query);
		$db->execute();

		JPluginHelper::importPlugin('redform');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onSubmissionConfirmed', array(array($sid)));

		return true;
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

		$url1 = "http://smschannel1.dk";
		$url2 = "http://smschannel2.dk";
		$str = "/sendsms/";
		$str .= "?username=" . $this->params->get('username');
		$str .= "&password=" . $this->params->get('password');
		$str .= "&to=" . $phoneNumber;
		$str .= "&from=" . $this->params->get('from');
		$str .= "&message=" . urlencode($message);

		if (file_get_contents($url1))
		{
			$res = file_get_contents($url1 . $str);
			RdfHelperLog::simpleLog('SMSGateway.dk message sent: ' . $url1 . $str);
		}
		elseif (file_get_contents($url2))
		{
			$res = file_get_contents($url2 . $str);
			RdfHelperLog::simpleLog('SMSGateway.dk message sent: ' . $url2 . $str);
		}
		else
		{
			throw new RuntimeException('SMSGateway error');
		}

		RdfHelperLog::simpleLog('SMSGateway.dk message response: ' . $res);
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
