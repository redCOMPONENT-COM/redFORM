<?php
/**
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license   GNU/GPL, see LICENSE.php
 *            redFORM can be downloaded from www.redcomponent.com
 *            redFORM is free software; you can redistribute it and/or
 *            modify it under the terms of the GNU General Public License 2
 *            as published by the Free Software Foundation.
 *            redFORM is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *            GNU General Public License for more details.
 *            You should have received a copy of the GNU General Public License
 *            along with redFORM; if not, write to the Free Software
 *            Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

class rfanswers
{
	public $_test = 0;

	public $_answer_id = 0;

	public $_fields = null;

	public $_values = null;

	public $_types = null;

	public $_form_id = 0;

	public $_submitter_email = array();

	public $_fullname = null;

	public $_username = null;

	public $_listnames = array();

	public $_recipients = array();

	private $_price = 0;

	private $_answers = null;

	private $_isnew = true;

	private $_sid = 0;

	private $_db;

	public function __construct()
	{
		$this->_db = & JFactory::getDBO();
	}

	public function setFormId($id)
	{
		$this->_form_id = (int) $id;
	}


	public function setAnswerId($id)
	{
		$this->_answer_id = (int) $id;
	}

	public function getAnswerId($id)
	{
		return $this->_answer_id;
	}

	public function getListNames()
	{
		return $this->_listnames;
	}

	public function getSubmitterEmails()
	{
		return $this->_submitter_email;
	}

	public function getRecipients()
	{
		return $this->_recipients;
	}

	public function getFullname()
	{
		return $this->_fullname;
	}

	public function getUsername()
	{
		return $this->_username;
	}

	public function initPrice($initial)
	{
		$this->_price = $initial;
	}

	public function getPrice()
	{
		return $this->_price;
	}

	public function isNew()
	{
		return $this->_isnew;
	}

	public function setNew($val)
	{
		$this->_isnew = $val ? true : false;
	}

	public function addPostAnswer($field, $postedvalue)
	{
		$db = JFactory::getDBO();

		$answer = '';

		switch ($field->fieldtype)
		{
			case 'textarea':
				$answer = is_array($postedvalue) ? $postedvalue['textarea'] : $postedvalue;
				break;

			case 'date':
				// Get date
				$answer = is_array($postedvalue) ? $postedvalue['date'] : $postedvalue;
				if ($answer && !strtotime($answer))
				{
					throw new Exception(JText::_('COM_REDFORM_INVALID_DATE_FORMAT'));
				}
				break;

			case 'wysiwyg':
				$answer = is_array($postedvalue) ? $postedvalue['wysiwyg'] : $postedvalue;
				break;

			case 'fullname':
				$answer = is_array($postedvalue) ? $postedvalue['fullname'][0] : $postedvalue;
				$this->_fullname = $answer;
				break;

			case 'username':
				$answer = is_array($postedvalue) ? $postedvalue['username'][0] : $postedvalue;
				$this->_username = $answer;
				break;

			case 'email':
				// TODO: store submitter email and listnames
				if (is_array($postedvalue))
				{
					$answer = $postedvalue['email'][0];

					if (array_key_exists('listnames', $postedvalue['email']))
					{
						$this->_listnames[$field->id] = array('email' => $answer, 'lists' => $postedvalue['email']['listnames']);
					}
				}
				else
				{
					$answer = $postedvalue;
				}

				if ($field->parameters->get('notify', 1))
				{
					$this->_submitter_email[] = $answer;
				}

				break;

			case 'textfield':
				$answer = is_array($postedvalue) ? $postedvalue['text'][0] : $postedvalue;
				break;

			case 'hidden':
				$answer = is_array($postedvalue) ? $postedvalue['hidden'][0] : $postedvalue;
				break;

			case 'select':
				$answer = is_array($postedvalue) ? $postedvalue['select'][0] : $postedvalue;

				foreach ($field->values as $v)
				{
					if ($v->value == $answer)
					{
						$this->_price += $v->price;
					}
				}
				break;

			case 'checkbox':
				$options = is_array($postedvalue) ? $postedvalue['checkbox'] : $postedvalue;
				$submittervalues = array();

				foreach ($options as $key => $submitteranswer)
				{
					$submittervalues[] = $submitteranswer;

					foreach ($field->values as $v)
					{
						if ($v->value == $submitteranswer)
						{
							$this->_price += $v->price;
						}
					}
				}

				$answer = implode("~~~", $submittervalues);
				break;

			case 'multiselect':
				$options = is_array($postedvalue) ? $postedvalue['multiselect'] : $postedvalue;
				$submittervalues = array();

				foreach ($options as $key => $submitteranswer)
				{
					$submittervalues[] = $submitteranswer;

					foreach ($field->values as $v)
					{
						if ($v->value == $submitteranswer)
						{
							$this->_price += $v->price;
						}
					}
				}

				$answer = implode("~~~", $submittervalues);
				break;

			case 'recipients':
				$options = is_array($postedvalue) ? $postedvalue['recipients'] : $postedvalue;
				$submittervalues = array();

				foreach ($options as $key => $submitteranswer)
				{
					$submittervalues[] = $submitteranswer;
					$this->_recipients[] = $submitteranswer;
				}

				$answer = implode("~~~", $submittervalues);
				break;

			case 'fileupload':
				if (in_array('fileupload', array_keys($postedvalue['name'])))
				{
					$answer = $this->_fileupload($postedvalue);
				}
				break;

			case 'radio':
				$value = is_array($postedvalue) ? $postedvalue['radio'][0] : $postedvalue;

				/* Get the real value from the database */
				$q = "SELECT value, price
                FROM #__rwf_values
                WHERE id = " . $value;
				$db->setQuery($q);
				$res = $db->loadObject();

				$answer = $res->value;
				$this->_price += $res->price;
				break;

			case 'price':
				if (count($field->values))
				{
					$answer = $field->values[0]->value;
				}
				else
				{
					$answer = is_array($postedvalue) ? $postedvalue['price'][0] : $postedvalue;
				}
				$this->_price += $answer;
				break;
		}
		$this->_fields[] = $field;
		$this->_values[] = $answer;
		$this->_types[]  = $field->fieldtype;

		return $answer;
	}

	/**
	 * manages file post field
	 *
	 * @param array $field
	 *
	 * @return string answer
	 */
	protected function _fileupload($field)
	{
		/* Check if the folder exists */
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$params = JComponentHelper::getParams('com_redform');

		$db = & JFactory::getDBO();
		$answer = '';

		/* Get the file path for file upload */
		$query = ' SELECT f.formname '
			. ' FROM #__rwf_forms AS f '
			. ' WHERE f.id = ' . $db->Quote($this->_form_id);
		$db->setQuery($query);
		$formname = $db->loadResult();

		$filepath = JPATH_SITE . DS . $params->get('upload_path', 'images/redform');
		$folder = JFile::makeSafe(str_replace(' ', '', $formname));

		$fullpath = $filepath . DS . $folder;
		if (!JFolder::exists($fullpath))
		{
			if (!JFolder::create($fullpath))
			{
				JError::raiseWarning(0, JText::_('COM_REDFORM_CANNOT_CREATE_FOLDER') . ': ' . $fullpath);
				$status = false;
				return false;
			}
		}
		clearstatcache();

		$src_file = $field['tmp_name']['fileupload'][0];
		// make sure we have a unique name for file
		$dest_filename = uniqid() . '_' . basename($field['name']['fileupload'][0]);

		if (JFolder::exists($fullpath))
		{
			/* Start processing uploaded file */
			if (is_uploaded_file($src_file))
			{
				if (JFolder::exists($fullpath) && is_writable($fullpath))
				{
					if (move_uploaded_file($src_file, $fullpath . DS . $dest_filename))
					{
						$answer = $fullpath . DS . $dest_filename;
					}
					else
					{
						JError::raiseWarning(0, JText::_('COM_REDFORM_CANNOT_UPLOAD_FILE'));
						return false;
					}
				}
				else
				{
					JError::raiseWarning(0, JText::_('COM_REDFORM_FOLDER_DOES_NOT_EXIST'));
					return false;
				}
			}
		}
		else
		{
			JError::raiseWarning(0, JText::_('COM_REDFORM_FOLDER_DOES_NOT_EXIST'));
			return false;
		}
		return $answer;
	}

	/**
	 * save the answer
	 *
	 * @param array $params: submit_key, xref, etc...
	 *
	 * @return true on success
	 */
	public function save($params = array())
	{
		$mainframe = Jfactory::getApplication();
		$db = & JFactory::getDBO();

		if (empty($this->_form_id))
		{
			JError::raiseError(0, JText::_('COM_REDFORM_ERROR_NO_FORM_ID'));
		}

		if (!count($this->_fields))
		{
			return true;
		}

		$values = array();
		$fields = array();
		foreach ($this->_fields as $v)
		{
			$fields[] = $db->nameQuote('field_' . $v->id);
		}
		foreach ($this->_values as $v)
		{
			$values[] = $db->Quote($v);
		}

		// we need to make sure all table fields are updated: typically, if a field is of type checkbox, if not checked it won't be posted, hence we have to set the value to empty
		$q = " SHOW COLUMNS FROM " . $db->nameQuote('#__rwf_forms_' . $this->_form_id);
		$db->setQuery($q);
		$columns = $db->loadResultArray();
		foreach ($columns as $col)
		{
			if (strstr($col, 'field_') && !in_array($db->nameQuote($col), $fields))
			{
				$fields[] = $db->nameQuote($col);
				$values[] = $db->Quote('');
			}
		}

		if ($this->_answer_id) // answers were already recorder, update them
		{
			$q = "UPDATE " . $db->nameQuote('#__rwf_forms_' . $this->_form_id);
			$set = array();
			foreach ($fields as $ukey => $col)
			{
				$set[] = $col . " = " . $values[$ukey];
			}
			$q .= ' SET ' . implode(', ', $set);
			$q .= " WHERE ID = " . $this->_answer_id;
			$db->setQuery($q);

			if (!$db->query())
			{
				JError::raiseError(0, JText::_('COM_REDFORM_UPDATE_ANSWERS_FAILED'));
				RedformHelperLog::simpleLog(JText::_('COM_REDFORM_Cannot_update_answers') . ' ' . $db->getErrorMsg());
			}
			$this->setPrice();
		}
		else
		{
			/* Construct the query */
			$q = "INSERT INTO " . $db->nameQuote('#__rwf_forms_' . $this->_form_id) . "
            (" . implode(', ', $fields) . ")
            VALUES (" . implode(', ', $values) . ")";
			$db->setQuery($q);

			if (!$db->query())
			{
				/* We cannot save the answers, do not continue */
				if (stristr($db->getErrorMsg(), 'duplicate entry'))
				{
					JRequest::setVar('ALREADY_ENTERED', true);
					$mainframe->enqueueMessage(JText::_('COM_REDFORM_ALREADY_ENTERED'), 'error');
				}
				else $mainframe->enqueueMessage(JText::_('COM_REDFORM_Cannot_save_form_answers') . ' ' . $db->getErrorMsg(), 'error');
				/* We cannot save the answers, do not continue */
				RedformHelperLog::simpleLog(JText::_('COM_REDFORM_Cannot_save_form_answers') . ' ' . $db->getErrorMsg());
				return false;
			}
			$this->_answer_id = $db->insertid();
			$res = $this->updateSubmitter($params);
			$this->setPrice();
			return $res;
		}
		return true;
	}

	/**
	 * new save function for new lib
	 *
	 * @param $params
	 *
	 * @return int submitter_id
	 */
	function savedata($params = array())
	{
		$mainframe = Jfactory::getApplication();
		$db = & JFactory::getDBO();


		if (empty($this->_form_id))
		{
			throw new Exception(JText::_('COM_REDFORM_ERROR_NO_FORM_ID'), 404);
		}

		if (!count($this->_fields))
		{
			throw new Exception('No field to save !');
		}

		if (isset($params['sid']))
		{
			$this->_isnew = intval($params['sid']) == 0;
			$sid = intval($params['sid']);
		}
		else
		{
			$this->_isnew = true;
			$sid = 0;
		}

		$values = array();
		$fields = array();
		foreach ($this->_fields as $v)
		{
			$fields[] = $db->nameQuote('field_' . $v->id);
		}
		foreach ($this->_values as $v)
		{
			$values[] = $db->Quote($v);
		}

		// we need to make sure all table fields are updated: typically, if a field is of type checkbox, if not checked it won't be posted, hence we have to set the value to empty
		$q = " SHOW COLUMNS FROM " . $db->nameQuote('#__rwf_forms_' . $this->_form_id);
		$db->setQuery($q);
		$columns = $db->loadResultArray();
		foreach ($columns as $col)
		{
			if (strstr($col, 'field_') && !in_array($db->nameQuote($col), $fields))
			{
				$fields[] = $db->nameQuote($col);
				$values[] = $db->Quote('');
			}
		}

		if ($sid) // answers were already recorded, update them
		{
			$submitter = $this->getSubmitter($sid);

			$q = "UPDATE " . $db->nameQuote('#__rwf_forms_' . $this->_form_id);
			$set = array();
			foreach ($fields as $ukey => $col)
			{
				$set[] = $col . " = " . $values[$ukey];
			}
			$q .= ' SET ' . implode(', ', $set);
			$q .= " WHERE ID = " . $submitter->answer_id;
			$db->setQuery($q);

			if (!$db->query())
			{
				JError::raiseError(0, JText::_('COM_REDFORM_UPDATE_ANSWERS_FAILED'));
				RedformHelperLog::simpleLog(JText::_('COM_REDFORM_Cannot_update_answers') . ' ' . $db->getErrorMsg());
			}
		}
		else
		{
			/* Construct the query */
			$q = "INSERT INTO " . $db->nameQuote('#__rwf_forms_' . $this->_form_id) . "
            (" . implode(', ', $fields) . ")
            VALUES (" . implode(', ', $values) . ")";
			$db->setQuery($q);

			if (!$db->query())
			{
				/* We cannot save the answers, do not continue */
				if (stristr($db->getErrorMsg(), 'duplicate entry'))
				{
					JRequest::setVar('ALREADY_ENTERED', true);
					$mainframe->enqueueMessage(JText::_('COM_REDFORM_ALREADY_ENTERED'), 'error');
				}
				else
				{
					$mainframe->enqueueMessage(JText::_('COM_REDFORM_Cannot_save_form_answers') . ' ' . $db->getErrorMsg(), 'error');
				}
				/* We cannot save the answers, do not continue */
				RedformHelperLog::simpleLog(JText::_('COM_REDFORM_Cannot_save_form_answers') . ' ' . $db->getErrorMsg());
				return false;
			}
			$this->_answer_id = $db->insertid();
			$sid = $this->updateSubmitter($params);
		}
		$this->_sid = $sid;
		$this->setPrice();
		return $sid;
	}

	function updateSubmitter($params = array())
	{
		$db = & JFactory::getDBO();
		$mainframe = & JFactory::getApplication();

		// prepare data for submitter record
		$submitterdata['answer_id'] = $this->_answer_id;
		if (empty($params['submit_key']))
		{
			JError::raiseError(0, JText::_('COM_REDFORM_ERROR_SUBMIT_KEY_MISSING'));
		}
		$submitterdata = array_merge($submitterdata, $params);
		$submitterdata['form_id'] = $this->_form_id;

		/* Store the submitter details */
		$row = & JTable::getInstance('Submitters', 'RedformTable');

		if (isset($params['xref']) && (int) $params['xref'])
		{
			$submitterdata['xref'] = $params['xref'];
			/* Add some settings */
			/* Get activate setting for event */
			$q = "SELECT activate
              FROM #__redevent_events AS e
              LEFT JOIN #__redevent_event_venue_xref AS x
              ON e.id = x.eventid
              WHERE x.id = " . $params['xref'];
			$db->setQuery($q);
			$activate = $db->loadResult();

			/* Check if the user needs to confirm */
			if ($activate)
			{
				$submitterdata['confirmed'] = 0;
			}
			else
			{
				/* Automatically confirm user */
				$submitterdata['confirmed'] = 1;
				$submitterdata['confirmdate'] = gmdate('Y-m-d H:i:s');
			}
		}
		else
		{
			/* Automatically confirm user */
			$submitterdata['confirmed'] = 1;
			$submitterdata['confirmdate'] = gmdate('Y-m-d H:i:s');
		}

		if (!$row->bind($submitterdata))
		{
			$mainframe->enqueueMessage(JText::_('COM_REDFORM_There_was_a_problem_binding_the_submitter_data'), 'error');
			RedformHelperLog::simpleLog(JText::_('COM_REDFORM_There_was_a_problem_binding_the_submitter_data') . ': ' . $row->getError());
			return false;
		}
		/* Set the date */
		$row->submission_date = date('Y-m-d H:i:s', time());
		$row->submitternewsletter = ($this->_listnames && count($this->_listnames)) ? 1 : 0;

		/* pre-save checks */
		if (!$row->check())
		{
			$mainframe->enqueueMessage(JText::_('COM_REDFORM_There_was_a_problem_checking_the_submitter_data'), 'error');
			RedformHelperLog::simpleLog(JText::_('COM_REDFORM_There_was_a_problem_checking_the_submitter_data') . ': ' . $row->getError());
			return false;
		}

		/* save the changes */
		if (!$row->store())
		{
			if (stristr($db->getErrorMsg(), 'Duplicate entry'))
			{
				$mainframe->enqueueMessage(JText::_('COM_REDFORM_You_have_already_entered_this_form'), 'error');
				RedformHelperLog::simpleLog(JText::_('COM_REDFORM_You_have_already_entered_this_form'));
			}
			else
			{
				$mainframe->enqueueMessage(JText::_('COM_REDFORM_There_was_a_problem_storing_the_submitter_data'), 'error');
				RedformHelperLog::simpleLog(JText::_('COM_REDFORM_There_was_a_problem_storing_the_submitter_data') . ': ' . $row->getError());
			}
			return false;
		}
		return $row->id;
	}

	// set price corresponding to answers in submitters table
	function setPrice()
	{
		if (!$this->_sid)
		{
			return false;
		}
		$params = & JComponentHelper::getParams('com_redform');
		if ($params->get('allow_negative_total', 1))
		{
			$price = $this->_price;
		}
		else
		{
			$price = max(array(0, $this->_price));
		}
		$db = & JFactory::getDBO();
		$query = ' UPDATE #__rwf_submitters SET price = ' . $db->Quote($price)
			. ' WHERE id = ' . $db->Quote($this->_sid);
		$db->setQuery($query);
		$res = $db->query();
		if (!$res)
		{
			RedformHelperLog::simpleLog($db->getErrorMsg());
			return false;
		}

		return $res;
	}

	function getAnswers()
	{
		$answers = array();
		foreach ($this->_fields as $k => $field)
		{
			$answers[] = array('field' => $field->field, 'field_id' => $field->id, 'value' => $this->_values[$k], 'type' => $this->_types[$k]);
		}
		return $answers;
	}

	/**
	 * return answer for specified field
	 *
	 * @param int $field_id
	 *
	 * @return string
	 */
	public function getFieldAnswer($field_id)
	{
		$answers = $this->getAnswers();
		if (!$answers)
		{
			return false;
		}
		foreach ($answers as $a)
		{
			if ($field_id == $a['field_id'])
			{
				return $a;
			}
		}
		return false;
	}

	/**
	 * loads answers of specified submitter
	 *
	 * @param int $submitter_id
	 *
	 * @return true on success
	 */
	function getSubmitterAnswers($submitter_id)
	{
		$db = & JFactory::getDBO();
		$sid = (int) $submitter_id;

		// get submission details first, to get the fieds
		$query = ' SELECT s.* FROM #__rwf_submitters AS s WHERE s.id = ' . $db->quote($sid);
		$db->setQuery($query);
		$submitter = $db->loadObject();

		if (!$submitter)
		{
			Jerror::raisewarning(0, JText::_('COM_REDFORM_unknown_submitter'));
			return false;
		}

		// get fields
		$query = ' SELECT f.* FROM #__rwf_fields AS f '
			. ' WHERE f.form_id = ' . $db->quote($submitter->form_id)
			. ' AND f.published = 1 '
			. ' ORDER BY f.ordering ';
		$db->setQuery($query);
		$fields = $db->loadObjectList('id');

		$fnames = array();
		foreach ($fields as $f)
		{
			$fnames[] = $db->namequote('f.field_' . $f->id);
		}

		// get values
		$query = ' SELECT ' . implode(',', $fnames)
			. ' FROM #__rwf_forms_' . $submitter->form_id . ' AS f '
			. ' WHERE f.id = ' . $db->quote($submitter->answer_id);
		$db->setQuery($query);
		$answers = $db->loadObject();

		if (!$answers)
		{
			Jerror::raisewarning(0, JText::_('COM_REDFORM_error_getting_submitter_answers'));
			return false;
		}

		foreach ($fields as $id => $f)
		{
			$property = 'field_' . $f->id;
			if (isset($answers->$property))
			{
				$fields[$id]->answer = $answers->$property;
			}
		}
		return $fields;
	}

	function getSubmitter($id)
	{
		$query = ' SELECT s.* '
			. ' FROM #__rwf_submitters AS s '
			. ' WHERE s.id = ' . $this->_db->Quote($id);
		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();
		return $res;
	}

	function getSid()
	{
		return $this->_sid;
	}

	/**
	 * Returns simple object field => value to save to session
	 *
	 * @return stdclass
	 */
	public function toSession()
	{
		$answers = new stdclass();

		foreach ($this->_fields as $k => $field)
		{
			$tablefield = 'field_' . $field->id;
			$answers->$tablefield = $this->_values[$k];
		}

		return $answers;
	}
}
