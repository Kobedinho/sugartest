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

require_once 'tests/{old}/rest/RestTestPortalBase.php';
class RestMetadataModuleListPortalTest extends RestTestPortalBase {
    public $createdStudioFile = false;
    public $unitTestFiles = array();
    public $oppTestPath ='modules/Accounts/clients/portal/views/list/list.php';

    public function setUp()
    {
        parent::setUp();
        // Portal test needs this one, tear down happens in parent
        SugarTestHelper::setup('mod_strings', array('ModuleBuilder'));

        $this->unitTestFiles[] = $this->oppTestPath;
        if (!file_exists('modules/Accounts/metadata/studio.php')) {
            SugarAutoLoader::put('modules/Accounts/metadata/studio.php', '<?php' . "\n\$time = time();", true);
            $this->createdStudioFile = true;
        }

    }

    public function tearDown()
    {
        if ($this->createdStudioFile && file_exists('modules/Accounts/metadata/studio.php')) {
            SugarAutoLoader::unlink('modules/Accounts/metadata/studio.php');
        }

        foreach($this->unitTestFiles as $unitTestFile ) {
            if ( file_exists($unitTestFile) ) {
                // Ignore the warning on this, the file stat cache causes the file_exist to trigger even when it's not really there
                SugarAutoLoader::unlink($unitTestFile);
            }
        }

        if (file_exists($this->oppTestPath)) {
            SugarAutoLoader::unlink($this->oppTestPath);
        }
        SugarAutoLoader::saveMap();
        // Set the tabs back to what they were
        if ( isset($this->defaultTabs[0]) ) {
            $tabs = new TabController();

            $tabs->set_system_tabs($this->defaultTabs[0]);
            $GLOBALS['db']->commit();
        }

        parent::tearDown();
    }

    // Need to set the platform to something else
    protected function _restLogin($username = '', $password = '', $platform = 'portal')
    {
        return parent::_restLogin($username,$password,$platform);
    }
    /**
     * @group rest
     */
    public function testMetadataGetModuleListPortal() {
        // Setup the tab controller here and get the default tabs for setting and resetting
        $tabs = new TabController();
        $this->defaultTabs = $tabs->get_tabs_system();

        $this->_clearMetadataCache();
        $restReply = $this->_restCall('me');

        $this->assertTrue(isset($restReply['reply']['current_user']['module_list']),'There is no portal module list');
        // There should only be the following modules by default: Bugs, Cases, KBOLDDocuments, Leads
        $enabledPortal = array('Cases','Contacts');
        $restModules = $restReply['reply']['current_user']['module_list'];

        unset($restModules['_hash']);
        foreach ( $enabledPortal as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the portal module list.');
        }
        // Bugs and KBOLDDocuments are sometimes enabled, and they are fine, just not in the normal list
        $idx = array_search('Bugs',$restModules);
        if ( is_int($idx) ) {
            unset($restModules[$idx]);
        }
        // Although there are 4 OOTB portal modules, only 2 are enabled by default
        $this->assertEquals(2,count($restModules),'There are extra modules in the portal module list');
        // add module

        $newModuleList = array('Home','Accounts','Contacts','Opportunities','Bugs','Leads','Calendar','Reports','Quotes','Documents','Emails','Campaigns','Calls','Meetings','Tasks','Notes','Forecasts','Cases','Prospects','ProspectLists');

        $tabs->set_system_tabs($newModuleList);
        $GLOBALS['db']->commit();
        // Do this to load the tab list into cache
        $tabs->get_tabs_system();
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('me');

        $this->assertTrue(isset($restReply['reply']['current_user']['module_list']),'There is no portal module list');
        // There should only be the following modules by default: Bugs, Cases, KBOLDDocuments, Contacts
        // And now 3 are enabled
        $enabledPortal = array('Cases','Contacts', 'Bugs');
        $restModules = $restReply['reply']['current_user']['module_list'];

        unset($restModules['_hash']);
        foreach ( $enabledPortal as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the portal module list.');
        }
        $this->assertEquals(3,count($restModules),'There are extra modules in the portal module list');

        // Set to include Opportunities
        $newModuleList = array('Home','Accounts','Contacts','Opportunities','Leads','Calendar','Reports','Quotes','Documents','Emails','Campaigns','Calls','Meetings','Tasks','Notes','Forecasts','Cases','Prospects','ProspectLists');

        $tabs->set_system_tabs($newModuleList);
        $GLOBALS['db']->commit();
        // Do this to load the tab list into cache
        $tabs->get_tabs_system();
        // Now add an extra file and make sure it gets picked up
        if (is_dir($dir = dirname($this->oppTestPath)) === false) {
            sugar_mkdir($dir, null, true);
        }
        SugarAutoLoader::put($this->oppTestPath, "<?php\n\$viewdefs['Accounts']['portal']['view']['list'] = array('test' => 'Testing');", true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('me');

        $this->assertTrue(in_array('Accounts',$restReply['reply']['current_user']['module_list']),'The new Accounts module did not appear in the portal list');

    }

    /**
     * @group rest
     * @group Bug56911
     */
    public function testPortalMetadataModulesContainsNotes()
    {
        // Get the metadata for portal
        $restReply = $this->_restCall('metadata?type_filter=modules&platform=portal');
        $this->assertArrayHasKey('modules', $restReply['reply'], "The modules index is missing from the response");
        $this->assertArrayHasKey('Notes', $restReply['reply']['modules'], 'Notes was not returned in the modules metadata as expected');
    }
}