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
require_once 'tests/{old}/SugarTestUserUtilities.php';
require_once 'tests/{old}/SugarTestAccountUtilities.php';

class RunnableSchedulersJobsTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $jobs = array();

    public function setUp()
    {
        $this->db = DBManagerFactory::getInstance();
    }

    public function tearDown()
    {
        if(!empty($this->jobs)) {
            $jobs = implode("','", $this->jobs);
            $this->db->query("DELETE FROM job_queue WHERE id IN ('$jobs')");
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $ids = SugarTestAccountUtilities::getCreatedAccountIds();
        if(!empty($ids)) {
            SugarTestAccountUtilities::removeAllCreatedAccounts();
        }
    }

    protected function createJob($data)
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        foreach($data as $key => $val) {
            $job->$key = $val;
        }
        $job->execute_time = empty($job->execute_time) ? TimeDate::getInstance()->getNow()->asDb() : $job->execute_time;
        $job->save();
        $this->jobs[] = $job->id;
        return $job;
    }



    public function testRunnableJobRunClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $job = $this->createJob(array("name" => "Test Func", "status" => SchedulersJob::JOB_STATUS_RUNNING,
            "target" => "class::TestRunnableJob", "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);

        $this->assertTrue($job->runnable_ran);

        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals($GLOBALS['current_user']->id, $job->user->id, "Wrong user");

        // function with args
        $job = $this->createJob(array("name" => "Test Func 2", "status" => SchedulersJob::JOB_STATUS_RUNNING,
                    "target" => "class::TestRunnableJob",
                    "data" => "function data", "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertTrue($job->runnable_ran);
        $this->assertEquals($job->runnable_data, "function data", "Argument 2 doesn't match");
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals($GLOBALS['current_user']->id, $job->user->id, "Wrong user");
    }
}


class TestRunnableJob implements RunnableSchedulerJob
{
    private $job;

    public function run($data)
    {
        $this->job->runnable_ran = true;
        $this->job->runnable_data = $data;
        $this->job->succeedJob();
        $this->job->user = $GLOBALS['current_user'];
        return $this->job->resolution;
    }

    public function setJob(SchedulersJob $job)
    {
        $this->job = $job;
    }
}