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
 * Submitter View
 *
 * @package     Redform.Backend
 * @subpackage  Views
 * @since       1.0
 */
class RedformViewSubmitter extends RdfView
{
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
		$this->item	= $this->get('Item');

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
		$title = JText::_('COM_REDFORM_SUBMITTER_TITLE');
		$state = $isNew ? JText::_('JNEW') : JText::_('COM_REDFORM_EDIT');

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
			$save = RToolbarBuilder::createSaveButton('submitter.apply');
			$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('submitter.save');

			$group->addButton($save)
				->addButton($saveAndClose);
		}

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('submitter.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('submitter.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
