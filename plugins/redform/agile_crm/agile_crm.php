<?php
/**
 * @package     redFORM
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Integrates redFORM with Agle CRM
 *
 * @package  redFORM.Plugin
 * @since    3.0
 */
class PlgRedformAgile_Crm extends JPlugin
{
	private $submitKey;

	private $answers;

	private $redFormCore;

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
	}

	/**
	 * Called after a submission
	 *
	 * @param   object  $result  result of submission
	 *
	 * @return void
	 */
	public function onAfterRedformSavedSubmission($result)
	{
		if (!$result || !$result->submit_key)
		{
			return;
		}

		$this->answers = array();
		$this->submitKey = $result->submit_key;

		try
		{
			$submissions = $this->getAnswers();
			$sid = $submissions->getSids($this->submitKey);

			foreach ($submissions->getSingleSubmissions() as $submission)
			{
				$inputs = array();
				$inputs['price'] = $this->getPrice($sid[0]);

				foreach ($submission->getFields() AS $field)
				{
					$inputs[$field->fieldId] = $field->getValueAsString();
				}
			}

			$this->createContact($inputs);
			$this->createDeal($inputs);
		}
		catch (Exception $e)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($e->getMessage(), 'error');
		}
	}

	/**
	 * Function to create a contact on Agile CRM
	 *
	 * @param   array  $data  redFORM data
	 *
	 * @return  void
	 */
	public function createContact($data)
	{
		$address = array(
			"address" => "",
			"city" => $data[15],
			"state" => "",
			"country" => ""
		);

		$contactEmail = $data[17];

		$exist = $this->curlWrap("contacts/search/email/" . $contactEmail, null, "GET", "application/json");

		if (!empty($exist))
		{
			$json = json_decode($exist);
			$this->contact_id = $json->id;

			return;
		}

		$contactJson = array(
			"properties" => array(
				array(
					"name" => "first_name",
					"value" => $data[11],
					"type" => "SYSTEM"
				),
				array(
					"name" => "email",
					"value" => $contactEmail,
					"type" => "SYSTEM"
				),  
				array(
					"name" => "address",
					"value" => json_encode($address),
					"type" => "SYSTEM"
				),
				array(
					"name" => "company",
					"value" => $data[12],
					"type" => "SYSTEM"
				),
				array(
					"name" => "phone",
					"value" => $data[16],
					"type" => "SYSTEM"
				),
				array(
					"name" => "Date Of Joining",
					"value" => date('Y-m-d H:i:s'),
					"type" => "CUSTOM"
				)
			)
		);

		$contactJson = json_encode($contactJson);

		$return = $this->curlWrap("contacts", $contactJson, "POST", "application/json");
		$json = json_decode($return);
		$this->contact_id = $json->id;
	}

	/**
	 * Function to create a deal on Agile CRM
	 *
	 * @param   array  $data  redFORM data
	 *
	 * @return  void
	 */
	public function createDeal($data)
	{
		$tags = explode(' ', trim(strtolower($data[25])));
		$opportunityJson = array(
			"name" => $data[25],
			"description" => "",
			"expected_value" => $data['price'],
			"milestone" => $this->params->get('milestone', 'New'),
			"probability" => $this->params->get('probability', 50),
			"owner_id" => $this->params->get('owner', 0),
			"close_date"=>time(),
			"contact_ids" => array($this->contact_id),
			"deal_source_id" => $this->params->get('deal_source', 0),
			"tags" => $tags,
			"tagsWithTime" => $tags
		);

		$opportunityJson = json_encode($opportunityJson);

		$return = $this->curlWrap("opportunity", $opportunityJson, "POST", "application/json");
		$json = json_decode($return);
		
		return JFactory::getSession()->set('deal_id', $json->id);
	}

	/**
	 * Triggered after a form submissin has been saved.
	 *
	 * @param   RdfCoreFormSubmission  $result  The result
	 *
	 * @return  void
	 */
	public function curlWrap($entity, $data, $method, $contentType) 
	{
		if ($contentType == NULL) 
		{
		    $contentType = "application/json";
		}

		$agileUrl = "https://" . $this->params->get('domain') . ".agilecrm.com/dev/api/" . $entity;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);

		switch ($method) 
		{
			case "POST":
				$url = $agileUrl;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				break;
			case "GET":
				$url = $agileUrl;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
				break;
			case "PUT":
				$url = $agileUrl;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				break;
			case "DELETE":
				$url = $agileUrl;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;
			default:
				break;
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    "Content-type : $contentType;", 'Accept : application/json'
		));

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, $this->params->get('email') . ':' . $this->params->get('api_key'));
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$output = curl_exec($ch);
		curl_close($ch);

		return $output;
	}

	/**
	 * get order id
	 *
	 * @param   int  $cartId  cart ID
	 *
	 * @return object
	 */
	public function getPrice($submission)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('price')
			->from($db->qn('#__rwf_submission_price_item'))
			->where($db->qn('submission_id') . ' = ' . $db->q((int) $submission));

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Return answers for sid
	 *
	 * @return RdfCoreFormSubmission
	 */
	private function getAnswers()
	{
		if (!$this->answers)
		{
			$this->answers = $this->getRedFormCore()->getAnswers($this->submitKey);
		}

		return $this->answers;
	}

	/**
	 * Get redformcore
	 *
	 * @return RedFormCore
	 */
	private function getRedFormCore()
	{
		if (!$this->redFormCore)
		{
			$this->redFormCore = new RdfCore;
		}

		return $this->redFormCore;
	}
}
