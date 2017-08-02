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

require_once('tests/{old}/service/APIv3Helper.php');

class RESTAPI4FieldACLsTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $v4;
    
    public function setUp() {
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("current_user");

        //Reload langauge strings
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Accounts');
        
        $this->v4 = new SugarWebServiceUtilv4();
    }
    
    public function tearDown() {
        // Copied from RESTAPI4Test, minus the isset check which is unnecessary
        unset($GLOBALS['listViewDefs']);
        unset($GLOBALS['viewdefs']);
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['mod_strings']);
        SugarTestHelper::tearDown();
    }
    
    /**
     * @dataProvider _wirelessListProvider
     * @param $module
     * @param $metadatafile
     */
    public function testAddFieldLevelACLsToWirelessList($module, $metadatafile) {
        $defs = $this->v4->get_module_view_defs($module, 'wireless', 'list');
        
        // $defs should be converted and ACLed at this point
        // find the name field
        foreach($defs AS $def) {
            $this->assertArrayHasKey('name', $def, "No name index");
        }

        $this->assertArrayHasKey('acl', $defs[0], 'no ACL attached to it');
        
        // Get the known metadata
        require $metadatafile;
        $known = $viewdefs[$module]['mobile']['view']['list'];
        
        $this->assertArrayHasKey('panels', $known, 'No panels array found in the known metadata');
        $this->assertEquals(count($defs), count($known['panels'][0]['fields']), 'Metadata converted field count different than known count');
    }
    
    /**
     * @dataProvider _wirelessGridProvider
     * @param $module
     * @param $view
     */
    public function testAddFieldLevelACLsToWirelessGrid($module, $view, $metadatafile) {
        $defs = $this->v4->get_module_view_defs($module, 'wireless', $view);
        
        // $defs should be converted and ACLed at this point
        $this->assertTrue(isset($defs['panels']), 'panels index not found in viewdef return');
        
        // Compare with known metadata
        require $metadatafile;
        $known = $viewdefs[$module]['mobile']['view'][$view];
        $this->assertArrayHasKey('panels', $known, 'No panels array found in the known metadata');
        $this->assertEquals(count($defs['panels']), count($known['panels'][0]['fields']), 'Metadata converted field count different than known count');
    }
    
    /**
     * ANY ENTRY MADE TO THIS RETURN SHOULD HAVE A CORRESPONDING LEGACY METADATA
     * FILE SAVED IN tests/service/metadata AND NAMED $module . 'legacy' . $view . '.php'
     * 
     * @return array
     */
    public function _wirelessGridProvider() {
        return array(
            array('module' => 'Accounts', 'view' => 'edit', 'metadatafile' => 'modules/Accounts/clients/mobile/views/edit/edit.php',),
            array('module' => 'Accounts', 'view' => 'detail', 'metadatafile' => 'modules/Accounts/clients/mobile/views/detail/detail.php',),
        );
    }
    
    /**
     * ANY ENTRY MADE TO THIS RETURN SHOULD HAVE A CORRESPONDING LEGACY METADATA
     * FILE SAVED IN tests/service/metadata AND NAMED $module . 'legacy' . $view . '.php'
     * 
     * @return array
     */
    public function _wirelessListProvider() {
        return array(
            array('module' => 'Cases', 'metadatafile' => 'modules/Cases/clients/mobile/views/list/list.php',),
        );
    }
}
