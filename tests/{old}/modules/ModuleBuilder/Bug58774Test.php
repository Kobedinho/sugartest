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


class Bug58774Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_originalRequest = array();
    protected $_originalDictionary = array();
    protected $_fileMapFiles = array();
    protected $_backedUpFiles = array();
    protected $_tearDownFiles = array(
        'custom/modules/Calls/Ext/Vardefs/vardefs.ext.php',
        'custom/modules/Calls/metadata/SearchFields.php',
        'custom/Extension/modules/Calls/Ext/Vardefs/sugarfield_duration_hours.php',        
        'cache/modules/Calls/Callvardefs.php',
    );
    
    public function setUp()
    {
        if (isset($GLOBALS['dictionary']['Call'])) {
            $this->_originalDictionary = $GLOBALS['dictionary']['Call'];
        }
        
        // Back up any current files we might have
        foreach ($this->_tearDownFiles as $file) {
            if (file_exists($file)) {
                rename($file, str_replace('.php', '-unittestbackup', $file));
                $this->_backedUpFiles[] = $file;
                // And if there are any of these files in the file map cache, 
                // handle those too
                if (SugarAutoLoader::fileExists($file)) {
                    $this->_fileMapFiles[] = $file;
                    SugarAutoLoader::delFromMap($file);
                }
            }
        }
        
        // The current user needs to be an admin user
        SugarTestHelper::setUp('current_user', array(true, true));
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));
        
        $this->_originalRequest = array('r' => $_REQUEST, 'p' => $_POST);
    }
    
    public function tearDown()
    {
        $_REQUEST = $this->_originalRequest['r'];
        $_POST = $this->_originalRequest['p'];
        
        SugarTestHelper::tearDown();
        
        // Remove created files
        foreach ($this->_tearDownFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
            
            if (SugarAutoLoader::fileExists($file)) {
                SugarAutoLoader::delFromMap($file);
            }
        }
        
        // Restore our backups
        foreach ($this->_backedUpFiles as $file) {
            rename(str_replace('.php', '-unittestbackup', $file), $file);
        }
        
        // Restore the file map cache
        foreach ($this->_fileMapFiles as $file) {
            SugarAutoLoader::addToMap($file);
        }
        
        // Reset the dictionary
        if (!empty($this->_originalDictionary)) {
            $GLOBALS['dictionary']['Call'] = $this->_originalDictionary;
        }
    }
    
    public function testCacheClearedAfterSavingFieldChanges()
    {
        // Setup some of the items needed in the request
        $_REQUEST = $_POST =array(
            'module' => 'ModuleBuilder',
            'action' => 'saveSugarField',
            'view_module' => 'Calls',
            'type' => 'int',
            'name' => 'duration_minutes',
            'labelValue' => 'Duration Minutes:',
            'label' => 'LBL_DURATION_MINUTES',
            'comments' => 'Call duration, minutes portion',
            'min' => '5',
            'max' => '90',
        );
        
        $controller = new ModuleBuilderController();
        $controller->action_saveSugarField();
        
        $newdefs = $this->_getNewVardefFromCache();
        
        // Handle assertions
        $this->assertNotEmpty($newdefs, "New vardef was not found");
        $this->assertTrue(isset($newdefs['fields']['duration_minutes']), "duration_minutes field not found in the vardef");
        $this->assertArrayHasKey('min', $newdefs['fields']['duration_minutes'], "Min value not saved");
        $this->assertEquals(5, $newdefs['fields']['duration_minutes']['min'], "Min did not save its value properly");
        $this->assertArrayHasKey('max', $newdefs['fields']['duration_minutes'], "Max value not saved");
        $this->assertEquals(90, $newdefs['fields']['duration_minutes']['max'], "Max did not save its value properly");
    }
    
    protected function _getNewVardefFromCache()
    {
        VardefManager::loadVardef('Calls', 'Call', true);
        return $GLOBALS['dictionary']['Call'];
    }
}