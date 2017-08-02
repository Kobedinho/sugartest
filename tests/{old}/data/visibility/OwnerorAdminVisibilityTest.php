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


class OwnerOrAdminVisibilityTest extends Sugar_PHPUnit_Framework_TestCase
{   
    public function setUp() 
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(); 
    }

    public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset( $GLOBALS['current_user']);
    }

    public function testOwnerVisibility() 
    {
        $bean = new Account();
        $owner = new OwnerOrAdminVisibility($bean);
        $query = "";
        $query = $owner->addVisibilityWhere($query);
        $this->assertContains('assigned_user_id', $query);
    }

    public function testModuleAdminVisibility()
    {
        $bean = new Account();
        $GLOBALS['current_user'] = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['current_user']->method('isAdminForModule')->willReturn(true);
        $owner = new OwnerOrAdminVisibility($bean);
        $query = "";
        $query = $owner->addVisibilityWhere($query);
        $this->assertEmpty($query);
    }
}
