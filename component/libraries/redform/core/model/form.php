<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Core.Model
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Class RdfCoreModelSubmission
 *
 * @package     Redform.Libraries
 * @subpackage  Core.Model
 * @since       3.0
 */
class RdfCoreModelForm extends RModel
{
	/**
	 * item id
	 * @var int
	 */
	protected $id;

	/**
	 * Caching
	 * @var object
	 */
	protected $form;

	/**
	 * Constructor
	 *
	 * @param   int  $form_id  form id
	 */
	public function __construct($form_id = null)
	{
		parent::__construct();

		if ($form_id)
		{
			$this->setId($form_id);
		}
	}

	/**
	 * Method to set the form identifier
	 *
	 * @param   int  $id  event identifier
	 *
	 * @return void
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Get form entity
	 *
	 * @return RdfEntityForm
	 */
	public function getForm()
	{
		if (!$this->form)
		{
			$this->form = RdfEntityForm::load($this->id);
		}

		return $this->form;
	}

	/**
	 * get the form fields
	 *
	 * @return array RdfRfield
	 */
	public function getFormFields()
	{
		$form = RdfEntityForm::getInstance($this->id);

		return $form->getFormFields();
	}

	/**
	 * return form status
	 *
	 * @return boolean
	 */
	public function getFormStatus()
	{
		$form_id = $this->id;

		$db   = JFactory::getDBO();
		$user = JFactory::getUser();

		$query = ' SELECT f.* '
			. ' FROM #__rwf_forms AS f '
			. ' WHERE id = ' . (int) $form_id;
		$db->setQuery($query);
		$form = $db->loadObject();

		if (!$form->published)
		{
			$this->setError(JText::_('COM_REDFORM_STATUS_NOT_PUBLISHED'));

			return false;
		}

		if (strtotime($form->startdate) > time())
		{
			$this->setError(JText::_('COM_REDFORM_STATUS_NOT_STARTED'));

			return false;
		}

		if ($form->formexpires && strtotime($form->enddate) < time())
		{
			$this->setError(JText::_('COM_REDFORM_STATUS_EXPIRED'));

			return false;
		}

		if ($form->access > 1 && !$user->get('id'))
		{
			$this->setError(JText::_('COM_REDFORM_STATUS_REGISTERED_ONLY'));

			return false;
		}

		if ($form->access > max($user->getAuthorisedViewLevels()))
		{
			$this->setError(JText::_('COM_REDFORM_STATUS_SPECIAL_ONLY'));

			return false;
		}

		return true;
	}

	/**
	 * Get submission associated to data saved to session
	 *
	 * @param   object  $fromSession  session object
	 *
	 * @return RdfCoreFormSubmission
	 */
	public function getSubmissionFromSession($fromSession)
	{
		$res = new RdfCoreFormSubmission;
		$fields = $this->getFormFields();

		foreach ($fromSession as $single)
		{
			$answers = new RdfAnswers;

			foreach ($fields as $field)
			{
				$clone = clone $field;

				if (isset($single->{'field_' . $field->field_id}))
				{
					$clone->setValue($single->{'field_' . $field->field_id});
				}

				$answers->addField($clone);
			}

			$res->addSubSubmission($answers);
		}

		return $res;
	}
}
