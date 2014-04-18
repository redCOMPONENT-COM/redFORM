<?php
/**
 * @package     Redform
 * @subpackage  front,view
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Class RdfViewSubmitters
 *
 * @package     Redform
 * @subpackage  front,views
 * @since       1.0
 */
class RedformViewSubmitters extends JViewLegacy {


	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$state = $this->get('State');

		$params = $state->get('params');
		$this->assignRef('params', $params);

		/* Get the forms */
		$forms = $this->get('FormsOptions');

		// Set the menu
		RedformHelperAdmin::setMenu();

		if (empty($forms))
		{
			echo '<p>' . JText::_('COM_REDFORM_SUBMITTERS_NO_FORM') . '</p>';

			return;
		}

		/* Create the dropdown list for form filter */
		$form_id = $state->get('form_id');
		$lists['form_id'] = JHTML::_('select.genericlist', $forms, 'form_id', '', 'value', 'text', $form_id);

		/* Get the form name, if one is selected */
		$form = $this->get('Form');
		$this->assignRef('form', $form);

		/* Get the pagination */
		$pagination = $this->get('Pagination');

		/* Get the submitters list */
		$submitters = $this->get('Items');

		/* Get the fields list */
		$fields = $this->get('Fields');

		$filter_from = JHTML::_('calendar', $state->get('filter.from'), 'filter_from', 'filter_from');
		$filter_to = JHTML::_('calendar', $state->get('filter.to'), 'filter_to', 'filter_to');

		/* Set variabels */
		$this->assignRef('pagination', $pagination);
		$this->assignRef('submitters', $submitters);
		$this->assignRef('lists', $lists);
		$this->assignRef('fields', $fields);
		$this->assignRef('integration', $app->input->get('integration', ''));
		$this->assignRef('filter_from', $filter_from);
		$this->assignRef('filter_to', $filter_to);

		JToolBarHelper::title(JText::_('COM_REDFORM_Submitters'), 'redform_submitters');

		JToolBarHelper::deleteList(JText::_('COM_REDFORM_SUBMITTERS_DELETE_WARNING'));

		if ($params->get('showintegration', false))
		{
			JToolBarHelper::custom('forcedelete', 'delete', 'delete', JText::_('COM_REDFORM_Force_delete'), true);
		}

		JToolBarHelper::editList();

		JToolBarHelper::divider();

		if (JFactory::getUser()->authorise('core.admin', 'com_redform'))
		{
			JToolBarHelper::preferences('com_redform');
		}

		/* Display the page */
		parent::display($tpl);
	}
}
