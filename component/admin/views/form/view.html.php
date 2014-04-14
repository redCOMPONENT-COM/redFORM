<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Form View
 *
 * @package     Redform.Backend
 * @subpackage  Views
 * @since       1.0
 */
class RedformViewForm extends RDFView
{
	/**
	 * @var  JForm
	 */
	protected $form;

	/**
	 * @var  object
	 */
	protected $item;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->form	= $this->get('Form');
		$this->item	= $this->get('Item');

		Jtext::script('COM_REDFORM_MISSING_OR_INVALID_EMAIL');
		Jtext::script('COM_REDFORM_CONDITIONAL_RECIPIENTS_FROMNAME_REQUIRED');
		Jtext::script('COM_REDFORM_CONDITIONAL_RECIPIENT_MISSING_PARAMETER');
		Jtext::script('COM_REDFORM_CONDITIONAL_RECIPIENTS_FIELD_REQUIRED');

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$isNew = (int) $this->item->id <= 0;
		$title = JText::_('COM_REDFORM_FORM_TITLE');
		$state = $isNew ? JText::_('JNEW') : JText::_('JEDIT');

		return $title . ' <small>' . $state . '</small>';
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;
		$canDoCore = RedformHelpersAcl::getActions();

		if ($canDoCore->get('core.edit') || $canDoCore->get('core.edit.own'))
		{
			$save = RToolbarBuilder::createSaveButton('form.apply');
			$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('form.save');

			$group->addButton($save)
				->addButton($saveAndClose);

			$saveAndNew = RToolbarBuilder::createSaveAndNewButton('form.save2new');

			$group->addButton($saveAndNew);
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('form.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('form.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
