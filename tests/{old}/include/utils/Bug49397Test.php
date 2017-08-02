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

/**
 * This tests checks to see that the get_user_array function correctly returns results
 * @author Collin Lee
 * 
 */
class Bug49397Test extends Sugar_PHPUnit_Framework_TestCase {

    var $user;
    var $user2;

	public function setUp()
	{
       $this->user = SugarTestUserUtilities::createAnonymousUser();
       $this->user2 = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->user);
        unset($this->user2);
    }

    public function testGetUserArray()
    {
        $users1 = get_user_array(false, '');
        $users2 = get_user_array(false, '', '', true, "{$this->user->user_name}");
        $users3 = get_user_array(false, '', '', true, "{$this->user2->user_name}");
        $this->assertNotEquals(count($users1), count($users2), 'get_user_array does not filter correctly on cache');
        $this->assertEquals(1, count($users2), 'get_user_array does not filter correctly on cache');
        $this->assertEquals(1, count($users3), 'get_user_array does not filter correctly on cache');
    }

}
