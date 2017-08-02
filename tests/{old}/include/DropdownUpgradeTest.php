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

class DropdownUpgradeTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_language = 'en_us'; // Test against English
    protected $_testCustFile = array('include' => 'tests/{old}/include/DropdownUpgradeTestCustFile.php', 'ext' => 'tests/{old}/include/Bug60008-60607TestCustomFile.php');
    protected $_custFile = array('include' => 'custom/include/language/en_us.lang.php', 'ext' => 'custom/application/Ext/Language/en_us.lang.ext.php');
    protected $_custDir = array('include' => 'custom/include/language/', 'ext' => 'custom/application/Ext/Language/');
    protected $_backedUp = false;
    
    public function setUp()
    {
        // Back up existing custom app list strings if they exist
        foreach($this->_custFile as $custFile) {
            if (file_exists($custFile)) {
                rename($custFile, $custFile . '-backup');
                $this->_backedUp = true;
            }
        }

        // For cases in which this test runs before the custom include directory
        // is created
        foreach ($this->_custDir as $custDir) {
            if (!is_dir($custDir)) {
                mkdir_recursive($custDir);
            }
        }

        foreach ($this->_testCustFile as $type => $testCustFile) {
            // Copy our test files into place
            copy($this->_testCustFile[$type], $this->_custFile[$type]);
        }

        // File map cache this bad boy
        foreach($this->_custFile as $custFile) {
            SugarAutoLoader::addToMap($custFile);
        }
    }
    
    public function tearDown() {
        foreach($this->_custFile as $custFile) {
            // Delete the custom file we just created
            unlink($custFile);
            
            if (file_exists($custFile . '-backup')) {
                // Move the backup back into place. No need to mess with the file map cache
                rename($custFile . '-backup', $custFile);
            } else {
                // There was no back up, so remove this from the file map cache
                SugarAutoLoader::delFromMap($custFile);
            }
        }
    }

    /**
     * Tests that both $app_list_strings and $GLOBALS['app_list_strings'] are picked
     * up when getting app_list_strings
     * 
     * @group Bug60008
     */
    public function testAppListStringsParsedEvenWhenInGlobals()
    {
        $als = return_app_list_strings_language($this->_language);
        
        // Assert that the indexes are found
        $this->assertArrayHasKey('aaa_test_list', $als, "First GLOBALS index not found");
        $this->assertArrayHasKey('bbb_test_list', $als, "First app_list_strings index not found");
        $this->assertArrayHasKey('ccc_test_list', $als, "Second GLOBALS index not found");
        
        // Assert that the indexes actually have elements
        $this->assertArrayHasKey('boop', $als['bbb_test_list'], "An element of the first app_list_strings array was not found");
        $this->assertArrayHasKey('sam', $als['ccc_test_list'], "An element of the second GLOBALS array not found");
        
        // Assert that GLOBALS overriding $app_list_strings work
        $this->assertArrayHasKey('zzz_test_list', $als, "Bug 60393 - dropdown not picked up");
        $this->assertArrayHasKey('X2', $als['zzz_test_list'], "Bug 60393 - dropdown values not picked up");
        $this->assertEquals($als['zzz_test_list']['X2'], 'X2 Z', "Bug 60393 - proper dropdown value not picked up");
        
        // Assert that app_list_strings overriding GLOBALS work
        $this->assertArrayHasKey('yyy_test_list', $als, "Bug 60393 - second dropdown not picked up");
        $this->assertArrayHasKey('Y2', $als['yyy_test_list'], "Bug 60393 - second dropdown values not picked up");
        $this->assertEquals($als['yyy_test_list']['Y2'], 'Y2 Q', "Bug 60393 - proper dropdown value not picked up for second dropdown");
    }
}
