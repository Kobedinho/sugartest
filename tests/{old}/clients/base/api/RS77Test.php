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
 * RS-77: Prepare Subscriptions Api
 */
class RS77Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SubscriptionsApi
     */
    protected $subscriptionsApi;

    /**
     * @var RestService
     */
    protected $serviceMock;

    /**
     * Subscription
     *
     * @var string
     */
    protected $subscription;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->subscriptionsApi = new SubscriptionsApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        if($this->subscription) {
            $GLOBALS['db']->query("DELETE FROM subscriptions WHERE id = '{$this->subscription}'");
        }
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts behavior of subscribeToRecord
     */
    public function testSubscribeToRecord()
    {
        $account = SugarTestAccountUtilities::createAccount();

        $result = $this->subscriptionsApi->subscribeToRecord($this->serviceMock, array(
            'module' => 'Accounts',
            'record' => $account->id,
        ));

        $this->assertNotEmpty($result);

        $subscription = BeanFactory::newBean('Subscriptions');
        $subscription->retrieve($result);

        $this->assertEquals($result, $subscription->id);
        $this->assertEquals('Accounts', $subscription->parent_type);
        $this->assertEquals($account->id, $subscription->parent_id);

        // check subscribe for already subscribed record
        $result = $this->subscriptionsApi->subscribeToRecord($this->serviceMock, array(
            'module' => 'Accounts',
            'record' => $account->id,
        ));

        $this->assertFalse($result);
    }

    /**
     * Test asserts behavior of unsubscribeFromRecord
     */
    public function testUnsubscribeFromRecord()
    {
        $account = SugarTestAccountUtilities::createAccount();

        $result = $this->subscriptionsApi->unsubscribeFromRecord($this->serviceMock, array(
            'module' => 'Accounts',
            'record' => $account->id,
        ));
        $this->assertFalse($result);

        $this->subscription = Subscription::subscribeUserToRecord($this->serviceMock->user, $account);

        $result = $this->subscriptionsApi->unsubscribeFromRecord($this->serviceMock, array(
            'module' => 'Accounts',
            'record' => $account->id,
        ));

        $this->assertTrue($result);
    }
}
