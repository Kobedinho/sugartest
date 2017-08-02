<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


class TemplateDateTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $hasExistingCustomSearchFields = false;

    public function setUp()
    {
		if(file_exists('custom/modules/Opportunities/metadata/SearchFields.php'))
		{
		   $this->hasExistingCustomSearchFields = true;
		   copy('custom/modules/Opportunities/metadata/SearchFields.php', 'custom/modules/Opportunities/metadata/SearchFields.php.bak');
		   SugarAutoLoader::unlink('custom/modules/Opportunities/metadata/SearchFields.php');
		} else if(!file_exists('custom/modules/Opportunities/metadata')) {
		   SugarAutoLoader::ensureDir('custom/modules/Opportunities/metadata');
		}
    }

    public function tearDown()
    {

    	if(!$this->hasExistingCustomSearchFields)
		{
		   SugarAutoLoader::unlink('custom/modules/Opportunities/metadata/SearchFields.php', true);
		}

		if(file_exists('custom/modules/Opportunities/metadata/SearchFields.php.bak')) {
		   copy('custom/modules/Opportunities/metadata/SearchFields.php.bak', 'custom/modules/Opportunities/metadata/SearchFields.php');
		   unlink('custom/modules/Opportunities/metadata/SearchFields.php.bak');
		   SugarAutoLoader::addToMap('custom/modules/Opportunities/metadata/SearchFields.php');
		}

    }

    public function testEnableRangeSearchInt()
    {
		$_REQUEST['view_module'] = 'Opportunities';
		$_REQUEST['name'] = 'probability';
		$templateDate = new TemplateInt();
		$templateDate->enable_range_search = true;
		$templateDate->populateFromPost();
		$this->assertTrue(file_exists('custom/modules/Opportunities/metadata/SearchFields.php'));
		include('custom/modules/Opportunities/metadata/SearchFields.php');
		$this->assertTrue(isset($searchFields['Opportunities']['range_probability']));
		$this->assertTrue(isset($searchFields['Opportunities']['start_range_probability']));
		$this->assertTrue(isset($searchFields['Opportunities']['end_range_probability']));
		$this->assertTrue(!isset($searchFields['Opportunities']['range_probability']['is_date_field']));
		$this->assertTrue(!isset($searchFields['Opportunities']['start_range_probability']['is_date_field']));
		$this->assertTrue(!isset($searchFields['Opportunities']['end_range_probability']['is_date_field']));
    }

    public function testEnableRangeSearchDate()
    {
		$_REQUEST['view_module'] = 'Opportunities';
		$_REQUEST['name'] = 'date_closed';
		$templateDate = new TemplateDate();
		$templateDate->enable_range_search = true;
		$templateDate->populateFromPost();
		$this->assertTrue(file_exists('custom/modules/Opportunities/metadata/SearchFields.php'));
		include('custom/modules/Opportunities/metadata/SearchFields.php');
		$this->assertTrue(isset($searchFields['Opportunities']['range_date_closed']));
		$this->assertTrue(isset($searchFields['Opportunities']['start_range_date_closed']));
		$this->assertTrue(isset($searchFields['Opportunities']['end_range_date_closed']));
		$this->assertTrue(isset($searchFields['Opportunities']['range_date_closed']['is_date_field']));
		$this->assertTrue(isset($searchFields['Opportunities']['start_range_date_closed']['is_date_field']));
		$this->assertTrue(isset($searchFields['Opportunities']['end_range_date_closed']['is_date_field']));
    }

}
?>