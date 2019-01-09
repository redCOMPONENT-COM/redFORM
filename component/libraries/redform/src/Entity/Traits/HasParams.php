<?php
/**
 * @package    Redform.Library
 *
 * @copyright  Copyright (C) 2009 - 2018 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

namespace Redform\Entity\Traits;

defined('_JEXEC') or die;

/**
 * HasParams trait
 *
 * @package  Redform.Library
 * @since    __deploy_version__
 */
Trait HasParams
{
	/**
	 * Get value from a param
	 *
	 * @param   string  $name     param name
	 * @param   mixed   $default  default value
	 *
	 * @return mixed
	 */
	public function getParam($name, $default = null)
	{
		$params = new \JRegistry($this->params);

		return $params->get($name, $default);
	}
}