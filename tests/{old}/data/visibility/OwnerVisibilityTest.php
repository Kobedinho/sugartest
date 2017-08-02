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


class OwnerVisibilityTest extends Sugar_PHPUnit_Framework_TestCase 
{   
    public function setUp() 
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(); 
    }

    public function tearDown() 
    {
        $GLOBALS['db']->query("DELETE FROM dashboards WHERE 1=1");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset( $GLOBALS['current_user']);
    }

    public function testOwnerVisibility() 
    {
        // Create a dashboard for current user
        $dashboard = new Dashboard();
        $dashboard->name = 'test dashboard1';
        $dashboard->save(); 
        // Create a dashboard for another user       
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(); 
        $dashboard = new Dashboard();
        $dashboard->name = 'test dashboard2';
        $dashboard->save();
        $dashboard = new Dashboard();
        $count = count($dashboard->get_full_list());
        $this->assertEquals(1, $count);       
    } 
}
