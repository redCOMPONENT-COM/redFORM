<?php
namespace Step\Acceptance;

class Adminredform extends \AcceptanceTester
{
	/**
	 * Create a section
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 */
	public function createSection($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redform&view=sections');
		$I->waitForText('Sections', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "section.add")]']);
		$I->waitForText('Name', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_name'], $params['name']);

		if (!empty($params['class']))
		{
			$I->fillField(['id' => 'jform_class'], $params['class']);
		}

		if (!empty($params['description']))
		{
			$I->fillTinyMceEditorById('jform_description', $params['description']);
		}

		$I->click(['xpath' => '//button[contains(@onclick, "section.save")]']);
	}

	/**
	 * Create a field
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 */
	public function createField($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redform&view=fields');
		$I->waitForText('Fields', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "field.add")]']);
		$I->waitForText('Name', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_field'], $params['field']);
		$I->selectOptionInChosenById('jform_fieldtype', $params['fieldtype']);

		if (isset($params['field_header']))
		{
			$I->fillField(['id' => 'jform_field_header'], $params['field_header']);
		}

		if (isset($params['tooltip']))
		{
			$I->fillField(['id' => 'jform_tooltip'], $params['tooltip']);
		}

		if (isset($params['default']))
		{
			$I->fillField(['id' => 'jform_default'], $params['default']);
		}

		$I->click(['xpath' => '//button[contains(@onclick, "field.save")]']);
	}

	/**
	 * Create a form
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 */
	public function createForm($params)
	{
		$I = $this;
	}
}
