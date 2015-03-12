<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.helpscout
 *
 * @copyright   Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

/**
 * helpscout lib
 *
 * @package     Redform.Plugin
 * @subpackage  Redform.helpscout
 * @since       3.0
 */
class PlghsConfigForm
{
	private $xml;

	/**
	 * Constructor
	 *
	 * @param   SimpleXMLElement  $xml  xml config
	 */
	public function __construct(SimpleXMLElement $xml)
	{
		$this->xml = $xml;
	}

	/**
	 * Return associated mailbox id
	 *
	 * @return int
	 */
	public function getMailboxId()
	{
		return (int) $this->xml->mailboxId;
	}

	/**
	 * Return associated mailbox id
	 *
	 * @return int
	 */
	public function getAssignedTo()
	{
		return isset($this->xml->assignTo) ?(int) $this->xml->assignTo : false;
	}

	/**
	 * Return posted email
	 *
	 * @param   RdfAnswers  $submission  submission
	 *
	 * @return mixed
	 *
	 * @throws InvalidArgumentException
	 */
	public function getSubmissionEmail(RdfAnswers $submission)
	{
		$fieldId = (int) $this->xml->emailFieldId;

		foreach ($submission->getFields() AS $field)
		{
			if ($field->fieldId == $fieldId)
			{
				return $field->getValueAsString();
			}
		}

		throw new InvalidArgumentException('Email field not found');
	}

	/**
	 * Return posted subject
	 *
	 * @param   RdfAnswers  $submission  submission
	 *
	 * @return mixed
	 *
	 * @throws InvalidArgumentException
	 */
	public function getSubmissionSubject(RdfAnswers $submission)
	{
		$fieldId = (int) $this->xml->subjectFieldId;

		foreach ($submission->getFields() AS $field)
		{
			if ($field->fieldId == $fieldId)
			{
				return $field->getValueAsString();
			}
		}

		throw new InvalidArgumentException('subject field not found');
	}

	/**
	 * Return posted subject
	 *
	 * @param   RdfAnswers  $submission  submission
	 *
	 * @return mixed
	 *
	 * @throws InvalidArgumentException
	 */
	public function getSubmissionBody(RdfAnswers $submission)
	{
		$fieldId = (int) $this->xml->bodyFieldId;

		foreach ($submission->getFields() AS $field)
		{
			if ($field->fieldId == $fieldId)
			{
				return $field->getValueAsString();
			}
		}

		throw new InvalidArgumentException('body field not found');
	}

	/**
	 * Return tags
	 *
	 * @return mixed
	 */
	public function getTags()
	{
		if (isset($this->xml->tags) && count($this->xml->tags->tag))
		{
			$res = array();

			foreach ($this->xml->tags->tag as $tag)
			{
				$res[] = (string) $tag;
			}

			return $res;
		}

		return false;
	}

	/**
	 * Return ccList
	 *
	 * @return mixed
	 */
	public function getCclist()
	{
		if (isset($this->xml->ccList) && count($this->xml->ccList->email))
		{
			$res = array();

			foreach ($this->xml->ccList->email as $value)
			{
				$res[] = (string) $value;
			}

			return $res;
		}

		return false;
	}

	/**
	 * Return BccList
	 *
	 * @return mixed
	 */
	public function getBcclist()
	{
		if (isset($this->xml->bccList) && count($this->xml->bccList->email))
		{
			$res = array();

			foreach ($this->xml->bccList->email as $value)
			{
				$res[] = (string) $value;
			}

			return $res;
		}

		return false;
	}
}
