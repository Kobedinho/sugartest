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
 * RS-167: Prepare Audit Api
 */
class RS167Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var Account */
    protected $account = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->account = SugarTestAccountUtilities::createAccount();
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts behavior of viewChangeLog method
     */
    public function testViewChangeLog()
    {
        $this->account->retrieve($this->account->id);
        $this->account->name = 'Test 1';
        $this->account->save();
        $this->account->name = 'Test 2';
        $this->account->save();

        $api = new AuditApi();
        $data = $api->viewChangeLog(SugarTestRestUtilities::getRestServiceMock(), array(
                'module' => 'Accounts',
                'record' => $this->account->id,
            ));
        $this->assertArrayHasKey('records', $data);
        $this->assertEquals(2, count($data['records']));
    }
}
