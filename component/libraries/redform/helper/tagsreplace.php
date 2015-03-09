<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

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
		if (!preg_match_all('/\[([^\]\[\s]+)(?:\s*)([^\]]*)\]/i', $text, $alltags, PREG_SET_ORDER))
		{
			return $text;
		}

		foreach ($alltags as $tag)
		{
			if (method_exists($this, 'getTag' . ucfirst($tag[1])))
			{
				$replace = $this->{'getTag' . ucfirst($tag[1])}();
				$text = str_replace($tag[0], $replace, $text);
			}
			else
			{
				$replace = $this->getAnswerReplace($tag[0]);

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
	 * Replace answer_xx tag with it's field value
	 *
	 * @param   string  $tag  the tag to replace
	 *
	 * @return mixed
	 */
	private function  getAnswerReplace($tag)
	{
		if (!preg_match('/^\[answer_([0-9]+)\]$/', $tag, $match))
		{
			return false;
		}

		$id = $match[1];

		foreach ($this->answers->getAnswers() as $field)
		{
			if ($field['field_id'] == $id)
			{
				if (is_array($field['value']))
				{
					return implode($this->glue, $field['value']);
				}
				else
				{
					return $field['value'];
				}
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
	private function getTotalprice()
	{
		return $this->answers->getPrice();
	}

	/**
	 * replace [answers] tag
	 *
	 * @return string
	 */
	private function getTagAnswers()
	{
		$text = RdfHelperLayout::render('tag.answers',
			$this->answers,
			'',
			array('client' => 0, 'component' => 'com_redform')
		);

		return $text;
	}

	/**
	 * replaces [confirmemail]
	 *
	 * @return string
	 */
	private function getTagConfirmlink()
	{
		$url = JURI::root() . 'index.php?option=com_redform&task=redform.confirm&key=' . $this->answers->getSubmitKey();

		return JRoute::_($url);
	}
}
