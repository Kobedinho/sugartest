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

class DashletSaveUserPreferencesTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }
    
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
    public function testCanStoreOptions() 
    {
        $options = array(
            'test1' => 'Test 1',
            'test2' => 'Test 2',
            );
        $dashlet = new Dashlet('unit_test_run');
        $dashlet->storeOptions($options);
        
        $prefs = $GLOBALS['current_user']->getPreference('dashlets', 'Home');
        
        $this->assertEquals($options,$prefs['unit_test_run']['options']);
        
        return $GLOBALS['current_user'];
    }
    
    /**
     * @depends testCanStoreOptions
     */
    public function testCanLoadOptions(User $user) 
    {
        $GLOBALS['current_user'] = $user;
        
        $options = array(
            'test1' => 'Test 1',
            'test2' => 'Test 2',
            );
        
        $dashlet = new Dashlet('unit_test_run');
        $this->assertEquals($options,$dashlet->loadOptions());
    }
    
    public function testLoadOptionsReturnsEmptyArrayIfNoPreferencesSet()
    {
        $dashlet = new Dashlet('unit_test_run');
        $this->assertEquals(array(),$dashlet->loadOptions());
    }
}
