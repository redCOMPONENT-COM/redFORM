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
 * Field View
 *
 * @package     Redform.Backend
 * @subpackage  Views
 * @since       1.0
 */
class RedformViewField extends RDFView
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
	 * @var  boolean
	 */
	protected $displaySidebar = false;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The template name
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();

		$fieldType = $app->getUserState('com_reditem.global.field.type', '');
		$editData = $app->getUserState('com_reditem.edit.field.data', array());

		$this->form	= $this->get('Form');
		$this->item	= $this->get('Item');

		if ($fieldType)
		{
			$this->form->loadFile('field_' . $fieldType, false);
			$this->item->type = $fieldType;

			if (isset($editData['params']) && is_array($editData['params']))
			{
				foreach ($editData['params'] as $key => $value)
				{
					$this->form->setValue($key, 'params', $value);
				}
			}
			elseif (isset($this->item->params))
			{
				$params = new JRegistry($this->item->params);
				$params = $params->toArray();

				foreach ($params as $key => $value)
				{
					$this->form->setValue($key, 'params', $value);
				}
			}
		}

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
		$title = JText::_('COM_REDFORM_FIELD_TITLE');
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
			$save = RToolbarBuilder::createSaveButton('field.apply');
			$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('field.save');

			$group->addButton($save)
				->addButton($saveAndClose);

			$saveAndNew = RToolbarBuilder::createSaveAndNewButton('field.save2new');

			$group->addButton($saveAndNew);
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('field.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('field.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
