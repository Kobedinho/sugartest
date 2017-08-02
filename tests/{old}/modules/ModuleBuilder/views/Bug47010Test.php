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


class Bug47010Test extends Sugar_PHPUnit_Framework_TestCase {

    public function setUp() {
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $_SESSION['authenticated_user_language'] = 'en_us';

        $_REQUEST['dropdown_name'] = 'testDD';
        $_REQUEST['dropdown_lang'] = 'en_us';
    }

    public function tearDown() {
        unset($_REQUEST['dropdown_name']);
        unset($_REQUEST['dropdown_lang']);
        unset($_SESSION['authenticated_user_language']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    public function testModuleNameMissingDoesNotThrowExceptionWhenGenereatingSmarty() {

        $view = new ViewDropdown();
        $smarty = $view->generateSmarty($_REQUEST);
        $this->assertEmpty($smarty->get_template_vars('module_name'));
    }
}
