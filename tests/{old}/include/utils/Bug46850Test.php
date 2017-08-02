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


class Bug46850Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $renames = array();
    protected $deletes = array();

    protected $hook = array(
        'test_logic_hook' => array(array(1, 'test_logic_hook', __FILE__, 'LogicHookTest', 'testLogicHook')),
    );

    public function setUp()
    {
        LogicHookTest::$called = false;
        unset($GLOBALS['logic_hook']);
        $GLOBALS['logic_hook'] = LogicHook::initialize();
        LogicHook::refreshHooks();
    }

    public function tearDown()
    {
        foreach($this->renames as $file) {
            rename($file.".bak", $file);
        }
        foreach($this->deletes as $file) {
            SugarAutoLoader::unlink($file);
        }
        unset($GLOBALS['logic_hook']);
        LogicHook::refreshHooks();
    }

    public static function tearDownAfterClass()
    {
    }

    protected function saveHook($file)
    {
        if(file_exists($file)) {
            rename($file, $file.".bak");
            $this->renames[] = $file;
        } else {
            $this->deletes[] = $file;
        }
    }

    public function getModules()
    {
        return array(
            array(''),
            array('Contacts'),
            array('Accounts'),
        );
    }

    /**
     * @dataProvider getModules
     */
    public function testHooksDirect($module)
    {
        $dir = rtrim("custom/modules/$module", "/");
        $file = "$dir/logic_hooks.php";
        $this->saveHook($file);
        if(!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        write_array_to_file('hook_array', $this->hook, $file);
        $GLOBALS['logic_hook']->getHooks($module, true); // manually refresh
        $GLOBALS['logic_hook']->call_custom_logic($module, 'test_logic_hook');
        $this->assertTrue(LogicHookTest::$called);
    }

    /**
     * @dataProvider getModules
     */
    public function testHooksExtDirect($module)
    {
        if(empty($module)) {
            $dir = "custom/application/Ext/LogicHooks";
        } else {
            $dir = "custom/modules/$module/Ext/LogicHooks";
        }
        if(!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $file = "$dir/logichooks.ext.php";
        $this->saveHook($file);
        write_array_to_file('hook_array', $this->hook, $file);
        $GLOBALS['logic_hook']->getHooks($module, true); // manually refresh
        $GLOBALS['logic_hook']->call_custom_logic($module, 'test_logic_hook');
        $this->assertTrue(LogicHookTest::$called);
    }

    /**
     * @dataProvider getModules
     */
    public function testHooksUtils($module)
    {
        $dir = rtrim("custom/modules/$module", "/");
        $file = "$dir/logic_hooks.php";
        if(!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $this->saveHook($file);
        check_logic_hook_file($module, 'test_logic_hook', $this->hook['test_logic_hook'][0]);
        $GLOBALS['logic_hook']->getHooks($module, true); // manually refresh
        $GLOBALS['logic_hook']->call_custom_logic($module, 'test_logic_hook');
        $this->assertTrue(LogicHookTest::$called);
    }


    /**
     * @dataProvider getModules
     */
    public function testGeHookArray($module)
    {
        $dir = rtrim("custom/modules/$module", "/");
        $file = "$dir/logic_hooks.php";
        if(!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $this->saveHook($file);
        check_logic_hook_file($module, 'test_logic_hook', $this->hook['test_logic_hook'][0]);
        $array = get_hook_array($module);
        $this->assertEquals($this->hook, $array);
    }
}

class LogicHookTest {
    public static $called = false;
    function testLogicHook() {
        self::$called = true;
    }
}
