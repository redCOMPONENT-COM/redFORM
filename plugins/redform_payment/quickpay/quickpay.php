<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redFORM
 * @copyright redFORM (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Import library dependencies
jimport('joomla.event.plugin');

// load language file for frontend
//JPlugin::loadLanguage( 'plg_redform_payment_quickpay', JPATH_ADMINISTRATOR );

class plgRedform_PaymentQuickpay extends JPlugin {

	public function plgRedform_PaymentQuickpay(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
	}

	function onGetGateway(&$gateways)
	{
		require_once ('quickpay/helpers/payment.php');
		$helper = new Paymentquickpay($this->params);
		$gateways[] = array('name' => 'quickpay', 'helper' => $helper);
		return true;
	}
}
