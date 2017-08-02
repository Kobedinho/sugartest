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
 * @ticket 43554
 */
class Bug43554Test extends Sugar_PHPUnit_Framework_TestCase
{

	static $ie = null;
    static $_user = null;

	static public function setUpBeforeClass()
    {
        self::$_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = self::$_user;

		self::$ie = new InboundEmail();
	}

    static public function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function getUrls()
    {
        return array(
            array("http://localhost:8888/sugarent/index.php?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1"),
            array("http://localhost:8888/index.php?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1"),
            array(to_html("http://localhost:8888/index.php?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1")),
            array("/index.php?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1"),
            array("index.php?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1"),
            array("/?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1"),
            array("https://localhost/?composeLayoutId=composeLayout1&fromAccount=1&module=Emails&action=EmailUIAjax&emailUIAction=sendEmail&setEditor=1"),
            );
    }

    /**
     * @dataProvider getUrls
     * @param string $url
     */
	function testEmailCleanup($url)
	{
        $data = "Test: <img src=\"$url\">";
        $res = str_replace("<img />", "", SugarCleaner::cleanHtml($data));
        $this->assertNotContains("<img", $res);
	}
}
