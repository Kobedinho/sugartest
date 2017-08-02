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


require_once 'modules/UpgradeWizard/uw_utils.php';

/**
 * Bug #52396
 * Can't convert ent to ult with oracle 10 via wizard.
 *
 * @author mgusev@sugarcrm.com
 * @ticked 52396
 */
class Bug52396Test extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var OracleManager
     */
    protected $original_db;

    public function setUp()
    {
        if ($GLOBALS['db']->dbType != 'oci8')
        {
            $this->markTestSkipped('Oracle is required');
            return false;
        }

        parent::setUp();

        $this->original_db = $GLOBALS['db'];
        $GLOBALS['db'] = new Bug52396OracleManager();
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'UpgradeWizard');
    }

    public function tearDown()
    {
        if ($GLOBALS['db'] instanceof Bug52396OracleManager)
        {
            unset($GLOBALS['db']);
            $GLOBALS['db'] = $this->original_db;
            unset($GLOBALS['mod_strings']);
        }
    }

    public function provideVersions()
    {
        return array(
            array('8.0.0', false),
            array('8.0.9', false),
            array('8.0.10', false),
            array('8.0.11', false),
            array('9.0.0', true),
            array('9.0.9', true),
            array('9.0.10', true),
            array('9.0.11', true),
            array('10.0.0', true),
            array('10.0.9', true),
            array('10.0.10', true),
            array('10.0.11', true),
            array('11.0.0', true),
            array('11.0.9', true),
            array('11.0.10', true),
            array('11.0.11', true),
            array('12.0.0', true),
            array('12.0.9', true),
            array('12.0.10', true),
            array('12.0.11', true)
        );
    }

    /**
     * Test tries to assert valid and invalid versions of Oracle
     *
     * @dataProvider provideVersions
     * @group 52396
     * @return void
     */
    public function testChangingOfRelation($version, $isValid)
    {
        $GLOBALS['db']->version = $version;
        $result = checkSystemCompliance();
        if ($isValid == true)
        {
            $this->assertArrayNotHasKey('dbVersion', $result, 'Version of oracle is valid but not passed');
        }
        else
        {
            $this->assertArrayHasKey('dbVersion', $result, 'Version of oracle is not valid but passed');
        }
    }
}

/**
 * Mock OracleManager to return required version for test
 */
class Bug52396OracleManager extends OracleManager
{
    /**
     * @var string
     */
    public $version = '';

    /**
     * Return faked version of Oracle
     * @return string
     */
    public function version()
    {
        return $this->version;
    }
}
