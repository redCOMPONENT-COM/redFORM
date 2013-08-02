<?php
// No direct access
defined('_JEXEC') or die;

class translationRe_eventFilter extends translationFilter
{
	public function __construct($contentElement)
	{
		$this->filterNullValue = "-1";
		$this->filterType = "re_event";
		$this->filterField = $contentElement->getFilter("re_event");
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

	function _createfilterHTML()
	{
		if (!$this->filterField) return "";

		$allCategoryOptions = array();

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select('id AS value, title AS text');
		$query->from('#__redevent_events');
		$query->where('published = 1');
		$query->order('title');

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
			$field["title"] = 'Event';
			$field["position"] = 'sidebar';
			$field["name"] = 're_event_filter_value';
			$field["type"] = 're_event';
			$field["options"] = $options;
			$field["html"] = JHTML::_('select.genericlist', $options, 're_event_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);
		}
		else
		{
			$field["title"] = 'Event';
			$field["html"] = JHTML::_('select.genericlist', $options, 're_event_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);
		}

		return $field;

	}


}
