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
 * @ticket BR-1345
 * Test byref logic hooks
 *
 */
class LogicHookRefTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $bean;
    protected $hook;

    public function setUp()
    {
        $this->bean = new Account();
        SugarTestHelper::setUp('current_user');
        LogicHook::refreshHooks();
	}

	public function tearDown()
	{
	    if(!empty($this->hook)) {
	        call_user_func_array('remove_logic_hook', $this->hook);
	    }
	    SugarTestHelper::tearDown();
	}

    public function testCallLogicHook()
    {
        $this->hook = array('Accounts', 'test_event', Array(1, 'Test hook BR-1345', __FILE__, 'BR1345TestHook', 'count', 'foo', 123));
        call_user_func_array('check_logic_hook_file', $this->hook);
        $this->bean->call_custom_logic("test_event", "bar", 345);
        $this->assertInstanceOf("Account", BR1345TestHook::$args[0]);
        $this->assertEquals(array('test_event', 'bar', 'foo', 123), array_slice(BR1345TestHook::$args, 1));
    }

}

class BR1345TestHook
{
    static public $args = '';
    public function count(&$bean, $event, $arguments)
    {
        self::$args = func_get_args();
    }

}
