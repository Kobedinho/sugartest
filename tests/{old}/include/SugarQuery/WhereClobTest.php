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

class SugarQuery_WhereClobTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var Account */
    private $account;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        parent::setUp();

        $this->account = SugarTestAccountUtilities::createAccount();
        $this->account->description = 'SUGAR_QUERY_WHERE_CLOB_TEST';
        $this->account->save();
    }

    public function testClob()
    {
        $q = new SugarQuery();
        $q->select('id');
        $q->from($this->account);
        $q->where()->contains('description', 'SUGAR_QUERY_WHERE_CLOB_TEST');
        $data = $q->execute();

        $this->assertCount(1, $data);
        $this->assertEquals($this->account->id, $data[0]['id']);
    }
}
