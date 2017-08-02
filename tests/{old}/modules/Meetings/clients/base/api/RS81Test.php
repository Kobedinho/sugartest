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
 * RS-81
 * Prepare Meetings Api
 * Test asserts only success of result, not result data.
 */
class RS81Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var MeetingsApi */
    protected $api = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->service = SugarTestRestUtilities::getRestServiceMock();

        $this->api = new MeetingsApi();
        SugarTestMeetingUtilities::createMeeting();
    }

    public function tearDown()
    {
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts behavior of getAgenda method
     */
    public function testGetAgenda()
    {
        $actual = $this->api->getAgenda($this->service, array());
        $this->assertArrayHasKey('today', $actual);
        $this->assertArrayHasKey('tomorrow', $actual);
        $this->assertArrayHasKey('upcoming', $actual);
    }
}
