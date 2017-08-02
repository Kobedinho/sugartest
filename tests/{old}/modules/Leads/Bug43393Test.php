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



class Bug43393Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $mod_strings;
        $mod_strings = return_module_language($GLOBALS['current_language'], 'Leads');

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
    }
    
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['app_list_strings']);
    }

    /**
     * @group bug43393
     */
    public function testStudioModuleAddNoLeadsOrUsers()
    {
        // set the request/post parameters
        $_REQUEST['module'] = 'Leads';
        $_REQUEST['action'] = 'Editconvert';

        // call display to generate output
        $vc = new ViewEditConvert();
        $vc->display();

        // we don't want leads or users to show up in the list
        $this->expectOutputNotRegex('/.*(Leads|Users)'.preg_quote('<\/option>','/').'.*/');

        // cleanup
        unset($_REQUEST['module']);
        unset($_REQUEST['action']);
    }

}
