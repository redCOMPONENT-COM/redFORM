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
defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * An editarea field for content creation
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @see         JEditor
 * @since       1.6
 */
class JFormFieldReeditor extends JFormFieldEditor
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'Reeditor';

	/**
	 * Method to get the field input markup for the editor area
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$rows        = (int) $this->element['rows'];
		$cols        = (int) $this->element['cols'];
		$height      = ((string) $this->element['height']) ? (string) $this->element['height'] : '250';
		$width       = ((string) $this->element['width']) ? (string) $this->element['width'] : '100%';
		$assetField  = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
		$authorField = $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
		$asset       = $this->form->getValue($assetField) ? $this->form->getValue($assetField) : (string) $this->element['asset_id'];

		// Build the buttons array.
		$buttons = (string) $this->element['buttons'];

		if ($buttons == 'true' || $buttons == 'yes' || $buttons == '1')
		{
			$buttons = true;
		}
		elseif ($buttons == 'false' || $buttons == 'no' || $buttons == '0')
		{
			$buttons = false;
		}
		else
		{
			$buttons = explode(',', $buttons);
		}

		$hide = ((string) $this->element['hide']) ? explode(',', (string) $this->element['hide']) : array();

		// Get an editor object.
		$editor = $this->getEditor();

		if ($this->value == $this->element['default']) // translate if default value
		{
			$this->value = JText::_($this->value);
		}

		return $editor
			->display(
			$this->name, htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8'), $width, $height, $cols, $rows,
			$buttons ? (is_array($buttons) ? array_merge($buttons, $hide) : $hide) : false, $this->id, $asset,
			$this->form->getValue($authorField)
		);
	}
}
