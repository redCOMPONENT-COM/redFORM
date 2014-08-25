<?php
/**
 * @package    Redform.plugins
 * @copyright  Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');

$redcoreLoader = JPATH_LIBRARIES . '/redcore/bootstrap.php';

if (!file_exists($redcoreLoader) || !JPluginHelper::isEnabled('system', 'redcore'))
{
	throw new Exception(JText::_('COM_REDITEM_REDCORE_INIT_FAILED'), 404);
}

// Bootstraps redCORE
RBootstrap::bootstrap();

// Register library prefix
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

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

	private $rfcore = null;

	/**
	 * onContentPrepare trigger
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
		return $this->_process($row, array());
	}

	/**
	 * Do the job
	 *
	 * @param   object  &$row    data
	 * @param   array   $params  options
	 *
	 * @return bool
	 */
	protected function _process(&$row, $params = array())
	{
		if (!file_exists(JPATH_SITE . '/components/com_redform/redform.core.php'))
		{
			JError::raiseWarning(0, JText::_('COM_REDFORM_COMPONENT_REQUIRED_FOR_REDFORM_PLUGIN'));

			return false;
		}

		$this->rfcore = new RdfCore;

		JPlugin::loadLanguage('plg_content_redform', JPATH_ADMINISTRATOR);

		$this->rwfparams = $params;

		/* Regex to find categorypage references */
		$regex = "#{redform}(.*?){/redform}#s";

		if (preg_match($regex, $row->text, $matches))
		{
			// Hook up other red components
			if (isset($row->competitionid))
			{
				JRequest::setVar('redcompetition', $row);
			}

			/* Execute the code */
			$row->text = preg_replace_callback($regex, array($this, 'FormPage'), $row->text);
		}

		return true;
	}

	/**
	 * Create the forms
	 *
	 * $matches[0] = form ID
	 * $matches[1] = Number of sign ups
	 *
	 * @param   array  $matches  matches
	 *
	 * @return string
	 */
	protected function FormPage ($matches)
	{
		/* Load the language file as Joomla doesn't do it */
		$language = JFactory::getLanguage();
		$language->load('plg_content_redform');

		if (!isset($matches[1]))
		{
			return false;
		}
		else
		{
			/* Reset matches result */
			$matches = explode(',', $matches[1]);

			/* Get the form details */
			$form = $this->getForm($matches[0]);
			$check = $this->_checkFormActive($form);

			if (!($check === true))
			{
				return $check;
			}

			/* Check if the user is allowed to access the form */
			$user = JFactory::getUser();

			if (!in_array($form->access, $user->getAuthorisedViewLevels()))
			{
				return JText::_('COM_REDFORM_LOGIN_REQUIRED');
			}

			/* Check if the number of sign ups is set, otherwise default to 1 */
			if (!isset($matches[1]))
			{
				$matches[1] = 1;
			}

			if (!isset($form->id))
			{
				return JText::_('COM_REDFORM_No_active_form_found');
			}

			return $this->getFormHtml($form, $matches[1]);
		}
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

		$query->select('f.*');
		$query->from('#__rwf_forms AS f');
		$query->where('f.id = ' . $db->Quote($form_id));
		$query->where('published = 1');

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
	protected function _checkFormActive($form)
	{
		if (strtotime($form->startdate) > time())
		{
			return JText::_('COM_REDFORM_FORM_NOT_STARTED');
		}
		elseif ($form->formexpires && strtotime($form->enddate) < time())
		{
			return JText::_('COM_REDFORM_FORM_EXPIRED');
		}

		return true;
	}

	/**
	 * Get form fields
	 *
	 * @param   int  $form_id  for id
	 *
	 * @return mixed
	 */
	protected function getFormFields($form_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('ff.id, f.field, ff.validate, f.tooltip, f.redmember_field, f.fieldtype, f.params');
		$query->from('#__rwf_fields AS f');
		$query->join('INNER', '#__rwf_form_field AS ff ON ff.field_id = f.id');
		$query->join('LEFT', '#__');
		$query->where('ff.published = 1');
		$query->where('ff.form_id = ' . $form_id);
		$query->order('ff.ordering');

		$db->setQuery($query);
		$fields = $db->loadObjectList();

		foreach ($fields as $k => $field)
		{
			$paramsdefs = JPATH_ADMINISTRATOR . '/components/com_redform/models/field_' . $field->fieldtype . '.xml';

			if (!empty($field->params) && file_exists($paramsdefs))
			{
				$fields[$k]->parameters = new JParameter($field->params, $paramsdefs);
			}
			else
			{
				$fields[$k]->parameters = new JRegistry;
			}
		}

		return $fields;
	}

	/**
	 * Get form values for field
	 *
	 * @param   int  $field_id  field id
	 *
	 * @return mixed
	 */
	protected function getFormValues($field_id)
	{
		$db = JFactory::getDBO();

		$q = "SELECT q.id, value, field_id, price
			FROM #__rwf_values q
			WHERE published = 1
			AND q.field_id = " . $field_id . "
			ORDER BY ordering";
		$db->setQuery($q);

		return $db->loadObjectList();
	}

	/**
	 * Get form html
	 *
	 * @param   object  $form   form data
	 * @param   int     $multi  number of instances
	 *
	 * @return mixed
	 */
	protected function getFormHtml($form, $multi=1)
	{
		return $this->rfcore->displayForm($form->id, null, $multi);
	}
}
