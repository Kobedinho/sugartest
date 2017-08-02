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

class CronForkTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // Unfortunately, this test can not be run automatically, since it uses parallel processes
        // and long timeouts. I check it in to make possible to run it manually if needed.
        // Manual testing - run this test and see that after 20 seconds job status in the DB changes to
        // success. You'll have to comment out the next line first, of course.
        $this->markTestSkipped("Cannot be run as part of automated suite");
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        // clean up queue
		$GLOBALS['db']->query("DELETE FROM job_queue WHERE status='queued'");
        $this->jq = $jobq = new SugarCronParallelJobs();
        // Uncomment to test shell on systems with pcntl_fork
        // $jobq->allow_fork = false;
    }

    public function tearDown()
    {
        // Disabling delete since we want it for manual test run
   //     $GLOBALS['db']->query("DELETE FROM job_queue WHERE scheduler_id='unittest'");
       sleep(2);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public static function cronJobFunction($job)
    {
        sleep(20);
        $job->succeedJob("OK!");
        return true;
    }

    public function testQueueJob()
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->name = "Unit test Job";
        $job->target = "function::CronForkTest::cronJobFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid = $job->id;

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        // Not doing asserts here - we'll check the DB manually.
    }
}
