<?php
/**
 * @package     Joomla
 * @subpackage  redEVENT
 * @copyright   redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
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
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

/**
 * country field
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       2.5
 */
class JFormFieldRecurrency extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'recurrency';

	/**
	 * (non-PHPdoc)
	 * @see JFormFieldList::getOptions()
	 */
	protected function getOptions()
	{
		$options = array_merge(parent::getOptions(), RedformHelperLogCurrency::getCurrencyOptions());

		return $options;
	}
}
