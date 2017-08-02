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

class Bugs39819_39820Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @ticket 39819
     * @ticket 39820
     */
    public function setUp()
    {
        SugarAutoLoader::ensureDir("custom/modules/Accounts/language"); // Creating nested directories at a glance
        SugarTestHelper::setUp('mod_strings', array('Administration'));
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testLoadEnHelp()
    {
        // en_us help on a standard module.
        SugarAutoLoader::put("modules/Accounts/language/en_us.help.DetailView.html", "<h1>ENBugs39819-39820</h1>");

        $_SERVER['HTTP_HOST'] = "";
        $_SERVER['SCRIPT_NAME'] = "";
        $_SERVER['QUERY_STRING'] = "";

        $_REQUEST['view'] = 'documentation';
        $_REQUEST['lang'] = 'en_us';
        $_REQUEST['help_module'] = 'Accounts';
        $_REQUEST['help_action'] = 'DetailView';

        ob_start();
        require "modules/Administration/SupportPortal.php";

        $tStr = ob_get_contents();
        ob_end_clean();

        SugarAutoLoader::unlink("modules/Accounts/language/en_us.help.DetailView.html");

        // I expect to get the en_us normal help file....
        $this->assertRegExp("/.*ENBugs39819\-39820.*/", $tStr);
    }

    public function testLoadCustomItHelp()
    {
        // Custom help (NOT en_us) on a standard module.
        SugarAutoLoader::put("custom/modules/Accounts/language/it_it.help.DetailView.html", "<h1>Bugs39819-39820</h1>");

        $_SERVER['HTTP_HOST'] = "localhost";
        $_SERVER['SCRIPT_NAME'] = "/index.php";
        $_SERVER['QUERY_STRING'] = "module=Administration&action=index";

        $_REQUEST['view'] = 'documentation';
        $_REQUEST['lang'] = 'it_it';
        $_REQUEST['help_module'] = 'Accounts';
        $_REQUEST['help_action'] = 'DetailView';

        ob_start();
        require "modules/Administration/SupportPortal.php";

        $tStr = ob_get_contents();
        ob_end_clean();

        SugarAutoLoader::unlink("custom/modules/Accounts/language/it_it.help.DetailView.html");

        // I expect to get the it_it custom help....
        $this->assertRegExp("/.*Bugs39819\-39820.*/", $tStr);

        // check for encoded URL in mailto body BR-3545. Change done in SupportPortal.tpl
        $this->assertRegExp("/body=http%3A%2F%2Flocalhost%2Findex\.php%3Fmodule%3DAdministration%26action%3Dindex/", $tStr);
    }
}
