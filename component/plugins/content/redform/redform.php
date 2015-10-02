<?php
/**
 * @package    Redform.plugins
 * @copyright  Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');

$redformLoader = JPATH_LIBRARIES . '/redform/bootstrap.php';

if (!file_exists($redformLoader))
{
	throw new Exception(JText::_('COM_REDFORM_LIB_INIT_FAILED'), 404);
}

include_once $redformLoader;

// Bootstraps redFORM
RdfBootstrap::bootstrap();

/**
 * redFORM content plugin
 *
 * @package  Redform.plugins
 * @since    2.5
 */
class PlgContentRedform extends JPlugin
{
	/**
	 * specific redform plugin parameters
	 *
	 * @var JParameter object
	 */
	private $rwfparams = null;

	/**
	 * @var RdfCore
	 */
	private $rfcore = null;

	/**
	 * onContentPrepare trigger.
	 * looks for tags in the form {redform}1{/redform}, or {redform}2,3{/redform} (3 times form 2)
	 *
	 * @param   string  $context  The context of the content being passed to the plugin.
	 * @param   object  &$row     The article object.  Note $article->text is also available
	 * @param   object  &$params  The article params
	 * @param   int     $page     The 'page' number
	 *
	 * @return boolean true on success
	 */
	public function onContentPrepare($context,&$row, &$params, $page = 0)
	{
		$this->rfcore = new RdfCore;

		JPlugin::loadLanguage('plg_content_redform', JPATH_ADMINISTRATOR);

		$this->rwfparams = $params;

		$regex = "#{redform}(.*?){/redform}#s";

		if (preg_match_all($regex, $row->text, $matches))
		{
			foreach ($matches[1] as $k => $match)
			{
				$row->text = str_replace($matches[0][$k], $this->buildForm($match), $row->text);
			}
		}

		return true;
	}

	/**
	 * Create the forms
	 *
	 * $match = form ID(, multiple count)
	 *
	 * @param   string  $match  match
	 *
	 * @return string
	 */
	protected function buildForm($match)
	{
		/* Load the language file as Joomla doesn't do it */
		$language = JFactory::getLanguage();
		$language->load('plg_content_redform');

		$parts = explode(',', $match);

		/* Get the form details */
		$form = $this->getForm($parts[0]);
		$check = $this->checkFormIsActive($form);

		if (!($check === true))
		{
			return $check;
		}

		/* Check if the user is allowed to access the form */
		$user = JFactory::getUser();

		if (!in_array($form->access, $user->getAuthorisedViewLevels()))
		{
			return JText::_('PLG_CONTENT_REDFORM_FORM_DISPLAY_NOT_ALLOWED');
		}

		/* Check if the number of sign ups is set, otherwise default to 1 */
		$multiple = isset($parts[1]) ? $parts[1] : 1;

		if (!isset($form->id))
		{
			return JText::_('COM_REDFORM_No_active_form_found');
		}

		$options = array();

		if (isset($this->rwfparams['module_id']))
		{
			$options['module_id'] = $this->rwfparams['module_id'];
		}

		return $this->rfcore->displayForm($form->id, null, $multiple, $options);
	}

	/**
	 * returns form object
	 *
	 * @param   int  $form_id  form id
	 *
	 * @return object
	 */
	protected function getForm($form_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id, access, published')
			->select('startdate, formexpires, enddate')
			->from('#__rwf_forms')
			->where('id = ' . $db->Quote($form_id));

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * checks if the form is active
	 *
	 * @param   object  $form  form object
	 *
	 * @return true if active, error message if not
	 */
	protected function checkFormIsActive($form)
	{
		if (!$form->published)
		{
			return JText::_('PLG_CONTENT_REDFORMFORM_NOT_PUBLISHED');
		}
		elseif (strtotime($form->startdate) > time())
		{
			return JText::_('PLG_CONTENT_REDFORM_FORM_NOT_STARTED');
		}
		elseif ($form->formexpires && strtotime($form->enddate) < time())
		{
			return JText::_('PLG_CONTENT_REDFORM_FORM_EXPIRED');
		}

		return true;
	}
}
