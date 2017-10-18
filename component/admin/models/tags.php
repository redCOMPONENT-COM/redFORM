<?php
/**
 * @package    Redform.admin
 * @copyright  redform (C) 2017 redCOMPONENT.com
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

use Redform\Tag\Info;

/**
 * redform Component tags Model
 *
 * @package  Redform.admin
 * @since    3.3.19
 */
class RedformModelTags extends RModel
{
	/**
	 * Get items
	 *
	 * @return array
	 * @since    3.3.19
	 */
	public function getItems()
	{
		$tags = array_merge(
			$this->getStandardTags(),
			$this->getFieldsTags()
		);

		JPluginHelper::importPlugin('redform');
		$dispatcher = RFactory::getDispatcher();
		$dispatcher->trigger('onRedformGetAvailableTags', array(&$tags));

		return $this->tagsBySection($tags);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return  void
	 * @since    3.3.19
	 */
	protected function populateState()
	{
		parent::populateState();

		$this->setState('filter.field', JFactory::getApplication()->input->getString('field', ''));
		$this->setState('filter.form_id', JFactory::getApplication()->input->getInt('form_id'));
	}

	/**
	 * Core tags
	 *
	 * @return Info[]
	 * @since    3.3.19
	 */
	private function getStandardTags()
	{
		$tags = array();
		$tags[] = new Info('formname', JText::_('LIB_REDFORM_TAG_DESCRIPTION_FORMNAME'));
		$tags[] = new Info('answers', JText::_('LIB_REDFORM_TAG_DESCRIPTION_ANSWERS'));
		$tags[] = new Info('submitkey', JText::_('LIB_REDFORM_TAG_DESCRIPTION_SUBMITKEY'));
		$tags[] = new Info('totalprice', JText::_('LIB_REDFORM_TAG_DESCRIPTION_TOTALPRICE'));
		$tags[] = new Info('totalvat', JText::_('LIB_REDFORM_TAG_DESCRIPTION_TOTALVAT'));
		$tags[] = new Info('totalpricevatexcluded', JText::_('LIB_REDFORM_TAG_DESCRIPTION_TOTALPRICEVATEXCLUDED'));
		$tags[] = new Info('confirmlink', JText::_('LIB_REDFORM_TAG_DESCRIPTION_CONFIRMLINK'));
		$tags[] = new Info('paymentlink', JText::_('LIB_REDFORM_TAG_DESCRIPTION_PAYMENTLINK'));
		$tags[] = new Info('submitter_id', JText::_('LIB_REDFORM_TAG_DESCRIPTION_SUBMITTER_ID'));

		return $tags;
	}

	/**
	 * Get custom fields tags
	 *
	 * @return Info[]
	 * @since    3.3.19
	 */
	private function getFieldsTags()
	{
		$query = $this->_db->getQuery(true);

		$query->select('f.id, f.field, f.fieldtype')
			->from('#__rwf_fields AS f')
			->order('f.field');

		if ($formId = $this->getState('filter.form_id'))
		{
			$query->innerJoin('#__rwf_form_field AS ff ON ff.field_id = f.id')
				->where('ff.form_id = ' . (int) $formId);
		}

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		$tags = array();

		foreach ((array) $res as $r)
		{
			$tags[] = new Info('field_' . $r->id, $r->field . ' (' . $r->fieldtype . ')', 'fields', $r->id);
		}

		return $tags;
	}

	/**
	 * index by section
	 *
	 * @param   array  $tags  all tags
	 *
	 * @return array
	 * @since    3.3.19
	 */
	private function tagsBySection($tags)
	{
		$res = array();

		foreach ($tags as $tag)
		{
			@$res[$tag->section][] = $tag;
		}

		return $res;
	}
}
