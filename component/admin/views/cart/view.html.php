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
 * Cart View
 *
 * @package     Redform.Backend
 * @subpackage  Views
 * @since       3.3.8
 */
class RedformViewCart extends RdfView
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
		$this->item = $this->get('Item');
		$this->cart = RdfEntityCart::load($this->item->id);
		$this->billing = $this->get('billing');

		$billingModel = RModel::getAdminInstance('billing', ['ignore_request' => true]);
		$billingModel->setState('billing.id', $this->billing->id);
		$this->billingForm = $billingModel->getForm();

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return JText::_('COM_REDFORM_CART_TITLE');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		$cancel = RToolbarBuilder::createCloseButton('cart.close');
		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
