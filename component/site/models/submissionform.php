<?php
/**
 * @package    Redform.front
 *
 * @copyright  Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Submission model
 *
 * @package  Redform.front
 * @since    3.3.18
 */
class RedformModelSubmissionform extends RModelAdmin
{
	/**
	 * Get the associated JTable
	 *
	 * @param   string  $name    Table name
	 * @param   string  $prefix  Table prefix
	 * @param   array   $config  Configuration array
	 *
	 * @return  JTable
	 */
	public function getTable($name = null, $prefix = '', $config = array())
	{
		return parent::getTable('Submitter', 'RedformTable', $config);
	}

	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState()
	{
		parent::populateState();

		$app = JFactory::getApplication();
		$this->setState('submissionId', $app->input->getInt('id'));
		$this->setState('params', $app->getParams());
	}
}
