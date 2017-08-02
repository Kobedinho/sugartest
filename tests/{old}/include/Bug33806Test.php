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

require_once('include/utils.php');


/**
 * @ticket 33806
 */
class Bug33806Test extends Sugar_PHPUnit_Framework_TestCase
{

    function _moduleNameProvider()
    {
        return array(
            array( 'singular' => 'Account', 'module' => 'Accounts'),
            array( 'singular' => 'Contact', 'module' => 'Contacts'),
        );
    }

    /**
     * Test the getMime function for the use case where the mime type is already provided.
     *
     * @dataProvider _moduleNameProvider
     */
    public function testGetModuleFromSingular($singular, $expectedName)
    {
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        $module = get_module_from_singular($singular);

        $this->assertEquals($expectedName, $module);
    }

    function _moduleNameProvider2()
    {
        return array(
            array( 'renamed' => 'Acct', 'module' => 'Accounts'),
        );
    }

    /**
     * Test the getMime function for the use case where the mime type is already provided.
     *
     * @dataProvider _moduleNameProvider2
     */
    public function testGetModuleFromRenamed($renamed, $expectedName)
    {
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        // manually rename the module name to 'Acct'
        $GLOBALS['app_list_strings']['moduleList']['Accounts'] = 'Acct';
        
        $module = get_module_from_singular($renamed);

        $this->assertEquals($expectedName, $module);
    }
}
