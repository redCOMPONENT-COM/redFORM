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
 * Cart tags replacer
 *
 * @since  3.3.17
 */
class RdfHelperTagsCart
{
	/**
	 * @var RdfEntityCart
	 */
	private $cart;

	/**
	 * Constructor
	 *
	 * @param   RdfEntityCart  $cart  cart entity
	 */
	public function __construct(RdfEntityCart $cart)
	{
		$this->cart = $cart;
	}

	/**
	 * Replace tags in text
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

		// Plugins integration
		JPluginHelper::importPlugin('redform_integration');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onRedformTagReplaceCart', array(&$text, $this->cart));

		foreach ($alltags as $tag)
		{
			$parsedTag = new RdfHelperTagsParsed($tag[0]);

			if (method_exists($this, 'replace' . ucfirst($parsedTag->getName())))
			{
				$replace = $this->{'replace' . ucfirst($parsedTag->getName())}($parsedTag);
				$text = str_replace($parsedTag->getFullMatch(), $replace, $text);
			}
			elseif (isset($this->cart->{$parsedTag->getName()}))
			{
				$text = str_replace($parsedTag->getFullMatch(), $this->cart->{$parsedTag->getName()}, $text);
			}
			elseif (strpos($parsedTag->getName(), 'billing_') === 0)
			{
				$billing = $this->cart->getBilling();
				$property = substr($parsedTag->getName(), strlen('billing_'));

				if (isset($billing->{$property}))
				{
					$text = str_replace($parsedTag->getFullMatch(), $billing->{$property}, $text);
				}
			}
		}

		return $text;
	}

	/**
	 * Return substitution for [payment_date]
	 *
	 * @param   RdfHelperTagsParsed  $parsedTag  parsed tag
	 *
	 * @return mixed
	 */
	public function replacePayment_date(RdfHelperTagsParsed $parsedTag)
	{
		$date = JFactory::getDate($this->cart->getPayment()->date);
		$format = $parsedTag->getParam('format');

		return $format ? $date->format($format) : $date->toSql();
	}

	/**
	 * Return substitution for [price]
	 *
	 * @param   RdfHelperTagsParsed  $parsedTag  parsed tag
	 *
	 * @return mixed
	 */
	public function replacePrice(RdfHelperTagsParsed $parsedTag)
	{
		return RHelperCurrency::getFormattedPrice($this->cart->price, $this->cart->currency);
	}

	/**
	 * Return substitution for [total]
	 *
	 * @param   RdfHelperTagsParsed  $parsedTag  parsed tag
	 *
	 * @return mixed
	 */
	public function replaceTotal(RdfHelperTagsParsed $parsedTag)
	{
		$total = $this->cart->price + $this->cart->vat;

		return RHelperCurrency::getFormattedPrice($total, $this->cart->currency);
	}

	/**
	 * Return substitution for [vat]
	 *
	 * @param   RdfHelperTagsParsed  $parsedTag  parsed tag
	 *
	 * @return mixed
	 */
	public function replaceVat(RdfHelperTagsParsed $parsedTag)
	{
		return RHelperCurrency::getFormattedPrice($this->cart->vat, $this->cart->currency);
	}
}
