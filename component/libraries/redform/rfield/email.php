<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Rfield
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * redFORM field
 *
 * @package     Redform.Libraries
 * @subpackage  Rfield
 * @since       2.5
 */
class RDFRfieldEmail extends RDFRfieldTextfield
{
	protected $type = 'email';

	/**
	 * Selected newsletters from post
	 * @var array
	 */
	protected $selectedNewsletters = null;

	/**
	 * Set field value from post data
	 *
	 * @param   string  $value  value
	 *
	 * @return string new value
	 *
	 * @throws LogicException
	 */
	public function setValueFromPost($value)
	{
		if (!is_array($value) || !isset($value['email']))
		{
			throw new LogicException('wrong input for email field set value ');
		}

		$this->value = $value['email'];

		if (isset($value['newsletter']))
		{
			$this->selectedNewsletters = $value['newsletter'];
		}

		return $this->value;
	}

	/**
	 * Return selected newsletters
	 *
	 * @return array
	 */
	public function getSelectedNewsletters()
	{
		return $this->selectedNewsletters ? $this->selectedNewsletters : array();
	}

	/**
	 * Returns field Input
	 *
	 * @return string
	 */
	public function getInput()
	{
		$properties = $this->getInputProperties();

		$element = "<div class=\"emailfields\">";
		$element .= "<div class=\"emailfield\">";
		$element .= sprintf('<input %s/>', $this->propertiesToString($properties));
		$element .= "</div>\n";

		if ($newsletter = $this->getNewsletters())
		{
			$element .= $this->addNewslettersElements($newsletter);
		}

		$element .= "</div>\n";

		return $element;
	}

	/**
	 * Get postfixed field name for form
	 *
	 * @return string
	 */
	public function getFormElementName()
	{
		$name = 'field' . $this->id;

		if ($this->formIndex)
		{
			$name .= '_' . $this->formIndex;
		}

		$name .= '[email]';

		return $name;
	}

	/**
	 * Get postfixed field name for form
	 *
	 * @return string
	 */
	protected function getFormListElementName()
	{
		$name = 'field' . $this->id;

		if ($this->formIndex)
		{
			$name .= '.' . $this->formIndex;
		}

		$name .= '[newsletter][]';

		return $name;
	}

	/**
	 * Return input properties array
	 *
	 * @return array
	 */
	protected function getInputProperties()
	{
		$properties = parent::getInputProperties();

		if (isset($properties['class']) && $properties['class'])
		{
			$properties['class'] .= ' validate-email';
		}
		else
		{
			$properties['class'] = 'validate-email';
		}

		return $properties;
	}

	/**
	 * Try to get a default value from integrations
	 *
	 * @return void
	 */
	protected function lookupDefaultValue()
	{
		if ($this->formIndex == 1 && $this->user && $this->user->email)
		{
			$this->value = $this->user->email;
		}
		else
		{
			$this->value = parent::lookupDefaultValue();
		}

		return $this->value;
	}

	protected function getNewsletters()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('listnames');
		$query->from('#__rwf_mailinglists');
		$query->where('field_id = ' . $this->id);

		$db->setQuery($query);
		$res = $db->loadResult();

		if ($res)
		{
			return explode(';', $res);
		}
		else
		{
			return false;
		}
	}

	protected function addNewslettersElements($newsletters)
	{
		$element = '';

		if ($this->getParam('force_mailing_list', 0))
		{
			// Auto subscribe => use hidden field
			foreach ($newsletters as $listname)
			{
				$element .= '<input type="hidden" name="' . $this->getFormListElementName() . '" value="' . $listname . '" />';
			}
		}
		else
		{
			$element .= '<div class="newsletterfields">';
			$element .= '<div id="signuptitle">' . JText::_('COM_REDFORM_SIGN_UP_MAILINGLIST') . '</div>';
			$element .= '<div class="fieldemail_listnames">';

			foreach ($newsletters AS $listkey => $listname)
			{
				$element .= '<div class="field_"' . $listkey . '">';
				$element .= '<input type="checkbox" name="' . $this->getFormListElementName() . '" value="' . $listname . '" />';
				$element .= $listname;
				$element .= '</div>';
			}

			$element .= "</div>\n";
			$element .= "</div>\n";
		}

		return $element;
	}
}
