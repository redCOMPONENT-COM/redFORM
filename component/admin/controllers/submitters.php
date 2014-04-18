<?php
/**
 * @package     Redform.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Submitters Controller
 *
 * @package     Redform.Backend
 * @subpackage  Controllers
 * @since       1.5
 */
class RedformControllerSubmitters extends RControllerAdmin
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('forcedelete',  'delete');
	}

	function delete()
	{
    $cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

    if (!is_array( $cid ) || count( $cid ) < 1) {
      JError::raiseError(500, JText::_('COM_REDFORM_Select_an_item_to_delete' ) );
    }

    $model = $this->getModel('submitters');

    if (JRequest::getVar('task') == 'forcedelete') {
    	$msg = $model->delete($cid, true);
    }
    else {
    	$msg = $model->delete($cid);
    }

    $cache = &JFactory::getCache('com_redform');
    $cache->clean();

    $form_id = JRequest::getVar('form_id', 0);

    $this->setRedirect( 'index.php?option=com_redform&view=submitters' . ($form_id ? '&form_id='.$form_id : ''), $msg );
	}
}
