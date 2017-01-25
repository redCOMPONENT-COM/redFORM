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
	 * Create a section
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 */
	public function createSectionIfNotExists($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redform&view=sections');
		$I->waitForText('Sections', 30, ['css' => 'H1']);

		if ($I->isElementPresent('//*[@id="table-items"]//td//*[contains(., "' . $params['name'] . '")]'))
		{
			return;
		}

		$I->createSection($params);
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
		$I->fillField(['id' => 'jform_field'], $params['name']);
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
	 * Create a field if it doesn't already exists
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 */
	public function createFieldIfNotExists($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redform&view=fields');
		$I->waitForText('Fields', 30, ['css' => 'H1']);

		if ($I->isElementPresent('//*[@id="fieldList"]//td//*[contains(., "' . $params['name'] . '")]'))
		{
			return;
		}

		$I->createField($params);
	}

	/**
	 * Create a Form if doesn't exist
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 */
	public function createFormIfNotExists($params)
	{
		$I = $this;
		$I->amOnPage('administrator/index.php?option=com_redform&view=forms');
		$I->waitForText('Forms', 30, ['css' => 'H1']);

		if ($I->isElementPresent('//*[@id="formList"]//td//*[contains(., "' . $params['name'] . '")]'))
		{
			return;
		}

		$I->createForm($params);
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
		$I->amOnPage('administrator/index.php?option=com_redform&view=forms');
		$I->waitForText('Forms', 30, ['css' => 'H1']);
		$I->click(['xpath' => '//button[contains(@onclick, "form.add")]']);
		$I->waitForText('Form name', 30, ['css' => 'label']);
		$I->fillField(['id' => 'jform_formname'], $params['name']);

		$I->click(['xpath' => '//button[contains(@onclick, "form.save")]']);

		if (!empty($params['fields']))
		{
			$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
			$I->click('//*[@id="formList"]//td//*[contains(., "' . $params['name'] . '")]');
			$I->waitForText('Form name', 30, ['css' => 'label']);

			foreach ($params['fields'] as $fieldName)
			{
				$I->click(['xpath' => '//*[@id="formTabs"]/li/a[normalize-space(text()) = "Fields"]']);

				$I->click(['xpath' => '//button[contains(@onclick, "formfield.add")]']);
				$I->waitForText('Form field', 30, ['css' => 'h1']);
				$I->selectOptionInChosenByIdUsingJs('jform_field_id', $fieldName);
				$I->click(['xpath' => '//button[contains(@onclick, "formfield.save")]']);

				$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
			}
		}

		$I->click(['xpath' => '//button[contains(@onclick, "form.save")]']);
	}

	/**
	 * Return true if element was found on page
	 *
	 * @param   string  $element  element descriptor
	 *
	 * @return bool
	 */
	protected function isElementPresent($element)
	{
		$I = $this;

		try
		{
			$I->seeElement($element);
		}
		catch (\PHPUnit_Framework_AssertionFailedError $f)
		{
			return false;
		}

		return true;
	}
}
