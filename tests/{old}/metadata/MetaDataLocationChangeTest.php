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

        
class MetaDataLocationChangeTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_expectedPortalModules = array(
        'Bugs' => 'Bugs',
        'Cases' => 'Cases',
        'Contacts' => 'Contacts',
        'KBContents' => 'KBContents',
    );
    
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }
    
    /**
     * @dataProvider _mobileMetaDataFilesExistsProvider
     * @param string $module The module name
     * @param string $view The view type
     * @param string $filepath The path to the metadata file
     */
    public function testMobileMetaDataFilesExists($module, $view, $filepath)
    {
        $exists = file_exists($filepath);
        $this->assertTrue($exists, "Mobile metadata file for $view view of the $module module was not found");
    }
    
    /**
     * @dataProvider _portalMetaDataFilesExistsProvider
     * @param string $module The module name
     * @param string $view The view type
     * @param string $filepath The path to the metadata file
     */
    public function testPortalMetaDataFilesExists($module, $view, $filepath)
    {
        $exists = file_exists($filepath);
        $this->assertTrue($exists, "Portal metadata file for $view view of the $module module was not found");
    }
    
    /**
     * @dataProvider _platformList
     * @param string $platform The platform to test
     */
    public function testMetaDataManagerReturnsCorrectPlatformResults($platform)
    {
        $mm = MetaDataManager::getManager(array($platform));
        $data = $mm->getModuleViews('Bugs');
        $this->assertTrue(isset($data['list']['meta']['panels']), "Panels meta array for list not set for $platform platform of Bugs module");
        $this->assertTrue(isset($data['record']['meta']['panels']), "Panels meta array for record not set for $platform platform of Bugs module");
    }
    
    public function testPortalLayoutsAreCorrect()
    {
        $pb = new SugarPortalBrowser();
        $nodes = $pb->getNodes();
        $this->assertNotEmpty($nodes[2]);
        
        $layoutNode = $nodes[2];
        $this->assertNotEmpty($layoutNode['children']);
        
        foreach ($layoutNode['children'] as $child) {
            $this->assertTrue(isset($child['module']), 'Module is not set in a child node');
            $this->assertNotEmpty($this->_expectedPortalModules[$child['module']], "$child[module] not found in expected portal modules");
            $this->assertNotEmpty($child['children'], 'Children of the child not set');
            $hasDetailView = $this->_hasRecordViewLink($child['children']);
            $this->assertTrue($hasDetailView, "$child[module] does not have a record view link");
        }
    }
    
    public function _mobileMetaDataFilesExistsProvider()
    {
        return array(
            array('module' => 'Accounts', 'view' => 'edit', 'filepath' => 'modules/Accounts/clients/mobile/views/edit/edit.php'),
            array('module' => 'Cases', 'view' => 'detail', 'filepath' => 'modules/Cases/clients/mobile/views/detail/detail.php'),
            array('module' => 'Contacts', 'view' => 'edit', 'filepath' => 'modules/Contacts/clients/mobile/views/edit/edit.php'),
            array('module' => 'Employees', 'view' => 'list', 'filepath' => 'modules/Employees/clients/mobile/views/list/list.php'),
            array('module' => 'Meetings', 'view' => 'detail', 'filepath' => 'modules/Meetings/clients/mobile/views/detail/detail.php'),
        );
    }
    
    public function _portalMetaDataFilesExistsProvider()
    {
        return array(
            array('module' => 'Bugs', 'view' => 'record', 'filepath' => 'modules/Bugs/clients/portal/views/record/record.php'),
            array('module' => 'Cases', 'view' => 'list', 'filepath' => 'modules/Cases/clients/portal/views/list/list.php'),
            array('module' => 'Contacts', 'view' => 'record', 'filepath' => 'modules/Contacts/clients/portal/views/record/record.php'),
        );
    }
    
    public function _platformList()
    {
        return array(
            array('platform' => 'portal'),
            array('platform' => 'mobile'),
        );
    }
    
    protected function _hasRecordViewLink($child)
    {
        foreach ($child as $props) {
            if (isset($props['action']) && strpos($props['action'], 'RecordView') !== false) {
                return true;
            }
        }
        
        return false;
    }
}
