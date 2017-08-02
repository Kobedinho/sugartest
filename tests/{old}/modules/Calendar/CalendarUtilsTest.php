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

require_once('modules/Meetings/Meeting.php');

class CalendarUtilsTest extends Sugar_PHPUnit_Framework_TestCase {

   	/**
	 * @var TimeDate
	 */
	protected $time_date;

	protected $meeting_id = null;

	public function setUp()
	{
		global $current_user;
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
        $this->time_date = new TimeDate();

		$meeting = BeanFactory::newBean('Meetings');
		$meeting->name = 'Test Meeting';
		$meeting->assigned_user_id = $current_user->id;
		$meeting->save();
		$this->meeting = $meeting;

		$contact = BeanFactory::newBean('Contacts');
		$contact->first_name = 'MeetingTest';
		$contact->last_name = 'Contact';
		$contact->save();
		$this->contact = $contact;

		$lead = BeanFactory::newBean('Leads');
		$lead->first_name = 'MeetingTest';
		$lead->last_name = 'Lead';
		$lead->account_name = 'MeetingTest Lead Account';
		$lead->save();
		$this->lead = $lead;
	}

	public function tearDown(){

		unset($GLOBALS['current_user']);
		unset($GLOBALS['mod_strings']);

		$GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$this->meeting->id}'");
		unset($this->meeting);

		$GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->contact->id}'");
		unset($this->contact);

		$GLOBALS['db']->query("DELETE FROM leads WHERE id = '{$this->lead->id}'");
		unset($this->lead);

        SugarTestHelper::tearDown();
	}

	/**
	 *
	 */
	public function testGetInvites()
	{
		global $current_user;
        global $locale;
        /** @var Meeting $meeting */
		$meeting = \BeanFactory::newBean('Meetings');
		$meeting->id = create_guid();
		$meeting->new_with_id = false;
		$meeting->processed = true;
		$meeting->email_reminder_time = "20";
		$meeting->name = 'Test Email Reminder';
		$meeting->assigned_user_id = $current_user->id;
		$meeting->status = "Planned";
		$meeting->date_start = '2015-11-18 18:00:00';
		$meeting->save();

		$relate_values = array('contact_id'=>$this->contact->id,'meeting_id'=> $meeting->id);
		$data_values = array('accept_status'=> 'none');
		$meeting->set_relationship($meeting->rel_contacts_table, $relate_values, true, true, $data_values);

		$invitesBefore = CalendarUtils::getInvitees($meeting);

		$relate_values = array('lead_id'=>$this->lead->id,'meeting_id'=> $meeting->id);
		$data_values = array('accept_status'=> 'accept');
		$meeting->set_relationship($meeting->rel_leads_table, $relate_values, true, true, $data_values);

		$relate_values = array('user_id'=>$current_user->id,'meeting_id'=> $meeting->id);
		$data_values = array('accept_status'=> 'accept');
		$meeting->set_relationship($meeting->rel_users_table, $relate_values, true, true, $data_values);

		$invitesAfter = CalendarUtils::getInvitees($meeting);

        $invitesBeforeExpected = array (
            array(
                'Contacts',
                $this->contact->id,
                '',
                'none',
                $locale->formatName($this->contact)
            ),
            array(
                'Users',
                $meeting->assigned_user_id,
                '',
                'none',
                $locale->formatName($current_user),
            ),
        );

        $invitesAfterExpected = array (
            array(
                'Contacts',
                $this->contact->id,
                '',
                'none',
                $locale->formatName($this->contact)
            ),
            array(
                'Leads',
                $this->lead->id,
                '',
                'accept',
                $locale->formatName($this->lead)
            ),
            array(
                'Users',
                $current_user->id,
                '',
                'accept',
                $locale->formatName($current_user)
            ),
        );

		$this->assertEquals($invitesBeforeExpected, $invitesBefore);
		$this->assertEquals($invitesAfterExpected, $invitesAfter);
	}

    /**
     * @covers CalendarUtils::saveRecurring
     */
    public function testSaveRecurringUsesRelationshipFramework()
    {
        global $current_user;

        $meeting = $this->getMockBuilder('Meeting')->getMock();
        $meeting->id = Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
        $meeting->date_start = '2030-08-15 13:00:00';
        $meeting->date_end = '2030-08-15 18:15:00';
        $meeting->name = "Test Meeting";
        $meeting->duration_hours = '1';
        $meeting->duration_minutes = '30';
        $meeting->repeat_type = 'Daily';
        $meeting->repeat_interval = 1;
        $meeting->repeat_count = 2;
        $meeting->repeat_until = null;
        $meeting->repeat_dow = null;
        $meeting->assigned_user_id = $current_user->id;
        $recurrencesTimeArray = array('2030-08-16 13:00');

        $attendees = array(new SugarBean(), new SugarBean());

        $contactsLinkMock = $this->getMockBuilder('Link2')->disableOriginalConstructor()->getMock();
        $contactsLinkMock->expects($this->once())->method('add');
        $meeting->contacts = $contactsLinkMock;

        $leadsLinkMock = $this->getMockBuilder('Link2')->disableOriginalConstructor()->getMock();
        $leadsLinkMock->expects($this->once())->method('add');
        $meeting->leads = $leadsLinkMock;

        $usersLinkMock = $this->getMockBuilder('Link2')->disableOriginalConstructor()->getMock();
        $usersLinkMock->expects($this->exactly(count($attendees)))->method('add');
        $meeting->users = $usersLinkMock;

        $tagsLinkMock = $this->getMockBuilder('Link2')->disableOriginalConstructor()->getMock();
        $meeting->tag_link = $tagsLinkMock;

        $meeting->expects($this->any())
            ->method('get_linked_beans')
            ->will($this->returnValue($attendees));

        $meeting->expects($this->any())
            ->method('load_relationship')
            ->will($this->returnValueMap(array(
                array('contacts', true),
                array('leads', true),
                array('users', true),
            )));

        CalendarUtils::saveRecurring($meeting, $recurrencesTimeArray);
    }
}
