<?php
/**
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * redFORM can be downloaded from www.redcomponent.com
 * redFORM is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redFORM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redFORM; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport( 'joomla.application.component.model' );

require_once JPATH_COMPONENT_SITE.DS.'classes'.DS.'answers.php';

/**
 */
class RedformModelSubmitter extends JModel {

  /**
   * Form id
   *
   * @var int
   */
  var $_id = null;

  /**
   * Form data array
   *
   * @var array
   */
  var $_data = null;

	var $_form_id = 0;

  var $_event = null;

  var $_form = null;

  var $_fields = null;

  /**
   * Constructor
   *
   * @since 0.9
   */
  function __construct()
  {
    parent::__construct();

    $cid = JRequest::getVar( 'cid', array(0), '', 'array' );
    JArrayHelper::toInteger($cid, array(0));
    $this->setId($cid[0]);
  }

  /**
   * Method to set the identifier
   *
   * @access  public
   * @param int event identifier
   */
  function setId($id)
  {
    // Set event id and wipe data
    $this->_id      = $id;
    $this->_data  = null;
  }

  /**
   * get the data
   *
   * @return object
   */
  function &getData()
  {
    if ($this->_loadData())
    {

    }
    else  $this->_initData();

    return $this->_data;
  }

  /**
   * Method to load content event data
   *
   * @access  private
   * @return  boolean True on success
   * @since 0.9
   */
  function _loadData()
  {
    // Lets load the content if it doesn't already exist
    if (empty($this->_data))
    {
	    $form_id = JRequest::getVar('form_id', false);
	    $cid = JRequest::getVar('cid');
	    $cid = (int) $cid[0];
	    $db = JFactory::getDBO();
	    if ($form_id && $form_id > 0)
	    {
	      $query = "SELECT f.*, s.*, s.id as sid
	        FROM ".$db->nameQuote('#__rwf_forms_'.$form_id)." f
	        LEFT JOIN #__rwf_submitters s
	        ON f.id = s.answer_id
	        WHERE s.id = ".$cid;
	      $db->setQuery($query);
	      $this->_data = $this->_db->loadObject();

	      if ($this->_data->integration == 'redevent')
	      {
		      $query = ' SELECT r.uid '
		             . ' FROM #__redevent_register AS r '
		             . ' WHERE r.submit_key = '. $db->Quote($this->_data->submit_key)
		             ;
		      $db->setQuery($query);
		      $this->_data->uid = $this->_db->loadResult();
	      }
	      return (boolean) $this->_data;
	    }
    }
    return true;
  }

  function _initData()
  {
  	// TODO: should query columns from the table
    //$this->_data = & JTable::getInstance('redform', 'RedformTable');
    return $this->_data;
  }

  /**
   * Tests if the form is checked out
   *
   * @access  public
   * @param int A user id
   * @return  boolean True if checked out
   * @since 0.9
   */
  function isCheckedOut( $uid=0 )
  {
    if ($this->_loadData())
    {
      if ($uid) {
        return ($this->_data->checked_out && $this->_data->checked_out != $uid);
      } else {
        return $this->_data->checked_out;
      }
    } elseif ($this->_id < 1) {
      return false;
    } else {
      JError::raiseWarning( 0, 'Unable to Load Data');
      return false;
    }
  }

  /**
   * Method to checkout/lock the item
   *
   * @access  public
   * @param int $uid  User ID of the user checking the item out
   * @return  boolean True on success
   * @since 0.9
   */
  function checkout($uid = null)
  {
    if ($this->_id)
    {
      // Make sure we have a user id to checkout the event with
      if (is_null($uid)) {
        $user =& JFactory::getUser();
        $uid  = $user->get('id');
      }
      // Lets get to it and checkout the thing...
      $row = & JTable::getInstance('redform', 'RedformTable');
      return $row->checkout($uid, $this->_id);
    }
    return false;
  }


  /**
   * Method to checkin/unlock the item
   *
   * @access  public
   * @return  boolean True on success
   * @since 0.9
   */
  function checkin()
  {
    if ($this->_id)
    {
      $row = & JTable::getInstance('redform', 'RedformTable');
      return $row->checkin($this->_id);
    }
    return false;
  }

	/**
	 * Gets the details of a single submitter
	 */
	function getSubmitter($sid = 0)
	{
		if ($sid)
		{
			$query = ' SELECT form_id FROM #__rwf_submitters WHERE id = '. $this->_db->Quote($sid);
			$this->_db->setQuery($query, 0 ,1);
			$form_id = $this->_db->loadResult();
		}
		else
		{
			$form_id = JRequest::getInt('form_id', 0);
			$sid = JRequest::getVar('cid');
			$sid = $sid[0];
		}

		if ($form_id && $sid)
		{
			$query = "SELECT *
				FROM ".$this->_db->nameQuote('#__rwf_forms_'.$form_id)." f
				LEFT JOIN #__rwf_submitters s
				ON f.id = s.answer_id
				WHERE s.id = ".$this->_db->Quote($sid);
			$this->_db->setQuery($query);
			return $this->_db->loadObject();
		}
		else {
			return false;
		}
	}

	function store()
	{
		echo '<pre>';print_r('deprecated ?'); echo '</pre>';exit;
		$mainframe = & JFactory::getApplication();
		$db = & $this->_db;

		/* Default values */
		$answer  = '';
		$return  = false;
		$redcompetition = false;
		$redevent = false;

		$event_task = JRequest::getVar('event_task');

		$submitter_id = Jrequest::getInt('submitter_id', 0);
		if ($submitter_id) {
			$submitter = $this->getSubmitter($submitter_id);
			$submit_key = $submitter->submit_key;
		}
		else {
			$submitter = false;
			$submit_key = uniqid();
		}

		/* Get the form details */
		$form = $this->getForm(JRequest::getInt('form_id'));

		/* Load the fields */
		$fieldlist = $this->getfields($form->id);

		/* Load the posted variables */
		$post = JRequest::get('post');
		$files = JRequest::get('files');
		$posted = array_merge($post, $files);

		/* See if we have an event ID */
		if (JRequest::getInt('event_xref', 0)) {
			$redevent = true;
			$posted['xref'] = JRequest::getInt('event_xref', 0);
		}
		else if (isset($post['integration']) && $post['integration'] == 'redevent') {
			$redevent = true;
			$posted['xref'] = JRequest::getInt('xref', 0);
		}
		else if (JRequest::getInt('competition_id', 0)) {
			$redcompetition = true;
			$posted['xref'] = JRequest::getInt('competition_id', 0);
		}
		else {
			$posted['xref'] = 0;
		}

		if ($posted['xref'] && $redevent) {
			$event = $this->getEvent($posted['xref']);
		}
		else {
			$event = null;
		}


		// new answers object
		$answers = new rfanswers();
		$answers->setFormId($form->id);
		if ($event) {
			$answers->initPrice($event->course_price);
		}

		/* Create an array of values to store */
		$postvalues = array();
		// remove the _X parts, where X is the form (signup) number
		$signup = 1;
		foreach ($posted as $key => $value)
		{
			if ((strpos($key, 'field') === 0) && (strpos($key, '_'.$signup, 5) > 0)) {
				$postvalues[str_replace('_'.$signup, '', $key)] = $value;
			}
		}

		/* Some default values needed */
		$postvalues['xref'] = $post['xref'];
		$postvalues['form_id'] = $post['form_id'];
		$postvalues['submit_key'] = $submit_key;
		if (isset($post['integration'])) {
			$postvalues['integration'] = $post['integration'];
		}

		/* Get the raw form data */
		$postvalues['rawformdata'] = serialize($posted);

		/* Build up field list */
		foreach ($fieldlist as $key => $field)
		{
			if (isset($postvalues['field'.$key]))
			{
				/* Get the answers */
				try
				{
					$answers->addPostAnswer($field, $postvalues['field'.$key]);
				}
				catch (Exception $e)
				{
					$this->setError($e->getMessage());
					return false;
				}
			}
		}

		if ($submitter)
		{
			// this 'anwers' were already posted
			$answers->setAnswerId($submitter->answer_id);
		}

		// save answers
		if (!$answers->save($postvalues)) {
			return false;
		}

		// add an attendee in redevent ?
		$uid = JRequest::getInt('uid');

		$this->updateMailingList($answers);

		return true;
	}

	/**
	 * Get the course title
	 */
	public function getCourseTitle() {
		$db = JFactory::getDBO();
		$xref = JRequest::getVar('xref', JRequest::getVar('filter', false));
		$q = "SELECT CONCAT(e.title, ' ', v.venue, ' ', dates, ' - ', enddates, ' ', times, ' - ', endtimes) AS coursetitle
			FROM #__redevent_event_venue_xref x
			LEFT JOIN #__redevent_events e
			ON e.id = x.eventid
			LEFT JOIN #__redevent_venues v
			ON v.id = x.venueid
			WHERE x.id = ".$xref;
		$db->setQuery($q);
		return $db->loadResult();
	}

	/**
	 * Initialise the mailer object to start sending mails
	 */
	 private function Mailer()
	 {
		 $mainframe = JFactory::getApplication();
		jimport('joomla.mail.helper');
		/* Start the mailer object */
		$this->mailer = &JFactory::getMailer();
		$this->mailer->isHTML(true);
		$this->mailer->From = $mainframe->getCfg('mailfrom');
		$this->mailer->FromName = $mainframe->getCfg('sitename');
		$this->mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
	 }

  /**
   * returns form object
   *
   * @param int form id
   * @return object form
   */
  function &getForm($id)
  {
  	if (!$id) {
  		$id = JRequest::getInt('form_id', $this->_form_id);
  	}
    /* Get the form details */
  	$query = 'SELECT * FROM #__rwf_forms WHERE id = '. $this->_db->Quote($id);
  	$this->_db->setQuery($query, 0, 1);
  	$form = $this->_db->loadObject();

    if ($form) {
    	$this->_form = $form;
    }

  	return $this->_form;
  }

  /**
   * get the form fields name
   *
   * @param int $form_id
   */
  function getfields($form_id = 0)
  {
  	if (!$form_id) {
  		$form_id = $this->_form_id;
  	}

  	$db = & $this->_db;

  	/* Load the fields */
  	$q = "SELECT id, field, fieldtype, ordering, params
        FROM ".$db->nameQuote('#__rwf_fields')."
        WHERE form_id = ".$form_id."
        ORDER BY ordering";
  	$db->setQuery($q);
  	$fields = $db->loadObjectList('id');

  	foreach ($fields as $k =>$field)
  	{
  		$query = ' SELECT id, value, price FROM #__rwf_values WHERE field_id = '. $this->_db->Quote($field->id);
  		$this->_db->setQuery($query);
  		$fields[$k]->values = $this->_db->loadObjectList();
  	}
  	return $fields;
  }

	 /**
	 * See which attendees should be removed
	 */
	private function getConfirmAttendees()
	{
		$db = JFactory::getDBO();
		$attendees = JRequest::getVar('confirm', false);

		if ($attendees)
		{
			/* Get the ID's of setup attendees */
			$q = "SELECT id, answer_id, form_id
				FROM #__rwf_submitters
				WHERE submit_key = ".$db->Quote(JRequest::getVar('submit_key'));
			$db->setQuery($q);
			$attendees_ids = $db->loadObjectList();

			/* Check for attendees to be removed */
			foreach ($attendees_ids as $key => $attendee)
			{
				if (in_array($attendee->answer_id, $attendees)) {
					unset($attendees_ids[$key]);
				}
			}

			/* Remove the leftovers from the database */
			foreach ($attendees_ids as $key => $attendee)
			{
				$q = "DELETE FROM #__rwf_forms_".$attendee->form_id."
					WHERE id = ".$attendee->answer_id;
				$db->setQuery($q);
				$db->query();

				$q = "DELETE FROM #__rwf_submitters
					WHERE id = ".$attendee->id;
				$db->setQuery($q);
				$db->query();
			}
		}
		return true;
	}

  /**
   * Adds email from answers to mailing list
   *
   * @param rfanswers object
   */
  function updateMailingList($rfanswers)
	{
	 	// mailing lists management
	 	// get info from answers
	 	$fullname  = $rfanswers->getFullname() ? $rfanswers->getFullname() : $rfanswers->getUsername();
	 	$listnames = $rfanswers->getListNames();

	 	JPluginHelper::importPlugin( 'redform_mailing' );
		$dispatcher =& JDispatcher::getInstance();

	 	foreach ((array) $listnames as $field_id => $lists)
	 	{
	 		$subscriber = new stdclass();
	 		$subscriber->name  = empty($fullname) ? $lists['email'] : $fullname;
	 		$subscriber->email = $lists['email'];

			$integration = $this->getMailingList($field_id);

	 		foreach ((array) $lists['lists'] as $listkey => $mailinglistname)
	 		{
				$results = $dispatcher->trigger( 'subscribe', array( $integration, $subscriber, $mailinglistname ) );
	 		}
	 	}
	}


	 /**
	  * returns event associated to xref
	  *
	  * @param int $xref
	  * @return object
	  */
	 function getEvent($xref)
	 {
     $db = JFactory::getDBO();
	 	 $query = ' SELECT e.*, x.* '
	 	        . ' FROM #__redevent_event_venue_xref AS x '
	 	        . ' INNER JOIN #__redevent_events AS e ON e.id = x.eventid '
	 	        . ' WHERE x.id = '. $db->Quote((int) $xref)
	 	        ;
	 	 $db->setQuery($query);
	 	 return $db->loadObject();
	 }
}
?>
