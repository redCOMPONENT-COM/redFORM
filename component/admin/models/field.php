<?php
/**
 * @package     Redform.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 *
 * Field Model
 *
 * @package     Redform.Backend
 * @subpackage  Models
 * @since       2.5
 */
class RedformModelField extends RModelAdmin
{
	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			$this->context . '.' . $this->formName, $this->formName,
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		$fieldType = JFactory::getApplication()->getUserState('com_redform.global.field.type', '');

		if ($fieldType)
		{
			$form->loadFile('field_' . $fieldType);
		}

		return $form;
	}
	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 */
	public function save($data)
	{
		echo '<pre>'; echo print_r($data, true); echo '</pre>'; exit;
		if (!parent::save($data))
		{
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

  /**
   * Save a field
   */
  function store($data)
  {
  	$row = $this->getTable('Fields', 'RedformTable');
  	$oldrow = $this->getTable('Fields', 'RedformTable');
  	$field_id = JRequest::getInt('id', false);
  	/* Check if a field moved form */
  	if ($field_id)  {
  		$oldrow->load($field_id);
  	}

  	/* Get the posted data */
  	$post = $data;

  	/* Check field order */
  	$row->load($field_id);

  	if (!$row->bind($post)) {
  		$this->setError(JText::_('COM_REDFORM_There_was_a_problem_binding_the_field_data'), 'error');
  		return false;
  	}

  	if (empty($row->ordering)) $row->ordering = $row->getNextOrder('form_id = '.$row->form_id);

  	/* pre-save checks */
  	if (!$row->check()) {
  		$this->setError(JText::_('COM_REDFORM_There_was_a_problem_checking_the_field_data'), 'error');
  		return false;
  	}

  	/* save the changes */
  	if (!$row->store()) {
  		$this->setError(JText::_('COM_REDFORM_There_was_a_problem_storing_the_field_data'), 'error');
  		return false;
  	}

  	$this->_id = $row->id;

  	/* Add form table */
  	$this->AddFieldTable($row, $oldrow);

  	/* mailing list handling in case of email field type */
  	if ($row->fieldtype == 'email')
	  {
	  	// first, clear previous records
	  	$query = ' DELETE FROM #__rwf_mailinglists '
	  	       . ' WHERE field_id = ' . $this->_db->Quote($row->id);
	  	$this->_db->setQuery($query);
	  	$res = $this->_db->query();

	  	if ( isset($data['mailinglist']) && !empty($data['mailinglist']) )
	  	{
			  /* Load the table */
			  $mailinglistrow = $this->getTable('Mailinglists', 'RedformTable');

			  /* Fix up the mailinglist */
			  if (isset($post['listname'])) {
			  	$post['listnames'] = implode(';', $post['listname']);
			  }
			  else {
			  	$post['listnames'] = '';
			  }
			  $post['field_id'] = $row->id;
			  if (!$mailinglistrow->bind($post)) {
			  	$mainframe->enqueueMessage(JText::_('COM_REDFORM_There_was_a_problem_binding_the_mailinglist_data').' '.$row->getError(), 'error');
			  	return false;
			  }

			  /* Pass on the ID */
			  $mailinglistrow->field_id = $row->id;

			  /* save the changes */
			  if (!$mailinglistrow->store()) {
			  	$mainframe->enqueueMessage(JText::_('COM_REDFORM_There_was_a_problem_storing_the_mailinglist_data').' '.$row->getError(), 'error');
			  	return false;
			  }
	  	}
	  }

  	return $row;
  }

	/**
	 * Adds a table if it doesn't exist yet
	 *
	 * @param object field table record object with updated value
	 * @param object previously recorded field table record object corresponding to current field id
	 */
	private function AddFieldTable($row, $oldrow)
	{
		$db = & JFactory::getDBO();

		/* column name for this field */
		$field = 'field_'. $row->id;

		/* Get columns from the active form */
		$q = "SHOW COLUMNS FROM ".$db->nameQuote($db->getPrefix().'rwf_forms_'.$row->form_id)." WHERE  ".$db->nameQuote('Field')." = ".$db->Quote($field);
		$db->setQuery($q);
		$db->query();
		$result = $db->loadResult();

		/* Check if the field already exists */
		if (!$result) {
			/* Field doesn't exist, need to create it */
			$q = ' ALTER TABLE '. $db->nameQuote('#__rwf_forms_'.$row->form_id) .' ADD '. $db->nameQuote($field) .' TEXT NULL';
			$db->setQuery($q);
			if (!$db->query()) JError::raiseWarning('error', $db->getErrorMsg());
		}

		/* Check if the field moved form */
		if ($oldrow->form_id && $row->form_id <> $oldrow->form_id)
		{
			$result = array();
			/* Check if the column exists on the old table */
			$q = "SHOW COLUMNS FROM ".$db->nameQuote($db->getPrefix().'rwf_forms_'.$oldrow->form_id)." WHERE  ".$db->nameQuote('Field')." = ".$db->Quote($field);
			$db->setQuery($q);
			$db->query();
			$result = $db->loadResult();

			/* Check if the field already exists */
			if ($result) {
				/* Drop the old column */
				$q = "ALTER TABLE ".$db->nameQuote('#__rwf_forms_'.$oldrow->form_id)." DROP ".$db->nameQuote($field);
				$db->setQuery($q);
				if (!$db->query()) JError::raiseWarning('error', JText::_('COM_REDFORM_Cannot_remove_field_from_old_form').' '.$db->getErrorMsg());
			}
		}

		/* Get indexes from the active form */
		$indexresult = null;
		$q = "SHOW KEYS FROM ".$db->nameQuote($db->getPrefix().'rwf_forms_'.$row->form_id)." WHERE key_name = ".$db->Quote($field);
		$db->setQuery($q);
		$db->query();
		$indexresult = $db->loadAssocList('Key_name');

		/* Check if the field has to be unique */
		$q = "ALTER TABLE ".$db->nameQuote('#__rwf_forms_'.$row->form_id);
		if ($row->unique && !isset($indexresult[$field]))
		{
			$q .= ' ADD UNIQUE ('. $db->nameQuote($field) .' (255))';
			$db->setQuery($q);
			if (!$db->query())
			{
				JError::raiseWarning('error', JText::_('COM_REDFORM_Cannot_make_the_field_unique').' '.$db->getErrorMsg());
				/* Remove unique status */
				$q = "UPDATE ".$db->nameQuote('#__rwf_fields')."
					SET ".$db->nameQuote('unique')." = 0
					WHERE id = ".$row->id;
				$db->setQuery($q);
				$db->query();
			}
		}
		else if (isset($indexresult[$field]))
		{
			$q .= ' DROP INDEX' . $db->nameQuote($field);
			$db->setQuery($q);
			if (!$db->query()) JError::raiseWarning('error', JText::_('COM_REDFORM_Cannot_remove_the_field_unique_status').' '.$db->getErrorMsg());
		}
	}

	/**
	 * returns redmember fields as options
	 * @return aray
	 */
	public function getRedmemberFieldsOptions()
	{
		$query = ' SELECT f.field_dbname AS value, CASE WHEN (t.tab_name) THEN CONCAT(t.tab_name, " - ", f.field_name) ELSE f.field_name END AS text '
		       . ' FROM #__redmember_fields AS f '
		       . ' LEFT JOIN #__redmember_tab AS t ON t.tab_id = f.field_tabid '
		       . ' ORDER BY t.tab_name, f.field_name '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;
	}

	public function getValues()
	{
		$field = $this->getData();

		$query = ' SELECT v.* '
		       . ' FROM #__rwf_values AS v '
		       . ' WHERE v.field_id = ' . $this->_db->Quote($field->id)
		       . ' ORDER BY v.ordering '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;
	}

	/**
	 * Get the mailingslists for the e-mail field
	 */
	function getActiveMailinglists()
	{
		$res = array();
		JPluginHelper::importPlugin( 'redform_mailing' );
		$dispatcher =& JDispatcher::getInstance();
		$results = $dispatcher->trigger( 'getIntegrationName', array( &$res ) );
		return $res;
	}

	/**
	 * Get the current mailingslist settings for this field
	 */
	function getMailinglist()
	{
		/* Load the table */
		$mailinglistrow = $this->getTable('Mailinglists', 'RedformTable');
		$mailinglistrow->load($this->_id);
		return $mailinglistrow;
	}

	/**
	 * copy fields to specified form
	 *
	 * @param array $field_ids
	 * @param int $form_id
	 * @return boolean true on success
	 */
	public function copy($field_ids, $form_id)
	{
		foreach($field_ids as $field_id)
		{
			$row = $this->getTable('Fields', 'RedformTable');
			/* Check field order */
			$row->load($field_id);
			$row->id = null;
			$row->form_id = $form_id;

			$row->ordering = $row->getNextOrder('form_id = '.$row->form_id);

			/* pre-save checks */
			if (!$row->check()) {
				$this->setError(JText::_('COM_REDFORM_There_was_a_problem_checking_the_field_data'), 'error');
				return false;
			}

			/* save the changes */
			if (!$row->store()) {
				$this->setError(JText::_('COM_REDFORM_There_was_a_problem_storing_the_field_data'), 'error');
				return false;
			}

			/* Add field to form table */
			$this->AddFieldTable($row, null);

			// copy associated values
			$query = ' SELECT * '
			. ' FROM #__rwf_values '
			. ' WHERE field_id = ' . $field_id
			;
			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();

			foreach($res as $r)
			{
				/* Load the table */
				$valuerow = $this->getTable('Values', 'RedformTable');
				$valuerow->bind($r);
				$valuerow->id = null;
				$valuerow->field_id = $row->id;

				/* save the changes */
				if (!$valuerow->store()) {
					$this->setError(JText::_('COM_REDFORM_There_was_a_problem_copying_field_options').' '.$row->getError(), 'error');
					return false;
				}
			}

			/* mailing list handling in case of email field type */
			if ($row->fieldtype == 'email')
			{
				// copy mailing list settings
				$query = ' SELECT * '
				. ' FROM #__rwf_mailinglists '
				. ' WHERE field_id = ' . $field_id
				;
				$this->_db->setQuery($query);
				$res = $this->_db->loadObjectList();

				foreach($res as $r)
				{
					/* Load the table */
					$mailinglistrow = $this->getTable('Mailinglists', 'RedformTable');
					$mailinglistrow->bind($r);
					$mailinglistrow->id = null;
					$mailinglistrow->field_id = $row->id;

					/* save the changes */
					if (!$mailinglistrow->store()) {
						$this->setError(JText::_('COM_REDFORM_There_was_a_problem_storing_the_mailinglist_data').' '.$row->getError(), 'error');
						return false;
					}
				}
			}

		}

		return true;
	}
}
