<?php
/**
 * @package     Redform
 * @subpackage  Payment.cybersource
 * @copyright   Copyright (C) 2008-2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

// Register library prefix
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

/**
 * Cybersource payment plugin
 *
 * @package     Redform.plugins
 * @subpackage  payment
 * @since       2.5
 */
class PlgRedform_PaymentCybersource extends RdfPaymentPlugin
{
	protected $gateway = 'cybersource';

	/**
	 * Render a tmpl file
	 *
	 * @param   string  $path  path
	 * @param   array   $data  data
	 *
	 * @return string
	 */
	public function render($path, $data)
	{
		$path = $this->getLayoutPath($path);

		$layoutOutput = '';

		if (!empty($path))
		{
			ob_start();
			include $path;
			$layoutOutput = ob_get_contents();
			ob_end_clean();
		}

		return $layoutOutput;
	}
}
