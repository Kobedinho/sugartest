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
 * @group api
 * @group subscriptions
 */
class SubscriptionsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api;
    private $subscriptionApi;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
        $this->api             = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user       = $GLOBALS['current_user'];
        $this->subscriptionApi = new SubscriptionsApi();
    }

    public function tearDown()
    {
        BeanFactory::setBeanClass('Leads');
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @expectedException     SugarApiExceptionNotFound
     */
    public function testSubscribeToRecord_RecordNotFound_ThrowsException()
    {
        $this->subscriptionApi->subscribeToRecord(
            $this->api,
            array(
                'module' => 'Leads',
                'record' => create_guid(),
            )
        );
    }

    /**
     * @expectedException     SugarApiExceptionNotAuthorized
     */
    public function testSubscribeToRecord_NoAccess_ThrowsException()
    {
        $mockLead = $this->getMockBuilder('Lead')->setMethods(array('ACLAccess'))->getMock();
        $mockLead->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(false));

        BeanFactory::setBeanClass('Leads', get_class($mockLead));

        $mockLead->id = create_guid();
        BeanFactory::registerBean($mockLead);

        $this->subscriptionApi->subscribeToRecord(
            $this->api,
            array(
                'module' => 'Leads',
                'record' => $mockLead->id,
            )
        );

        BeanFactory::unregisterBean($mockLead);
    }

    /**
     * @expectedException     SugarApiExceptionNotFound
     */
    public function testUnSubscribeFromRecord_RecordNotFound_ThrowsException()
    {
        $lead = SugarTestLeadUtilities::createLead();
        $lead->mark_deleted($lead->id);

        $this->subscriptionApi->unsubscribeFromRecord(
            $this->api,
            array(
                'module' => 'Leads',
                'record' => $lead->id,
            )
        );
    }

    /**
     * @expectedException     SugarApiExceptionNotAuthorized
     */
    public function testUnSubscribeFromRecord_NoAccess_ThrowsException()
    {
        $mockLead = $this->getMockBuilder('Lead')->setMethods(array('ACLAccess'))->getMock();
        $mockLead->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(false));

        BeanFactory::setBeanClass('Leads', get_class($mockLead));

        $mockLead->id = create_guid();
        BeanFactory::registerBean($mockLead);

        $this->subscriptionApi->unsubscribeFromRecord(
            $this->api,
            array(
                'module' => 'Leads',
                'record' => $mockLead->id,
            )
        );

        BeanFactory::unregisterBean($mockLead);
    }
}
