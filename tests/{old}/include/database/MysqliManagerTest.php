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

require_once 'include/database/MysqlManagerTest.php';

class MysqliManagerTest extends MysqlManagerTest
{
    protected $configOptions = array();

    protected function setUp()
    {
        parent::setUp();

        $this->db = new MysqliManager();

        $this->configOptions = array(
            'db_host_name' => $GLOBALS['db']->connectOptions['db_host_name'],
            'db_host_instance' => $GLOBALS['db']->connectOptions['db_host_instance'],
            'db_user_name' => $GLOBALS['db']->connectOptions['db_user_name'],
            'db_password' => $GLOBALS['db']->connectOptions['db_password'],
        );
        
        $this->db->setOptions(array());
    }

    protected function tearDown()
    {
        if ($this->db) {
            $this->db->disconnect();
        }

        parent::tearDown();
    }

    /**
     * Test if proper client flags are set when 'ssl'=>true is provided
     */
    public function testSetupSSLSimple()
    {
        $this->db->setOptions(array(
            'ssl' => true
        ));

        $this->db->connect($this->configOptions, false);
        $dbInstanceOptions = SugarTestReflection::getProtectedValue($this->db, 'connectOptions');

        $this->assertEquals($dbInstanceOptions['db_client_flags'], MYSQLI_CLIENT_SSL);
    }

    /**
     * Test if proper client flags are set when 'ssl_options' are provided
     */
    public function testSetupSSLAdvanced()
    {
        $this->db->setOptions(array(
            'ssl' => true,
            'ssl_options' => array(
                'ssl_ca' => 'test'
            )
        ));

        $this->db->connect($this->configOptions, false);
        $dbInstanceOptions = SugarTestReflection::getProtectedValue($this->db, 'connectOptions');

        $this->assertEquals($dbInstanceOptions['db_client_flags'], 0);
    }

    /**
     * This is the data provider for testSupports
     */
    public function supportsProvider()
    {
        return array(
            array('recursive_query', true),
            array('fix:report_as_condition', true),
        );
    }

    /**
     * This is a test for known supported features
     * @dataProvider supportsProvider
     */
    public function testSupports($feature, $expectedSupport)
    {
        $this->assertEquals($expectedSupport, $this->db->supports($feature));
    }
}
