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
 *  Prepare MassUpdate Api.
 */
class RS189Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarApi
     */
    protected $api;

    /**
     * @var RestService
     */
    protected static $rest;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
        self::$rest = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->api = new MassUpdateApi();
    }

    /**
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testDeleteException()
    {
        $this->api->massDelete(self::$rest, array());
    }

    /**
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testEmptyDelete()
    {
        $this->api->massDelete(
            self::$rest,
            array('massupdate_params' => array(), 'module' => 'Accounts')
        );
    }

    public function testDelete()
    {
        $id = create_guid();
        $account = SugarTestAccountUtilities::createAccount($id);
        $result = $this->api->massDelete(
            self::$rest,
            array(
                'massupdate_params' => array('uid' => array($id)),
                'module' => 'Accounts'
            )
        );
        $this->assertEquals('done', $result['status']);
        $account = BeanFactory::newBean('Accounts');
        $account->retrieve($id, true, false);
        $this->assertEquals(1, $account->deleted);
    }

    public function testMassUpdate()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $result = $this->api->massUpdate(
            self::$rest,
            array(
                'massupdate_params' => array('uid' => array($account->id), 'name' => 'RS189Test'),
                'module' => 'Accounts'
            )
        );
        $this->assertEquals('done', $result['status']);
        $account = BeanFactory::getBean('Accounts', $account->id);
        $this->assertEquals('RS189Test', $account->name);
    }
}
