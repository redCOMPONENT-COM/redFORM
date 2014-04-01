<?php
/**
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license   GNU/GPL, see LICENSE.php
 * redFORM can be downloaded from www.redcomponent.com
 * redFORM is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 * redFORM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with redFORM; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/* No direct access */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * redFORM View
 */
class RedformViewSubmitters extends JView
{
	/**
	 * redFORM view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		/* Get the submitters list */
		$model = $this->getModel();
		$model->setContext('submitters.export');
		$model->getState('list.limit');
		$model->setState('list.limit', 0);

		$form = $this->get('Form');
		$fields = $this->get('Fields');
		$submitters = $model->getItems();

		$this->assignRef('form', $form);
		$this->assignRef('fields', $fields);
		$this->assignRef('submitters', $submitters);

		parent::display($tpl);
	}

	function writecsvrow($fields, $delimiter = ',', $enclosure = '"')
	{
		$delimiter_esc = preg_quote($delimiter, '/');
		$enclosure_esc = preg_quote($enclosure, '/');

		$output = array();
		foreach ($fields as $field)
		{
			$output[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field) ? (
				$enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure
			) : $field;
		}

		return join($delimiter, $output) . "\n";
	}
}
