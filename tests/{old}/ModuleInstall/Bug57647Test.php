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

require_once 'ModuleInstall/ModuleInstaller.php';

class Bug57647Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected static $_testUpdateDir;
    protected static $_testInstallPath = 'custom/modulebuilder/packages/bilbo/modules/bango/metadata/';
    protected static $_testInstallFiles = array(
        'wireless.editviewdefs.php',
        'wireless.detailviewdefs.php',
        'wireless.listviewdefs.php',
    );
    
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('moduleList');
        
        // Get the test update directory. Will need this for assertions and cleanup
        self::$_testUpdateDir = dirname(self::$_testInstallPath) . '/clients/mobile/views/';
        
        // Move the files into place
        if (!is_dir(self::$_testInstallPath)) {
            mkdir_recursive(self::$_testInstallPath);
        }
        
        // Move our test files into place
        foreach (self::$_testInstallFiles as $file) {
            copy('tests/{old}/ModuleInstall/Bug57647Support/' . $file, self::$_testInstallPath . $file);
        }
        
        // Update the metadata
        $mi = new ModuleInstaller();
                
        // Set the install defs - this mocks a call to install()
        $mi->installdefs = array(
            'id' => 'project_bilbo',
            'copy' =>  array(
                array(
                    'from' => '<basepath>/bilbo',
                    'to' => 'custom/modulebuilder/packages/bilbo',
                ),
            ),
        );
        
        // This is the add for the fix
        $mi->update_wireless_metadata();
    }
    
    public static function tearDownAfterClass()
    {
        // Remove our test package files
        foreach (self::$_testInstallFiles as $file) {
            unlink(self::$_testInstallPath . $file);
        }
        
        // Remove our updated files
        foreach (array('edit', 'detail', 'list') as $type) {
            unlink(self::$_testUpdateDir . $type . '/' . $type  .'.php');
        }
        
        // Remove our created directory
        rmdir_recursive('custom/modulebuilder/packages/bilbo/');
        
        // Teardown our environment
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider _testLegacyFilenameProvider
     * @group Bug57647
     */
    public function testLegacyCustomModuleMetadataWasConvertedOnInstall($type)
    {
        // Assert existence of the file
        $file = self::$_testUpdateDir . $type . '/' . $type . '.php';
        $this->assertFileExists($file, "New metadata file $file was not found");
        
        // Handle assertion of content
        require $file;
                
        // Begin assertions, using package_module name
        $this->assertNotEmpty($viewdefs['f0001_bango']['mobile']['view'][$type], "$type view defs are empty");
        
        $defs = $viewdefs['f0001_bango']['mobile']['view'][$type];
        $this->assertArrayHasKey('panels', $defs, 'No panels array found in view defs');
        $this->assertArrayHasKey('fields', $defs['panels'][0], 'Fields array missing or in incorrect format in view defs');
        $this->assertNotEmpty($defs['panels'][0]['fields'], 'Fields array is empty');
    }
    
    /**
     * Data provider for the test method
     * 
     * @return array
     */
    public function _testLegacyFilenameProvider() {
        return array(
            array('edit'), 
            array('detail'), 
            array('list'),
        );
    }
}
