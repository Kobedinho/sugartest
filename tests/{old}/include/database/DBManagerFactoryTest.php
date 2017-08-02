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


class DBManagerFactoryTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_oldSugarConfig;

    public function setUp()
    {
        $this->_oldSugarConfig = $GLOBALS['sugar_config'];
    }

    public function tearDown()
    {
        $GLOBALS['sugar_config'] = $this->_oldSugarConfig;
    }

    public function testGetInstance()
    {
        $db = DBManagerFactory::getInstance();

        $this->assertTrue($db instanceOf DBManager,"Should return a DBManger object");
    }

    public function testGetInstanceCheckMysqlDriverChoosen()
    {
        if ( $GLOBALS['db']->dbType != 'mysql' )
            $this->markTestSkipped('Only applies to MySql');

        $this->assertInstanceOf('MysqlManager', DBManagerFactory::getInstance());
    }

    /**
     * @ticket 27781
     */
    public function testGetInstanceMssqlDefaultSelection()
    {
        if ( $GLOBALS['db']->dbType != 'mssql' )
            $this->markTestSkipped('Only applies to SQL Server');

        $this->assertInstanceOf('MssqlManager', DBManagerFactory::getInstance());
    }
}
