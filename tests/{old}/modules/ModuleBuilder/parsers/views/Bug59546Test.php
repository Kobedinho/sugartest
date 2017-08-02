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


class Bug59546Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_testFile = 'custom/modules/Opportunities/metadata/dashletviewdefs.php';
    protected $_customDefs = array();
    
    public function setUp()
    {
        // Back up our current custom file and remove it if it is there
        if (file_exists($this->_testFile)) {
            copy($this->_testFile, $this->_testFile . '.backup');
            SugarAutoLoader::unlink($this->_testFile, true);
        }
        
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, true));
        
        // Build the POST array
        $_POST = array(
            // Enabled fields
            "group_0" => array("name", "probability", "account_name"),
            
            // Available fields
            "group_1" => array("opportunity_type", "lead_source"),
        );
        
        // Save the custom file
        $parser = new DashletMetaDataParser(MB_DASHLET, 'Opportunities');        
        $parser->handleSave();
        
        $id = create_guid();
        $dashlet = new TestMyOpportunitiesDashlet($id);
        $this->_customDefs = $dashlet->getColumns();
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
        
        // Remove the test file from the autoloader
        SugarAutoLoader::unlink($this->_testFile, true);
        
        // Restore the backup if it is there
        if (file_exists($this->_testFile . '.backup')) {
            rename($this->_testFile . '.backup', $this->_testFile);
            SugarAutoLoader::addToMap($this->_testFile);
        }
    }

    /**
     * Tests whether the right fields saved in the right way
     * 
     * @dataProvider _newLayoutMetaProvider
     * @param string  $field
     * @param boolean $default
     */
    public function testDashletSavePicksUpNewLayout($field, $default)
    {
        $defDefault = isset($this->_customDefs[$field]['default']) ? $this->_customDefs[$field]['default'] : 'ZZZ';
        $this->assertEquals($default, $defDefault, "Default value for $field did not meet the tested expectation");
    }

    /**
     * Data provider for the test method
     * 
     * @return array
     */
    public function _newLayoutMetaProvider()
    {
        return array(
            // Enabled fields
            array("field" => "name", "default" => true,),
            array("field" => "probability", "default" => true,),
            array("field" => "account_name", "default" => true,),
            // Available fields
            array("field" => "opportunity_type", "default" => false,),
            array("field" => "lead_source", "default" => false,),
        );
    }
}

/**
 * Simple accessor to the columns array via a protected parent method
 */
class TestMyOpportunitiesDashlet extends MyOpportunitiesDashlet
{
    public function getColumns()
    {
        parent::loadCustomMetadata();
        return $this->columns;
    }
}