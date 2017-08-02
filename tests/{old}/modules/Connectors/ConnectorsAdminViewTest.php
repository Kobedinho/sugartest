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
 * ConnectorsAdminViewTest
 *
 * @author Collin Lee
 */
class ConnectorsAdminViewTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        global $mod_strings, $app_strings, $theme;
        $theme = SugarTestThemeUtilities::createAnonymousTheme();
        $mod_strings = return_module_language($GLOBALS['current_language'], 'Connectors');
        $app_strings = return_application_language($GLOBALS['current_language']);
    }

    public static function tearDownAfterClass()
    {
        global $mod_strings, $app_strings, $theme;
        SugarTestThemeUtilities::removeAllCreatedAnonymousThemes();
        unset($theme);
        unset($mod_strings);
        unset($app_strings);
    }

    protected function withTwitter($output)
    {
        $this->assertRegExp('/ext_rest_twitter/', $output);
    }

    protected function withoutTwitter($output)
    {
        $this->assertNotRegExp('/ext_rest_twitter/', $output);
    }

    public function testMapConnectorFields()
    {
        $view = new ViewModifyMapping(null, null);
        $view->ss = new Sugar_Smarty();
        $view->display();
        $this->setOutputCallback(array($this, 'withTwitter'));
    }

    public function testEnableConnectors()
    {
        $view = new ViewModifyDisplay(null, null);
        $view->ss = new Sugar_Smarty();
        $view->display();
        $this->setOutputCallback(array($this, 'withTwitter'));
    }

    public function testConnectorProperties()
    {
        $view = new ViewModifyProperties(null, null);
        $view->ss = new Sugar_Smarty();
        $view->display();
        $this->setOutputCallback(array($this, 'withTwitter'));
    }

    public function testConnectorSearchProperties()
    {
        $view = new ViewModifySearch(null, null);
        $view->ss = new Sugar_Smarty();
        $view->display();
        $this->setOutputCallback(array($this, 'withoutTwitter'));
    }
}
