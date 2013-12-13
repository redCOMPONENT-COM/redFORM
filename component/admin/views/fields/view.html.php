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
 * Categories View
 *
 * @package     Redform.Backend
 * @subpackage  Views
 * @since       1.0
 */
class RedformViewFields extends RDFView
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
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$model = $this->getModel('fields');

		$this->items = $model->getItems();
		$this->state = $model->getState();
		$this->pagination = $model->getPagination();
		$this->filterForm = $model->getForm();
		$this->activeFilters = $model->getActiveFilters();
		$this->stoolsOptions['searchField'] = 'search_fields';

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return JText::_('COM_REDFORM_FIELD_LIST_TITLE');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$canDoCore = RedformHelpersAcl::getActions();
		$user = JFactory::getUser();

		$firstGroup = new RToolbarButtonGroup;
		$secondGroup = new RToolbarButtonGroup;

		// Add / edit
		if ($canDoCore->get('core.create'))
		{
			$new = RToolbarBuilder::createNewButton('field.add');
			$firstGroup->addButton($new);
		}

		if ($canDoCore->get('core.edit'))
		{
			$edit = RToolbarBuilder::createEditButton('field.edit');
			$firstGroup->addButton($edit);
		}

		// Publish / Unpublish
		if ($canDoCore->get('core.edit.state'))
		{
			$publish = RToolbarBuilder::createPublishButton('fields.publish');
			$unpublish = RToolbarBuilder::createUnpublishButton('fields.unpublish');

			$firstGroup->addButton($publish)
				->addButton($unpublish);
		}

		// Delete / Trash
		if ($canDoCore->get('core.delete'))
		{
			$delete = RToolbarBuilder::createDeleteButton('fields.delete');
			$secondGroup->addButton($delete);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup);

		return $toolbar;
	}
}
