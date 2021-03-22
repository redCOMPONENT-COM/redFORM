<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

/**
 * Class RdfHelperTagsreplace
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 * @since       2.5
 */
class RdfHelperTagsreplace
{
	/**
	 * @var array|RdfAnswers
	 */
	private $answers;

	/**
	 * @var objects
	 */
	private $formdata;

	/**
	 * Glue to use for imploding fields array value
	 *
	 * @var string
	 */
	private $glue;

	/**
	 * Return supported conditions
	 *
	 * @return array
	 */
	public static function getConditions()
	{
		static $conditions;

		if (is_null($conditions))
		{
			$conditions = [];

			// Plugins integration
			JPluginHelper::importPlugin('redform_replacecondition');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onRedformGetConditions', array(&$conditions));
		}

		return $conditions;
	}

	/**
	 * Contructor
	 *
	 * @param   object      $formdata  form data
	 * @param   RdfAnswers  $answers   answers to form
	 * @param   string      $glue      Glue to use for imploding fields array value
	 */
	public function __construct($formdata, RdfAnswers $answers, $glue = ',')
	{
		$this->formdata = $formdata;
		$this->answers = $answers;
		$this->glue = $glue;
	}

	/**
	 * Replaces tags in text
	 *
	 * @param   string  $text   text
	 * @param   array   $extra  extra associative array for custom replacements
	 *
	 * @return string
	 */
	public function replace($text, $extra = array())
	{
		$text = $this->processStructure($text);

		if (!preg_match_all('/\[([^\]\[\s]+)(?:\s*)([^\]]*)\]/i', $text, $alltags, PREG_SET_ORDER))
		{
			return $text;
		}

		// Plugins integration
		JPluginHelper::importPlugin('redform_integration');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onRedformTagReplace', array(&$text, $this->formdata, $this->answers));

		foreach ($alltags as $tag)
		{
			if (method_exists($this, 'getTag' . ucfirst($tag[1])))
			{
				$replace = $this->{'getTag' . ucfirst($tag[1])}();
				$text = str_replace($tag[0], $replace, $text);
			}
			else
			{
				$replace = $this->getFieldReplace($tag[0]);

				if ($replace !== false)
				{
					$text = str_replace($tag[0], $replace, $text);
				}
			}
		}

		if ($extra)
		{
			foreach ($extra as $tag => $replace)
			{
				$text = str_replace($tag, $replace, $text);
			}
		}

		return $text;
	}

	/**
	 * Process supported structures
	 *
	 * @param   string  $text  the text to parse
	 *
	 * @return string
	 */
	private function processStructure($text)
	{
		if (!preg_match_all('/\[rdfif:([^\]\[\s]+)\]((?:(?!\[rdfendif\]|\[rdfif\]).)*)\[rdfendif\]/is', $text, $alltags, PREG_SET_ORDER))
		{
			return $text;
		}

		$loop = false;

		foreach ($alltags as $parts)
		{
			$isValid = $this->isConditionValid($parts[1]);

			if (is_null($isValid))
			{
				continue;
			}

			if ($isValid)
			{
				$text = str_replace($parts[0], $parts[2], $text);
			}
			else
			{
				$text = str_replace($parts[0], '', $text);
			}

			$loop = true;
		}

		// Pass again in case this was an inner rdfif
		return $loop ? $this->processStructure($text) : $text;
	}

	/**
	 * Check if condition is valid
	 *
	 * @param   string  $condition  condition
	 *
	 * @return boolean
	 */
	private function isConditionValid($condition)
	{
		$parts = explode(';', $condition);
		$isValid = null;

		// Plugins integration
		JPluginHelper::importPlugin('redform_replacecondition');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onRedformProcessReplaceCondition', array($parts, $this->answers, &$isValid));

		return $isValid;
	}

	/**
	 * Replace field_xx tag with it's field value
	 *
	 * @param   string  $tag  the tag to replace
	 *
	 * @return mixed
	 */
	private function getFieldReplace($tag)
	{
		if (preg_match('/^\[field_([0-9]+)\]$/', $tag, $match))
		{
			$id = $match[1];
		}
		else
		{
			return $this->getAnswerReplace($tag);
		}

		foreach ($this->answers->getFields() as $field)
		{
			if ($field->field_id === $id)
			{
				return $field->renderValue($this->glue);
			}
		}

		return false;
	}

	/**
	 * Replace answer_xx tag with it's field value
	 *
	 * @param   string  $tag  the tag to replace
	 *
	 * @return mixed
	 */
	private function getAnswerReplace($tag)
	{
		if (!preg_match('/^\[answer_([0-9]+)\]$/', $tag, $match))
		{
			return false;
		}

		$id = $match[1];

		foreach ($this->answers->getFields() as $field)
		{
			if ($field->id == $id)
			{
				return $field->renderValue($this->glue);
			}
		}

		return false;
	}

	/**
	 * replace [submitkey] tag
	 *
	 * @return string
	 */
	private function getTagSubmitkey()
	{
		return $this->answers->getSubmitKey();
	}

	/**
	 * replace [formname] tag
	 *
	 * @return string
	 */
	private function getTagFormname()
	{
		return $this->formdata->formname;
	}

	/**
	 * replace [totalprice] tag
	 *
	 * @return string
	 */
	private function getTagTotalprice()
	{
		return RdfHelper::formatPrice($this->answers->getPrice() + $this->answers->getVat(), $this->answers->getCurrency());
	}

	/**
	 * replace [totalvat] tag
	 *
	 * @return string
	 */
	private function getTagTotalvat()
	{
		return RdfHelper::formatPrice($this->answers->getVat(), $this->answers->getCurrency());
	}

	/**
	 * replace [totalpricevatexcluded] tag
	 *
	 * @return string
	 */
	private function getTagTotalpricevatexcluded()
	{
		return RdfHelper::formatPrice($this->answers->getPrice(), $this->answers->getCurrency());
	}

	/**
	 * replace [answers] tag
	 *
	 * @return string
	 */
	private function getTagAnswers()
	{
		$text = RdfLayoutHelper::render('tag.answers',
			$this->answers,
			'',
			array('component' => 'com_redform')
		);

		return $text;
	}

	/**
	 * replaces [confirmlink]
	 *
	 * @return string
	 */
	private function getTagConfirmlink()
	{
		$url = JURI::root() . 'index.php?option=com_redform&task=redform.confirm&key=' . $this->answers->getSubmitKey();

		return JRoute::_($url);
	}

	/**
	 * replaces [confirmlink]
	 *
	 * @return string
	 */
	private function getTagConfirmlink_Relative()
	{
		return 'index.php?option=com_redform&task=redform.confirm&key=' . $this->answers->getSubmitKey();
	}

	/**
	 * replaces [paymentlink]
	 *
	 * @return string
	 */
	private function getTagPaymentlink()
	{
		$url = JURI::root() . RdfHelperRoute::getPaymentRoute($this->answers->getSubmitKey());

		return JRoute::_($url);
	}

	/**
	 * replaces [submitter_id]
	 *
	 * @return string
	 */
	private function getTagSubmitter_id()
	{
		return $this->answers->sid;
	}

	/**
	 * replaces [base_url]
	 *
	 * @return string
	 */
	private function getTagBase_url()
	{
		return rtrim(JURI::root(), '/');
	}
}
