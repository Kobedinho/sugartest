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

require_once('modules/UpgradeWizard/uw_utils.php');

class UWUtilsTest extends Sugar_PHPUnit_Framework_TestCase  {

    private $job;
    private static $isSetup;
    private static $forecastRanges;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        //Set is_setup to 0 for testing purposes
        SugarTestForecastUtilities::setUpForecastConfig(array(
                'forecast_ranges' => 'show_binary',
                'forecast_by' => 'Opportunities'
            ));
        $db = DBManagerFactory::getInstance();
        $db->query("UPDATE opportunities SET deleted = 1");

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        $db = DBManagerFactory::getInstance();
        $db->query("UPDATE opportunities SET deleted = 0");
        SugarTestHelper::tearDown();

        parent::tearDownAfterClass();
    }

    public function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestJobQueueUtilities::removeAllCreatedJobs();
        parent::tearDown();
    }


    /**
     * Check that for every old opportunity related products are created via job queue
     *
     * @global type $current_user
     * @group forecasts
     */
    public function testSugarJobUpdateOpportunities()
    {
        $this->markTestIncomplete("Probability Incorrect.  SFA Team Should Diagnose");
        global $db, $current_user;

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->assigned_user_id = $current_user->id;
        $opp->probability = '';
        $opp->commit_stage = '';
        $opp->save();

        $this->assertEmpty($opp->commit_stage, 'Commit stage should be empty for old Opportunity');

        //unset best/worst cases
        $db->query("UPDATE opportunities SET best_case = NULL, worst_case = NULL, probability = 80 WHERE id = '{$opp->id}'");

        $this->job = updateOpportunitiesForForecasting();

        $job = new SchedulersJob();
        $job->retrieve($this->job);
        $job->runnable_ran = true;
        $job->runnable_data = '';
        $job->runJob();

        $updated_opp = BeanFactory::newBean('Opportunities');
        $updated_opp->retrieve($opp->id);
        $this->assertNotEmpty($updated_opp->commit_stage, "Updated opportunity's commit stage should not be empty");

        $exp_product = array('name' => $updated_opp->name,
            'best_case' => $updated_opp->best_case,
            'likely_case' => $updated_opp->amount,
            'worst_case' => $updated_opp->worst_case,
            'cost_price' => $updated_opp->amount,
            'quantity' => '1',
            'currency_id' => $updated_opp->currency_id,
            'base_rate' => $updated_opp->base_rate,
            'probability' => $updated_opp->probability,
            'assigned_user_id' => $updated_opp->assigned_user_id,
            'opportunity_id' => $updated_opp->id,
            'commit_stage' => $updated_opp->commit_stage);

        $this->assertTrue($job->runnable_ran);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");

    }
}
