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

require_once('tests/{old}/SugarTestViewConvertLeadUtilities.php');
require_once 'tests/{old}/SugarTestViewConvertLeadUtilities.php';


class ConvertLeadTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var mixed
     */
    protected $license_expires_in = null;

    public function setUp()
    {
        SugarTestHelper::saveFile('custom/modules/Leads/metadata/editviewdefs.php');
        @SugarAutoLoader::unlink('custom/modules/Leads/metadata/editviewdefs.php');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('Leads'));
        SugarTestHelper::setUp('current_user');
        if (isset($_SESSION['LICENSE_EXPIRES_IN']))
        {
            $this->license_expires_in = $_SESSION['LICENSE_EXPIRES_IN'];
        }
        $_SESSION['LICENSE_EXPIRES_IN'] = '5';

    }

    public function tearDown()
    {
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestStudioUtilities::removeAllCreatedFields();
        if(!empty($this->relation_id)) {
            SugarTestMeetingUtilities::deleteMeetingLeadRelation($this->relation_id);
        }
        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        unset($GLOBALS['app']->controller);
        unset($_REQUEST['module']);
        unset($_REQUEST['action']);
        unset($_REQUEST['record']);
        if(!empty($this->meeting) && !empty($this->contact)) {
            $GLOBALS['db']->query("delete from meetings_contacts where meeting_id='{$this->meeting->id}' and contact_id= '{$this->contact->id}'");
        }
        if(!empty($this->contact)) {
            $GLOBALS['db']->query("delete from meetings where parent_id='{$this->contact->id}' and parent_type= 'Contacts'");
        }
        if(!empty($this->contact_id)) {
            $GLOBALS['db']->query("delete from meetings where parent_id='{$this->contact_id}' and parent_type= 'Contacts'");
        }
        if(!empty($this->lead)) {
            $GLOBALS['db']->query("delete from meetings where parent_id='{$this->lead->id}' and parent_type= 'Leads'");
        }
        if(!empty($this->new_meeting_id) && !empty($this->contact)) {
            $GLOBALS['db']->query("delete from meetings_contacts where meeting_id='{$this->new_meeting_id}' and contact_id= '{$this->contact->id}'");
        }
        if(!empty($this->new_meeting_id) && !empty($this->contact_id)) {
            $GLOBALS['db']->query("delete from meetings_contacts where meeting_id='{$this->new_meeting_id}' and contact_id= '{$this->contact_id}'");
        }

        $_SESSION['LICENSE_EXPIRES_IN'] = $this->license_expires_in;
        SugarTestHelper::tearDown();

    }

    /**
     * @group bug44033
     */
    public function testActivityMove() {
        // init
        $lead = SugarTestLeadUtilities::createLead();
        $this->contact = $contact = SugarTestContactUtilities::createContact();
        $meeting = SugarTestMeetingUtilities::createMeeting();
        SugarTestMeetingUtilities::addMeetingParent($meeting->id, $lead->id);
        $relation_id = SugarTestMeetingUtilities::addMeetingLeadRelation($meeting->id, $lead->id);
        $_REQUEST['record'] = $lead->id;

        // refresh the meeting to include parent_id and parent_type
        $meeting_id = $meeting->id;
        $this->meeting = $meeting = new Meeting();
        $meeting->retrieve($meeting_id);

        // action: move meeting from lead to contact
        $convertObj = new TestViewConvertLead();
        $convertObj->moveActivityWrapper($meeting, $contact);

        // verification 1, parent id should be contact id
        $this->assertTrue($meeting->parent_id == $contact->id, 'Meeting parent id is not converted to contact id.');

        // verification 2, parent type should be "Contacts"
        $this->assertTrue($meeting->parent_type == 'Contacts', 'Meeting parent type is not converted to Contacts.');

        // verification 3, record should be deleted from meetings_leads table
        $sql = "select id from meetings_leads where meeting_id='{$meeting->id}' and lead_id='{$lead->id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse($row, "Meeting-Lead relationship is not removed.");

        // verification 4, record should be added to meetings_contacts table
        $sql = "select id from meetings_contacts where meeting_id='{$meeting->id}' and contact_id='{$contact->id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse(empty($row), "Meeting-Contact relationship is not added.");

        // clean up
    }


    public function testActivityCopyWithParent() {
        // lets the run the activity copy again, only this time we pass in a parent account
        $this->lead = $lead = SugarTestLeadUtilities::createLead();
        $this->contact = $contact = SugarTestContactUtilities::createContact();
        $meeting = SugarTestMeetingUtilities::createMeeting();
        $account = SugarTestAccountUtilities::createAccount();
        SugarTestMeetingUtilities::addMeetingParent($meeting->id, $lead->id);
        $this->relation_id = SugarTestMeetingUtilities::addMeetingLeadRelation($meeting->id, $lead->id);
        $_REQUEST['record'] = $lead->id;

        // refresh the meeting to include parent_id and parent_type
        $meeting_id = $meeting->id;
        $this->meeting = $meeting = new Meeting();
        $meeting->retrieve($meeting_id);

        // action: copy meeting from lead to contact
        $convertObj = new TestViewConvertLead();
        $convertObj->copyActivityWrapper($meeting, $contact, array('id'=>$account->id,'type'=>'Accounts'));


        // 2a a newly created meeting with no parent info passed in, so parent id and type are empty
        //parent type=Contatcs and parent_id=$contact->id
        //$sql = "select id from meetings where parent_id='{$contact->id}' and parent_type= 'Contacts' and deleted=0";
        $sql = "select id, parent_id from meetings where name = '{$meeting->name}'";
        $result = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)){
            //skip if this is the original message
            if($row['id'] == $meeting_id){
                continue;
            }

            $this->assertEquals($row['parent_id'], $account->id, 'parent id of meeting should be equal to passed in account id: '.$account->id);
        }

    }


    public function testActivityCopyWithNoParent() {
        // init
        $this->lead = $lead = SugarTestLeadUtilities::createLead();
        $this->contact = $contact = SugarTestContactUtilities::createContact();
        $meeting = SugarTestMeetingUtilities::createMeeting();
        SugarTestMeetingUtilities::addMeetingParent($meeting->id, $lead->id);
        $this->relation_id = $relation_id = SugarTestMeetingUtilities::addMeetingLeadRelation($meeting->id, $lead->id);
        $_REQUEST['record'] = $lead->id;

        // refresh the meeting to include parent_id and parent_type
        $meeting_id = $meeting->id;
        $this->meeting = $meeting = new Meeting();
        $meeting->retrieve($meeting_id);

        // action: copy meeting from lead to contact
        $convertObj = new TestViewConvertLead();
        $convertObj->copyActivityWrapper($meeting, $contact);

        // 1. the original meeting should still have the same parent_type and parent_id
        $meeting->retrieve($meeting_id);
        $this->assertEquals('Leads', $meeting->parent_type, 'parent_type of the original meeting was changed from Leads to '.$meeting->parent_type);
        $this->assertEquals($lead->id, $meeting->parent_id, 'parent_id of the original meeting was changed from '.$lead->id.' to '.$meeting->parent_id);

        // 2. a newly created meeting with no parent info passed in, so parent id and type are empty
        $new_meeting_id = '';
        $sql = "select id, parent_id from meetings where name = '{$meeting->name}'";
              $result = $GLOBALS['db']->query($sql);
              while ($row = $GLOBALS['db']->fetchByAssoc($result)){
                  //skip if this is the original message
                  if($row['id'] == $meeting_id){
                      continue;
                  }
                  $new_meeting_id = $row['id'];
                  $this->assertEmpty($row['parent_id'],'parent id of meeting should be empty as no parent was sent in ');
              }
              $this->new_meeting_id = $new_meeting_id;


        // 3. record should not be deleted from meetings_leads table
        $sql = "select id from meetings_leads where meeting_id='{$meeting->id}' and lead_id='{$lead->id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertNotNull($row, "Meeting-Lead relationship was removed.");

        // 4. new meeting record should be added to meetings_contacts table
        $sql = "select id from meetings_contacts where meeting_id='{$new_meeting_id}' and contact_id='{$contact->id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse(empty($row), "Meeting-Contact relationship has not been added.");
    }

    /**
     * @outputBuffering enabled
     */
    public function testConversionAndCopyActivities() {
        global $sugar_config;

        // init
        $lead = SugarTestLeadUtilities::createLead();
        $account = SugarTestAccountUtilities::createAccount();
        $meeting = SugarTestMeetingUtilities::createMeeting();
        SugarTestMeetingUtilities::addMeetingParent($meeting->id, $lead->id);
        $this->relation_id = $relation_id = SugarTestMeetingUtilities::addMeetingLeadRelation($meeting->id, $lead->id);
        $_REQUEST['record'] = $lead->id;

        // set the request/post parameters before converting the lead
        $_REQUEST['module'] = 'Leads';
        $_REQUEST['action'] = 'ConvertLead';
        $_REQUEST['record'] = $lead->id;
        $_REQUEST['handle'] = 'save';
        $_REQUEST['selectedAccount'] = $account->id;
        $sugar_config['lead_conv_activity_opt'] = 'copy';
        $_POST['lead_conv_ac_op_sel'] = array('Contacts');

        // call display to trigger conversion
        $vc = new ViewConvertLead();
        $vc->display();

        // refresh meeting
        $meeting_id = $meeting->id;
        $this->meeting = $meeting = new Meeting();
        $meeting->retrieve($meeting_id);

        // refresh lead
        $lead_id = $lead->id;
        $this->lead = $lead = new Lead();
        $lead->retrieve($lead_id);

        // retrieve the new contact id from the conversion
        $this->contact_id = $contact_id = $lead->contact_id;

        // 1. Lead's contact_id should not be null
        $this->assertNotNull($contact_id, 'Lead has null contact id after conversion.');

        // 2. Lead status should be 'Converted'
        $this->assertEquals('Converted', $lead->status, "Lead atatus should be 'Converted'.");

        // 3. parent_type of the original meeting should be Leads
        $this->assertEquals('Leads', $meeting->parent_type, 'Meeting parent should be Leads');

        // 4. parent_id of the original meeting should be contact id
        $this->assertEquals($lead_id, $meeting->parent_id, 'Meeting parent id should be lead id.');

        // 5. record should NOT be deleted from meetings_leads table
        $sql = "select id from meetings_leads where meeting_id='{$meeting->id}' and lead_id='{$lead->id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse(empty($row), "Meeting-Lead relationship is removed.");

        // 6. record should be added to meetings_contacts table
        $sql = "select meeting_id from meetings_contacts where contact_id='{$contact_id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse(empty($row), "Meeting-Contact relationship is not added.");

        // 7. the parent_type of the new meeting should be empty
        $new_meeting_id = $row['meeting_id'];
        $sql = "select id, parent_type, parent_id from meetings where id='{$new_meeting_id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse(empty($row), "New meeting is not added for contact.");
        $this->assertEmpty($row['parent_type'], 'Parent type of the new meeting should be Empty');

        // 8. the parent_id of the new meeting should be contact id
        $this->assertEmpty($row['parent_id'], 'Parent id of the new meeting should be empty.');

        // to suppress output on phpunit (need to be reviewed when proper tests are made)
        $this->expectOutputRegex('/Used an existing contact/');
    }

    /**
     * @outputBuffering enabled
     */
    public function testConversionAndDoNothing() {
        global $sugar_config;

        // init
        $lead = SugarTestLeadUtilities::createLead();
        $account = SugarTestAccountUtilities::createAccount();
        $meeting = SugarTestMeetingUtilities::createMeeting();
        SugarTestMeetingUtilities::addMeetingParent($meeting->id, $lead->id);
        $relation_id = SugarTestMeetingUtilities::addMeetingLeadRelation($meeting->id, $lead->id);
        $_REQUEST['record'] = $lead->id;

        // set the request/post parameters before converting the lead
        $_REQUEST['module'] = 'Leads';
        $_REQUEST['action'] = 'ConvertLead';
        $_REQUEST['record'] = $lead->id;
        $_REQUEST['handle'] = 'save';
        $_REQUEST['selectedAccount'] = $account->id;
        $sugar_config['lead_conv_activity_opt'] = 'none';

        // call display to trigger conversion
        $vc = new ViewConvertLead();
        $vc->display();

        // refresh meeting
        $meeting_id = $meeting->id;
        $this->meeting = $meeting = new Meeting();
        $meeting->retrieve($meeting_id);

        // refresh lead
        $lead_id = $lead->id;
        $this->lead = $lead = new Lead();
        $lead->retrieve($lead_id);

        // retrieve the new contact id from the conversion
        $this->contact_id = $contact_id = $lead->contact_id;

        // 1. Lead's contact_id should not be null
        $this->assertNotNull($contact_id, 'Lead has null contact id after conversion.');

        // 2. Lead status should be 'Converted'
        $this->assertEquals('Converted', $lead->status, "Lead atatus should be 'Converted'.");

        // 3. parent_type of the original meeting should be Leads
        $this->assertEquals('Leads', $meeting->parent_type, 'Meeting parent should be Leads');

        // 4. parent_id of the original meeting should be contact id
        $this->assertEquals($lead_id, $meeting->parent_id, 'Meeting parent id should be lead id.');

        // 5. record should NOT be deleted from meetings_leads table
        $sql = "select id from meetings_leads where meeting_id='{$meeting->id}' and lead_id='{$lead->id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse(empty($row), "Meeting-Lead relationship is removed.");

        // 6. record should NOT be added to meetings_contacts table
        $sql = "select meeting_id from meetings_contacts where contact_id='{$contact_id}' and deleted=0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertFalse($row, "Meeting-Contact relationship should not be added.");

        // to suppress output on phpunit (need to be reviewed when proper tests are made)
        $this->expectOutputRegex('/Used an existing contact/');
    }

    public function testMeetingsUsersRelationships()
    {
        global $current_user;

        $bean = SugarTestMeetingUtilities::createMeeting();
        $convert_lead = SugarTestViewConvertLeadUtilities::createViewConvertLead();

        if ($bean->object_name == "Meeting")
        {
            $convert_lead->setMeetingsUsersRelationship($bean);
        }

        $this->assertTrue(is_object($bean->users), "Relationship wasn't set.");
    }
}

class TestViewConvertLead extends ViewConvertLead
{
    public function moveActivityWrapper($activity, $bean) {
        parent::moveActivity($activity, $bean);
    }

    public function copyActivityWrapper($activity, $bean,$parent=array()) {
        parent::copyActivityAndRelateToBean($activity, $bean,$parent);
    }

    public function testMeetingsUsersRelationships()
    {
        global $current_user;

        $bean = SugarTestMeetingUtilities::createMeeting();
        $convert_lead = SugarTestViewConvertLeadUtilities::createViewConvertLead();

        if ($bean->object_name == "Meeting")
        {
            $convert_lead->setMeetingsUsersRelationship($bean);
        }

        $this->assertTrue(is_object($bean->users), "Relationship wasn't set.");
    }
}
