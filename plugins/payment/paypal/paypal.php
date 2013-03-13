<?php
/**
 * @package     Joomla
 * @subpackage  redFORM
 * @copyright   redFORM (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * paypal plugin for redFORM
 *
 * @package     RedFORM
 * @subpackage  RedFORM.payment
 * @since       2.5
 */
class PlgRedform_PaymentPaypal extends JPlugin
{
	/**
	 * contructor
	 *
	 * @param   object  &$subject  subject
	 * @param   array   $config    config
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Adds this plugin to the list of gateways if active
	 *
	 * @param   array   &$gateways  the active gateways
	 * @param   string  $lang       language filter
	 *
	 * @return  boolean  true on success
	 */
	public function onGetGateway(&$gateways, $lang = null)
	{
		require_once 'paypal/helpers/payment.php';
		$helper = new PaymentPaypal($this->params);

		if ($lang && $inc_lang = $this->params->get('inc_languages'))
		{
			if (!in_array($lang, $inc_lang)) // Not included
			{
				return true;
			}
		}

		$gateways[] = array('name' => 'Paypal', 'helper' => $helper);

		return true;
	}
}
