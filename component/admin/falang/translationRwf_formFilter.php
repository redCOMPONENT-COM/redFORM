<?php
// No direct access
defined('_JEXEC') or die;

class translationRwf_formFilter extends translationFilter
{
	public function __construct($contentElement)
	{
		$this->filterNullValue = "-1";
		$this->filterType = "rwf_form";
		$this->filterField = $contentElement->getFilter("rwf_form");
		parent::__construct($contentElement);
	}

	public function _createFilter()
	{
		if (!$this->filterField) return "";
		$filter = "";

		//since joomla 3.0 filter_value can be '' too not only filterNullValue
		if (isset($this->filter_value) && strlen($this->filter_value) > 0 && $this->filter_value != $this->filterNullValue)
		{
			$db = JFactory::getDBO();
			$filter = " c." . $this->filterField . "=" . $db->escape($this->filter_value, true);
		}
		return $filter;
	}

	/**
	 * add filter to query
	 *
	 * @param   JDatabaseQuery  $query  query object
	 *
	 * @return JDatabaseQuery
	 */
	public function addFilter(JDatabaseQuery $query)
	{
		if (!$this->filterField)
		{
			return $query;
		}

		$db = JFactory::getDbo();

		//since joomla 3.0 filter_value can be '' too not only filterNullValue
		if (isset($this->filter_value) && strlen($this->filter_value) > 0 && $this->filter_value != $this->filterNullValue)
		{
			$query->where(" c." . $this->filterField . " = " . $db->escape($this->filter_value, true));
		}

		return $query;
	}

	function _createfilterHTML()
	{
		if (!$this->filterField) return "";

		$allCategoryOptions = array();

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select('id AS value, formname AS text');
		$query->from('#__rwf_forms');
		$query->where('published = 1');
		$query->order('formname');

		$db->setQuery($query);
		$options = $db->loadObjectList();

		if (!FALANG_J30)
		{
			$allOptions[-1] = JHTML::_('select.option', '-1', JText::_('JALL'));
		}
		$options = array_merge($allOptions, $options);

		$field = array();

		if (FALANG_J30)
		{
			$field["title"] = 'Form';
			$field["position"] = 'sidebar';
			$field["name"] = 'rwf_form_filter_value';
			$field["type"] = 'rwf_form';
			$field["options"] = $options;
			$field["html"] = JHTML::_('select.genericlist', $options, 'rwf_form_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);
		}
		else
		{
			$field["title"] = 'Form';
			$field["html"] = JHTML::_('select.genericlist', $options, 'rwf_form_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);
		}

		return $field;

	}


}
