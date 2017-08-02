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


class Bug57571Test extends Sugar_PHPUnit_Framework_TestCase 
{
    protected $_module = 'Quotes';
    protected $_backedUpDefs = false;
    protected $_field;
    protected $_panel;
    protected $_testFile = 'custom/modules/Quotes/metadata/editviewdefs.php';
    
    public function setUp() {
        // Setup our environment
        SugarTestHelper::init();
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array($this->_module));
        
        // Backup the custom metadata for quotes if there is one
        if (file_exists($this->_testFile)) {
            rename($this->_testFile, $this->_testFile . '.backup');
            $this->_backedUpDefs = true;
        }
        
        // Write it out
        $this->_addTabIndex();
    }
    
    public function tearDown() {
        // Get rid of the custom file we created
        unlink($this->_testFile);
        
        // Restore if necessary
        if ($this->_backedUpDefs) {
            rename($this->_testFile . '.backup', $this->_testFile);
        } 
        
        SugarTestHelper::tearDown();
    }
    
    public function testTabIndexFoundInViewDefs() {
        $parser = new GridLayoutMetaDataParser(MB_EDITVIEW, $this->_module);
        $defs = $parser->getLayout();
        $this->assertNotEmpty($defs[$this->_panel], "No panel named $this->_panel found in the modified defs");
        $panel = $defs[$this->_panel];
        
        $col = $this->_getColFromPanel($panel);
        $this->assertNotEmpty($col, "No column found with the correct field name for testing");
        $this->assertTrue(isset($col['tabindex']), "Tab index was not properly fetched for this test");
        $this->assertEquals($col['tabindex'], '1', 'Tab Index was not set to 1 as expected');
    }
    
    protected function _addTabIndex() {
        require 'modules/Quotes/metadata/editviewdefs.php';
        foreach ($viewdefs['Quotes']['EditView']['panels'] as $panelname => $paneldef) {
            foreach ($paneldef as $index => $row) {
                foreach ($row as $id => $value) {
                    if (is_string($value)) {
                        // Save the field name & panel
                        $this->_panel = $panelname;
                        $this->_field = $value;
                        
                        // Rewrite the def
                        $viewdefs['Quotes']['EditView']['panels'][$panelname][$index][$id] = array(
                            'name' => $value,
                            'tabindex' => '1',
                        );
                        
                        break 3;
                    }
                }
            }
        }
        
        mkdir_recursive(dirname($this->_testFile));
        write_array_to_file('viewdefs', $viewdefs, $this->_testFile);
    }
    
    protected function _getColFromPanel($panel) {
        foreach ($panel as $row) {
            foreach ($row as $col) {
                if (isset($col['name']) && $col['name'] == $this->_field) {
                    return $col;
                }
            }
        }
        
        return array();
    }
}
