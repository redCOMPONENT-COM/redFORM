<?php
/**
 * @package     Redform.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Submitter entity.
 *
 * @since  3.0
 */
class RdfEntitySubmitter extends RdfEntityBase
{
	/**
	 * Get associated form
	 *
	 * @return RdfEntityForm
	 */
	public function getForm()
	{
		$item = $this->getItem();

		if (!$item)
		{
			return false;
		}

		return RdfEntityForm::load($item->form_id);
	}

	/**
	 * Return array of RdfEntitySubmitter
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return array
	 */
	public static function loadBySubmitKey($submit_key)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('s.*')
			->from('#__rwf_submitters AS s')
			->where('s.submit_key = ' . $db->q($submit_key));

		$db->setQuery($query);
		$res = $db->loadObjectList();

		if (!$res)
		{
			return false;
		}

		$submitters = array_map(
			function($item)
			{
				$submitter = RdfEntitySubmitter::getInstance($item->id);
				$submitter->bind($item);

				return $submitter;
			},
			$res
		);

		return $submitters;
	}

	/**
	 * Return GA ecommerce js tracking code for submitter
	 *
	 * @param   array  $options  optional parameters for tracking
	 *
	 * @return string js code
	 */
	public function recordTransJs($options = array())
	{
		$item = $this->getItem();

		if (!$item)
		{
			return false;
		}

		// Add transaction
		$trans = new stdclass;
		$trans->id = $item->submit_key . '-' . $item->id;
		$trans->affiliation = isset($options['affiliate']) ? $options['affiliate'] : $item->getForm()->name;
		$trans->revenue = $item->price;
		$trans->currency = $item->currency;

		$js = self::addTrans($trans);

		$productname = isset($options['productname']) ? $options['productname'] : null;
		$sku         = isset($options['sku']) ? $options['sku'] : null;
		$category    = isset($options['category']) ? $options['category'] : null;

		$item = new stdclass;
		$item->id = $item->id;
		$item->productname = $productname ? $productname : 'submitter' . $s->id;
		$item->sku  = $sku ? $sku : 'submitter' . $s->id;
		$item->category  = $category ? $category : '';
		$item->price = $s->price;
		$item->currency = $s->currency;

		$js .= self::addItem($item);

		// Push transaction to server
		$js .= self::trackTrans();

		return $js;
	}
}
