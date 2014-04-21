<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Fields View
 *
 * @package     Redform.Backend
 * @subpackage  Views
 * @since       2.5
 */
class RedformViewSubmitters extends RdfView
{
	/**
	 * @var  array
	 */
	public $items;

	/**
	 * @var  object
	 */
	public $state;

	/**
	 * @var  JPagination
	 */
	public $pagination;

	/**
	 * @var  JForm
	 */
	public $filterForm;

	/**
	 * @var array
	 */
	public $activeFilters;

	/**
	 * @var array
	 */
	public $stoolsOptions = array();

	/**
	 * @var array
	 */
	public $fields = array();

	/**
	 * @var JRegistry
	 */
	public $params;

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

		$params = JComponentHelper::getParams('com_redform');

		$model = $this->getModel('submitters');

		$this->items = $model->getItems();
		$this->state = $model->getState();
		$this->pagination = $model->getPagination();
		$this->filterForm = $model->getForm();
		$this->activeFilters = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_fields';

		/* Get the fields list */
		$this->fields = $model->getFields();

		$this->formInfo = $model->getFormInfo();

		$this->integration = $app->input->get('integration', '');
		$this->params = $params;

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return JText::_('COM_REDFORM_SUBMITTER_LIST_TITLE');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$params = JComponentHelper::getParams('com_redform');

		$canDoCore = RedformHelpersAcl::getActions();
		$user = JFactory::getUser();

		$firstGroup = new RToolbarButtonGroup;
		$secondGroup = new RToolbarButtonGroup;
		$thirdGroup = new RToolbarButtonGroup;

		if ($this->formInfo)
		{
			$csvlink = 'index.php?option=com_redform&task=submitters&format=csv'
				. ($this->integration ? '&integration=' . $this->integration : '');
			$csvexport = RToolbarBuilder::createCsvButton();
			$firstGroup->addButton($csvexport);
		}

		if ($canDoCore->get('core.edit'))
		{
			$edit = RToolbarBuilder::createEditButton('submitter.edit');
			$firstGroup->addButton($edit);
		}

		// Delete / Trash
		if ($canDoCore->get('core.delete'))
		{
			$delete = RToolbarBuilder::createDeleteButton('submitter.delete');

			if ($params->get('showintegration', false))
			{
				$delete = RToolbarBuilder::createDeleteButton('submitter.forcedelete');
			}

			$secondGroup->addButton($delete);
		}

		// Options
		if ($canDoCore->get('core.manage'))
		{
			$options = RToolbarBuilder::createOptionsButton('com_redform');
			$thirdGroup->addButton($options);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup)
			->addGroup($thirdGroup);

		return $toolbar;
	}
}
