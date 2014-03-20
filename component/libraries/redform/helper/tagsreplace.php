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
 * Class RedformHelperTagsreplace
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 */
class RedformHelperTagsreplace
{
	private $answers;
	private $formdata;

	/**
	 * Contructor
	 *
	 * @param   object  $formdata  form data
	 * @param   array   $answers   answers to form
	 */
	public function __construct($formdata, $answers)
	{
		$this->formdata = $formdata;
		$this->answers = $answers;
	}

	/**
	 * Replaces tags in text
	 *
	 * @param   string  $text  text
	 *
	 * @return string
	 */
	public function replace($text)
	{
		if (!preg_match_all('/\[([^\]\[\s]+)(?:\s*)([^\]]*)\]/i', $text, $alltags, PREG_SET_ORDER))
		{
			return $text;
		}

		foreach ($alltags as $tag)
		{
			if ($tag[1] == 'formname')
			{
				$text = str_replace($tag[0], $this->formdata->formname, $text);
			}
			elseif ($replace = $this->getAnswerReplace($tag[0]))
			{
				$text = str_replace($tag[0], $replace, $text);
			}
		}

		return $text;
	}

	private function getAnswerReplace($tag)
	{
		if (!preg_match('/^\[answer_([0-9]+)\]$/', $tag, $match))
		{
			return false;
		}

		$id = $match[1];

		foreach ($this->answers as $answer)
		{
			if ($answer['field_id'] == $id)
			{
				return $answer['value'];
			}
		}

		return false;
	}
}
