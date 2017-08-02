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


class Bug46122Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $hasCustomModulesLogicHookFile = false;
    var $hasCustomContactLogicHookFile = false;
    var $modulesHookFile = 'custom/modules/logic_hooks.php';
    var $contactsHookFile = 'custom/modules/Contacts/logic_hooks.php';

    public function setUp()
    {
        //Setup mock logic hook files
        if(file_exists($this->modulesHookFile))
        {
            $this->hasCustomModulesLogicHookFile = true;
            copy($this->modulesHookFile, $this->modulesHookFile.'.bak');
        } else {
            write_array_to_file("test", array(), $this->modulesHookFile);
        }

        if(file_exists($this->contactsHookFile))
        {
            $this->hasCustomContactLogicHookFile = true;
            copy($this->contactsHookFile, $this->contactsHookFile.'.bak');
        } else {
            write_array_to_file("test", array(), $this->contactsHookFile);
        }

        LogicHook::refreshHooks();
    }

    public function tearDown()
    {
        //Remove the custom logic hook files
        if($this->hasCustomModulesLogicHookFile && file_exists($this->modulesHookFile.'.bak'))
        {
            copy($this->modulesHookFile.'.bak', $this->modulesHookFile);
            unlink($this->modulesHookFile.'.bak');
        } else if(file_exists($this->modulesHookFile)) {
            SugarAutoLoader::unlink($this->modulesHookFile);
        }

        if($this->hasCustomContactLogicHookFile && file_exists($this->contactsHookFile.'.bak'))
        {
            copy($this->contactsHookFile.'.bak', $this->contactsHookFile);
            unlink($this->contactsHookFile.'.bak');
        } else if(file_exists($this->contactsHookFile)) {
            SugarAutoLoader::unlink($this->contactsHookFile);
        }
        unset($GLOBALS['logic_hook']);
    }

    public function testSugarViewProcessLogicHookWithModule()
    {
        $GLOBALS['logic_hook'] = new LogicHookMock();
        $hooks = $GLOBALS['logic_hook']->getHooks('Contacts');
        $sugarViewMock = $this->getSugarViewMock();
        $sugarViewMock->module = 'Contacts';
        $sugarViewMock->process();
        $expectedHookCount = isset($hooks['after_ui_frame']) ? count($hooks['after_ui_frame']) : 0;
        $this->assertEquals($expectedHookCount, $GLOBALS['logic_hook']->hookRunCount, 'Assert that two logic hook files were run');
    }


    public function testSugarViewProcessLogicHookWithoutModule()
    {
        $GLOBALS['logic_hook'] = new LogicHookMock();
        $hooks = $GLOBALS['logic_hook']->getHooks('');
        $sugarViewMock = $this->getSugarViewMock();
        $sugarViewMock->module = '';
        $sugarViewMock->process();
        $expectedHookCount = isset($hooks['after_ui_frame']) ? count($hooks['after_ui_frame']) : 0;
        $this->assertEquals($expectedHookCount, $GLOBALS['logic_hook']->hookRunCount, 'Assert that one logic hook file was run');
    }
    
    protected function getSugarViewMock()
    {
        $mock = $this->getMockBuilder('SugarView')
            ->setMethods(array('_trackView', 'renderJavascript', '_buildModuleList', 'preDisplay', 'displayErrors', 'display'))
            ->getMock();
        $mock->options = array();
        return $mock;
    }
}

class LogicHookMock extends LogicHook
{
    var $hookRunCount = 0;

    function process_hooks($hook_array, $event, $arguments)
    {
        if(!empty($hook_array[$event])){
            if($event == 'after_ui_frame')
            {
                $this->hookRunCount++;
            }
        }
    }
}

