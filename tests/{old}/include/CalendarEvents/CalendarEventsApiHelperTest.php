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


class CalendarEventsApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api;

    public function happyPathProvider()
    {
        return array(
            array(1, 1),
            array('1', '1'),
            array(0, 0),
            array('0', '0'),
        );
    }

    public function throwsMissingParameterExceptionProvider()
    {
        $now = $GLOBALS['timedate']->nowDb();
        return array(
            array(null, 1, 1),
            array($now, null, 1),
            array($now, 1, null),
            array($now, '', 1),
            array($now, 1, ''),
        );
    }

    public function throwsInvalidParameterExceptionProvider()
    {
        return array(
            array('a', 1),
            array(1, 'a'),
            array(-1, 1),
            array('-1', '1'),
            array(1, -1),
            array('1', '-1'),
            array(1.5, 1),
            array('1.5', '1'),
            array(1, 1.5),
            array('1', '1.5'),
        );
    }

    public function setUp()
    {
        parent::setUp();
        $this->api = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @group bug
     * @group SI49195
     */
    public function testPopulateFromApi_ShouldNotUpdateVcal()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->date_start = $GLOBALS['timedate']->nowDb();
        $meeting->duration_hours = 1;
        $meeting->duration_minutes = '0';
        $meeting->assigned_user_id = 1;

        $helper = $this->getMockBuilder('CalendarEventsApiHelper')
            ->setMethods(['getInvitees'])
            ->setConstructorArgs([$this->api])
            ->getMock();
        $helper->expects($this->any())->method('getInvitees')->will($this->returnValue(array()));

        $helper->populateFromApi($meeting, array());
        $this->assertFalse($meeting->update_vcal, 'Should have been set to false');
    }

    public function testPopulateFromApi_TheExistingInviteesAreAddedToTheBean()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->date_start = $GLOBALS['timedate']->nowDb();
        $meeting->duration_hours = 1;
        $meeting->duration_minutes = '0';
        $meeting->assigned_user_id = $GLOBALS['current_user']->id;

        $users = array_map('create_guid', array_fill(0, 5, null));
        $leads = array_map('create_guid', array_fill(0, 5, null));
        $contacts = array_map('create_guid', array_fill(0, 5, null));

        $map = array(
            array($meeting, 'users', array(), $users),
            array($meeting, 'leads', array(), $leads),
            array($meeting, 'contacts', array(), $contacts),
        );
        $helper = $this->getMockBuilder('CalendarEventsApiHelper')
            ->setMethods(['getInvitees'])
            ->setConstructorArgs([$this->api])
            ->getMock();
        $helper->expects($this->any())->method('getInvitees')->will($this->returnValueMap($map));

        $helper->populateFromApi($meeting, array());
        $this->assertCount(
            count($users),
            $meeting->users_arr,
            'Should have the number of generated users'
        );
        $this->assertCount(count($leads), $meeting->leads_arr, 'Should have the number of generated leads');
        $this->assertCount(count($contacts), $meeting->contacts_arr, 'Should have the number of generated contacts');
    }

    /**
     * @dataProvider happyPathProvider
     */
    public function testPopulateFromApi_ReturnsTrue($hours, $minutes)
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->date_start = $GLOBALS['timedate']->nowDb();
        $meeting->duration_hours = $hours;
        $meeting->duration_minutes = $minutes;

        $helper = new CalendarEventsApiHelper($this->api);
        $actual = $helper->populateFromApi($meeting, array());
        $this->assertTrue($actual, 'The happy path should have returned true');
    }

    /**
     * @dataProvider throwsMissingParameterExceptionProvider
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testPopulateFromApi_ThrowsMissingParameterException($starts, $hours, $minutes)
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->date_start = $starts;
        $meeting->duration_hours = $hours;
        $meeting->duration_minutes = $minutes;

        $helper = new CalendarEventsApiHelper($this->api);
        $helper->populateFromApi($meeting, array());
    }

    /**
     * @dataProvider throwsInvalidParameterExceptionProvider
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testPopulateFromApi_ThrowsInvalidParameterException($hours, $minutes)
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->date_start = $GLOBALS['timedate']->nowDb();
        $meeting->duration_hours = $hours;
        $meeting->duration_minutes = $minutes;

        $helper = new CalendarEventsApiHelper($this->api);
        $helper->populateFromApi($meeting, array());
    }

    public function testFormatForApi_MeetingIsRelatedToAContact_TheNameOfTheContactIsAddedToTheResponse()
    {
        $meeting = $this->getMockBuilder('Meeting')->setMethods(array('ACLAccess'))->getMock();
        $meeting->expects($this->any())->method('ACLAccess')->will($this->returnValue(true));
        BeanFactory::setBeanClass('Meetings', get_class($meeting));
        $meeting->id = create_guid();
        BeanFactory::registerBean($meeting);

        $contact = SugarTestContactUtilities::createContact();
        $meeting->contact_id = $contact->id;

        $helper = new CalendarEventsApiHelper($this->api);
        $data = $helper->formatForApi($meeting);
        $this->assertEquals($data['contact_name'], $contact->full_name, "The contact's name does not match");

        BeanFactory::unregisterBean($meeting);
        BeanFactory::setBeanClass('Meetings');
    }

    public function testGetInvitees_ReturnsCorrectDataForLink()
    {
        $meeting = $this->createPartialMock('Meeting', array('load_relationship'));
        $meeting->expects($this->any())->method('load_relationship')
            ->will($this->returnValue(false));

        BeanFactory::setBeanClass('Meetings', get_class($meeting));

        $meeting->id = create_guid();
        BeanFactory::registerBean($meeting);

        $contactsId1 = create_guid();
        $contactsId2 = create_guid();
        $leadsId1    = create_guid();
        $usersId1    = create_guid();

        $submittedData = array(
            'contacts' => array(
                'add'    => array(
                    $contactsId1,
                    array(
                        'id' => $contactsId2,
                    )
                ),
            ),
            'leads'    => array(
                'add'    => array(
                    $leadsId1,
                ),
                'delete' => array()
            ),
            'users'    => array(
                'delete' => array(
                    $usersId1,
                )
            )
        );
        $helper = new CalendarEventsApiHelperMock($this->api);

        $invitees = $helper->getInvitees($meeting, 'contacts', $submittedData);
        $this->assertCount(2, $invitees, 'Should include two contacts in the list');
        $this->assertContains($contactsId1, $invitees);
        $this->assertContains($contactsId2, $invitees);

        $invitees = $helper->getInvitees($meeting, 'leads', $submittedData);
        $this->assertCount(1, $invitees, 'Should include both the assigned user and current user');
        $this->assertContains($leadsId1, $invitees);

        $invitees = $helper->getInvitees($meeting, 'users', $submittedData);
        $this->assertEmpty(0, $invitees, 'Should include both the assigned user and current user');

        BeanFactory::unregisterBean($meeting);
        BeanFactory::setBeanClass('Meetings');
    }
}
/*
 * Mock class to test protected methods
 */
class CalendarEventsApiHelperMock extends CalendarEventsApiHelper
{
    public function getInvitees(SugarBean $bean, $link, $submittedData)
    {
        return parent::getInvitees($bean, $link, $submittedData);
    }
}
