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
		return isset($this->xml->assignTo) ? (int) $this->xml->assignTo : false;
	}

	/**
	 * Return autoreply value
	 *
	 * @return boolean
	 */
	public function getAutoreply()
	{
		return isset($this->xml->autoreply) ? (bool) $this->xml->autoreply : false;
	}

	/**
	 * Return posted email
	 *
	 * @param   RdfAnswers  $submission  submission
	 *
	 * @return mixed
	 */
	public function getSubmissionEmail(RdfAnswers $submission)
	{
		return $this->getXmlConfigTagSubmissionFieldValue('emailFieldId', $submission, true);
	}

	/**
	 * Return posted subject
	 *
	 * @param   RdfAnswers  $submission  submission
	 *
	 * @return mixed
	 */
	public function getSubmissionSubject(RdfAnswers $submission)
	{
		if (isset($this->xml->subject))
		{
			return $submission->replaceTags((string) $this->xml->subject);
		}

		return $this->getXmlConfigTagSubmissionFieldValue('subjectFieldId', $submission, true);
	}

	/**
	 * Return posted subject
	 *
	 * @param   RdfAnswers  $submission  submission
	 *
	 * @return mixed
	 */
	public function getSubmissionBody(RdfAnswers $submission)
	{
		if (isset($this->xml->body))
		{
			return $submission->replaceTags((string) $this->xml->body);
		}

		return $this->getXmlConfigTagSubmissionFieldValue('bodyFieldId', $submission, true);
	}

	/**
	 * Return posted firstname
	 *
	 * @param   RdfAnswers  $submission  submission
	 *
	 * @return mixed
	 */
	public function getSubmissionFirstname(RdfAnswers $submission)
	{
		return $this->getXmlConfigTagSubmissionFieldValue('firstNameFieldId', $submission);
	}

	/**
	 * Return posted lastname
	 *
	 * @param   RdfAnswers  $submission  submission
	 *
	 * @return mixed
	 */
	public function getSubmissionLastname(RdfAnswers $submission)
	{
		return $this->getXmlConfigTagSubmissionFieldValue('lastnameFieldId', $submission);
	}

	/**
	 * Return posted Organization
	 *
	 * @param   RdfAnswers  $submission  submission
	 *
	 * @return mixed
	 */
	public function getSubmissionOrganization(RdfAnswers $submission)
	{
		return $this->getXmlConfigTagSubmissionFieldValue('organizationFieldId', $submission);
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

	/**
	 * Return attachements absolute paths
	 *
	 * @param   RdfAnswers  $submission  submission
	 *
	 * @return mixed
	 */
	public function getAttachments(RdfAnswers $submission)
	{
		if (isset($this->xml->attachments) && count($this->xml->attachments->fieldId))
		{
			$res = array();

			foreach ($this->xml->attachments->fieldId as $fieldId)
			{
				$fieldId = (int) $fieldId;

				foreach ($submission->getFields() AS $field)
				{
					if ($field->fieldId == $fieldId)
					{
						if ($path = $field->getValueAsString())
						{
							$res[] = $path;
						}
					}
				}
			}

			return $res;
		}

		return false;
	}

	/**
	 * Return field value from submission
	 *
	 * @param   string      $xmlFieldIdTag  field id xml tag
	 * @param   RdfAnswers  $submission     answers
	 * @param   bool        $required       is it required
	 *
	 * @return string|bool
	 *
	 * @throws InvalidArgumentException
	 */
	private function getXmlConfigTagSubmissionFieldValue($xmlFieldIdTag, $submission, $required = false)
	{
		if (!isset($this->xml->{$xmlFieldIdTag}) && $required)
		{
			if ($required)
			{
				throw new InvalidArgumentException($xmlFieldIdTag . ' tag not found in config xml');
			}

			return false;
		}

		$fieldId = (int) $this->xml->{$xmlFieldIdTag};

		foreach ($submission->getFields() AS $field)
		{
			if ($field->fieldId == $fieldId)
			{
				return $field->getValueAsString();
			}
		}

		if ($required)
		{
			throw new InvalidArgumentException($xmlFieldIdTag . ' matching field not found in submission');
		}

		return false;
	}
}
