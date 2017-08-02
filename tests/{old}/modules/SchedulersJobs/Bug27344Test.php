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


class Bug27344Test extends Sugar_PHPUnit_Framework_TestCase
{
	private $_url;
    private $_initial_server_port;
    private $_has_initial_server_port;
    private $_cron_test_file = 'cronUnitTestBug27344.php';

    public function setUp()
    {

        $this->_has_initial_server_port = isset($_SERVER['SERVER_PORT']);
        if ($this->_has_initial_server_port) {
            $this->_initial_server_port = $_SERVER['SERVER_PORT'];
        }
    }

    public function tearDown()
    {
        if ($this->_has_initial_server_port) {
            $_SERVER['SERVER_PORT'] = $this->_initial_server_port;
        } else {
            unset($_SERVER['SERVER_PORT']);
        }
    }

    public function testLocalServerPortNotUsed()
    {

        $url = $GLOBALS['sugar_config']['site_url'] . '/maintenance.php';

        $_SERVER['SERVER_PORT'] = '9090';
        $sJob = new SchedulersJob(FALSE);
        $this->assertTrue($sJob->fireUrl($url));
    }

}
