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


class iCalTest extends Sugar_PHPUnit_Framework_TestCase
{
    var $timedate;
    var $project;

    public function setUp()
    {
        $this->timedate = new TimeDate();

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->full_name = "Boro Sitnikovski";
        $GLOBALS['current_user']->email1 = "bsitnikovski@sugarcrm.com";

        $meeting = SugarTestMeetingUtilities::createMeeting();
        $meeting->name = "VeryImportantMeeting";
        $meeting->date_start = $this->timedate->to_display_date_time(gmdate("Y-m-d H:i:s", mktime(12, 30, 00, date("m"), date("d")+1, date("Y"))));
        $meeting->save();
        $GLOBALS['db']->query(sprintf("INSERT INTO meetings_users (id, meeting_id, user_id, required, accept_status, date_modified, deleted) VALUES ('%s', '%s', '%s', '1', 'none', NULL, '0')", create_guid(), $meeting->id, $GLOBALS['current_user']->id));

        $task = SugarTestTaskUtilities::createTask();
        $task->assigned_user_id = $GLOBALS['current_user']->id;
        $task->name = "VeryImportantTask";
        $task->save();

        $this->project = SugarTestProjectUtilities::createProject();
        $projectId = $this->project->id;
        $projectTaskData = array (
            'project_id' => $projectId,
            'parent_task_id' => '',
            'project_task_id' => 1,
            'percent_complete' => 50,
            'name' => 'VeryImportantProjectTask'
        );
        $projectTask = SugarTestProjectTaskUtilities::createProjectTask($projectTaskData);
        $projectTask->assigned_user_id = $GLOBALS['current_user']->id;
        $projectTask->save();

    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestProjectUtilities::removeAllCreatedProjects();
        SugarTestProjectTaskUtilities::removeAllCreatedProjectTasks();
        unset($this->timedate);
        unset($this->project);
        unset($GLOBALS['current_user']);
    }

    public function testGetVcalIcal()
    {
        $iCal = new iCal();
        $iCalString = $iCal->getVcalIcal($GLOBALS['current_user'], null);

        // echo "***********BEGIN iCalString*************\n".$iCalString."***********END iCalString***************\n";

        $this->assertContains("VeryImportantMeeting", $iCalString, "Cannot find VEVENT: VeryImportantMeeting");
        $this->assertContains("VeryImportantTask", $iCalString, "Cannot find VTODO: VeryImportantTask");
        $this->assertContains("VeryImportantProjectTask", $iCalString, "Cannot find VTODO: VeryImportantProjectTask");
    }

    public function testiCalNewline()
    {
        $res = vCal::get_ical_event($this->getDummyBean("http://www.sugarcrm.com/"), $GLOBALS['current_user']);

        $desc = $this->grabiCalField($res, "DESCRIPTION");
        // Test to see if there are two newlines after url for description
        $this->assertContains("http://www.sugarcrm.com/\r\n\r\n", $desc);
    }

    public function testiCalEmptyJoinURL()
    {
        $res = vCal::get_ical_event($this->getDummyBean(), $GLOBALS['current_user']);

        $desc = $this->grabiCalField($res, "DESCRIPTION");

        // Test to see if there are no newlines for empty url for description
        $this->assertNotContains("\\n\\n", $desc);
    }

    private function grabiCalField($iCal, $field)
    {
        $ical_arr = vCal::create_ical_array_from_string($iCal);

        foreach ($ical_arr as $ical_val) {
            if ($ical_val[0] == $field) {
                return $ical_val[1];
            }
        }

        return "";
    }

    private function getDummyBean($join_url = "")
    {
        $bean = new SugarBean();
        $bean->id = 123;
        $bean->date_start = $bean->date_end = $GLOBALS['timedate']->nowDb();
        $bean->name = "Dummy Bean";
        $bean->location = "Sugar, Cupertino; Sugar, EMEA";
        $bean->join_url = $join_url;
        $bean->description = "Hello, this is a dummy description.\n"
            . "It contains newlines, backslash \\ semicolon ; and commas";
        return $bean;
    }
}
