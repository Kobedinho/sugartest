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

class SugarForecasting_Progress_ManagerTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var array args to be passed onto methods
     */
    protected static $args = array();

    /**
     * @var array array of users used throughout class
     */
    protected static $users = array();

    /**
     * @var Currency
     */
    protected static $currency;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setup('mod_strings', array('manager', 'Forecasts'));
        SugarTestHelper::setup('current_user');
        SugarTestForecastUtilities::setUpForecastConfig();

        $timeperiod = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');

        self::$args['timeperiod_id'] = $timeperiod->id;

        self::$currency = SugarTestCurrencyUtilities::createCurrency('Yen','¥','YEN',78.87);

        SugarTestForecastUtilities::setTimePeriod($timeperiod);

        self::$users['top_manager'] = SugarTestForecastUtilities::createForecastUser(array(
                'timeperiod_id' => $timeperiod->id,
                'currency_id' => self::$currency->id,
                'quota' => array('amount' => 30000)
            ));

        self::$users['manager'] = SugarTestForecastUtilities::createForecastUser(array(
                'timeperiod_id' => $timeperiod->id,
                'currency_id' => self::$currency->id,
                'quota' => array('amount' => 50000),
                'user' =>
                array('manager', 'reports_to' => self::$users['top_manager']['user']->id)
            ));

        global $current_user;

        $current_user = self::$users['top_manager']['user'];
        self::$args['user_id'] = self::$users['manager']['user']->id;
        $current_user->setPreference('currency', self::$currency->id);

        $current_user = self::$users['manager']['user'];

        $config = array(
            'timeperiod_id' => $timeperiod->id,
            'currency_id' => self::$currency->id,
            'user' =>
            array('manager', 'reports_to' => self::$users['manager']['user']->id),
            'quota' => array('amount' => 27000)
        );
        self::$users['reportee'] = SugarTestForecastUtilities::createForecastUser($config);

    }

    /**
     * reset after each test back to manager id, some tests may have changed to use top manager
     */
    public function setup() {
        self::$args['user_id'] = self::$users['manager']['user']->id;

        global $current_user;
        $current_user = self::$users['manager']['user'];
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestHelper::tearDown();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        $timedate = TimeDate::getInstance();
        $timedate->allow_cache = true;
        parent::tearDownAfterClass();
    }

    /**
     * destroy some parts after each test
     */
    public function tearDown() {
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
        parent::tearDown();
    }

    /**
     * Utility function for calculating pipeline sums
     *
     * @param obj Top level manager
     * @param obj users, passed in as individual arguments ($manager, $user1, $user2, ... $userN)
     * @return array consisting of the amount and closed amount
     */
    protected function calculatePipelineAmount($manager, $user)
    {
        $returnArray = array("amount" => 0, "closed" => 0);

        $users = array($user);

        $numargs = func_num_args();
        if ($numargs > 2) {
            for ($i = 2; $i < $numargs; $i++) {
                $users[] = func_get_arg($i);
            }
        }

        foreach($manager["opportunities"] as $opp){
            if($opp->sales_stage != Opportunity::STAGE_CLOSED_WON || $opp->sales_stage != Opportunity::STAGE_CLOSED_LOST){
                $returnArray["amount"] += $opp->amount;
            } else if($opp->sales_stage == Opportunity::STAGE_CLOSED_WON){
                $returnArray["closed"] += $opp->closed;
            }
        }

        foreach($users as $user){
            $returnArray["amount"] += ($user["forecast"]->pipeline_amount);
            $returnArray["closed"] += ($user["forecast"]->closed_amount);
        }

        return $returnArray;
    }

    /* Check for a manager with reps and submanagers with reps with committed opps.. make sure the cascade works after
    * a simulated multisave (commiting multiple times) and marking some as close won/lost
    *
    * @group forecasts
    * @group forecastsprogress
    */
    public function testManagerWithSubManagerAndReps_multisave_withCloseLostWon()
    {
        $this->markTestIncomplete("This needs to be refactored to work with the totals from the worksheets");
        $manager = SugarTestForecastUtilities::createForecastUser(array(
                'opportunities' => array(
                    'total' => 2,
                    'include_in_forecast' => 2
                )
            ));
        $subManager1 = SugarTestForecastUtilities::createForecastUser(array(
                'user' => array(
                    'reports_to' => $manager["user"]->id
                ),
                'opportunities' => array(
                    'total' => 2,
                    'include_in_forecast' => 1
                ),
            ));
        $subManager2 = SugarTestForecastUtilities::createForecastUser(array(
                'user' => array(
                    'reports_to' => $manager["user"]->id
                ),
                'opportunities' => array(
                    'total' => 2,
                    'include_in_forecast' => 1
                ),
            ));
        $reportee1 = SugarTestForecastUtilities::createForecastUser(array(
                'user' => array(
                    'reports_to' => $subManager1["user"]->id
                ),
                'opportunities' => array(
                    'total' => 2,
                    'include_in_forecast' => 2
                ),
            ));
        $reportee2 = SugarTestForecastUtilities::createForecastUser(array(
                'user' => array(
                    'reports_to' => $subManager2["user"]->id
                ),
                'opportunities' => array(
                    'total' => 2,
                    'include_in_forecast' => 2
                ),
            ));
        $reportee3 = SugarTestForecastUtilities::createForecastUser(array(
                'user' => array(
                    'reports_to' => $manager["user"]->id
                ),
                'opportunities' => array(
                    'total' => 2,
                    'include_in_forecast' => 2
                ),
            ));

        //now we want to change the stage to close lost/close won of a few opps, commit, and make sure they are excluded
        $manager['opportunities'][0]->sales_stage = Opportunity::STAGE_CLOSED_WON;
        $manager['opportunities'][0]->save();
        $reportee3['opportunities'][0]->sales_stage = Opportunity::STAGE_CLOSED_WON;
        $reportee3['opportunities'][0]->save();
        $reportee2['opportunities'][0]->sales_stage = Opportunity::STAGE_CLOSED_LOST;
        $reportee2['opportunities'][0]->save();

        //sleep needed so that the new committed forecasts are clearly newer
        sleep(1);
        //recommit
        $reportee3['forecast'] = SugarTestForecastUtilities::createRepDirectForecast($reportee3);
        $reportee2['forecast'] = SugarTestForecastUtilities::createRepDirectForecast($reportee2);
        $subManager1['forecast'] = SugarTestForecastUtilities::createManagerRollupForecast($subManager1, $reportee1);
        $subManager2['forecast'] = SugarTestForecastUtilities::createManagerRollupForecast($subManager2, $reportee2);
        $manager['forecast'] = SugarTestForecastUtilities::createManagerRollupForecast($manager, $subManager1, $subManager2, $reportee3);

        //calculate what the amount should be
        $totals = $this->calculatePipelineAmount($manager, $subManager1, $subManager2, $reportee3);
        $amount = $totals["amount"];
        $closed = $totals["closed"];

        $obj = new SugarForecasting_Progress_Manager(array(
            "timeperiod_id" => SugarTestForecastUtilities::getCreatedTimePeriod()->id,
            "user_id" => $manager["user"]->id
        ));

        $data = $obj->process();

        //Make sure closed amounts match
        $this->assertEquals($closed, $data["closed_amount"], "Closed Amount Incorrect.");
    }

    public function testManagerWithSubManagerAndReps_multisave_withOnlyCloseWon()
    {
        $manager = SugarTestForecastUtilities::createForecastUser(array(
                'opportunities' => array(
                    'total' => 1,
                    'include_in_forecast' => 0
                )
            ));
        $subManager1 = SugarTestForecastUtilities::createForecastUser(array(
                'user' => array(
                    'reports_to' => $manager["user"]->id
                ),
                'opportunities' => array(
                    'total' => 1,
                    'include_in_forecast' => 0
                ),
            ));
        $reportee1 = SugarTestForecastUtilities::createForecastUser(array(
                'user' => array(
                    'reports_to' => $subManager1["user"]->id
                ),
                'opportunities' => array(
                    'total' => 1,
                    'include_in_forecast' => 1
                ),
            ));


        //now we want to change the stage to close lost/close won of a few opps, commit, and make sure they are excluded
        $reportee1['opportunities'][0]->sales_stage = Opportunity::STAGE_CLOSED_WON;
        $reportee1['opportunities'][0]->save();

        //sleep needed so that the new committed forecasts are clearly newer
        sleep(1);
        //recommit
        $reportee1['forecast'] = SugarTestForecastUtilities::createRepDirectForecast($reportee1);
        $subManager1['forecast'] = SugarTestForecastUtilities::createManagerRollupForecast($subManager1, $reportee1);
        $manager['forecast'] = SugarTestForecastUtilities::createManagerRollupForecast($manager, $subManager1);

        //calculate what the amount should be
        $totals = $this->calculatePipelineAmount($manager, $subManager1);
        $amount = $totals["amount"];
        $closed = $totals["closed"];

        $obj = new SugarForecasting_Progress_Manager(array(
            "timeperiod_id" => SugarTestForecastUtilities::getCreatedTimePeriod()->id,
            "user_id" => $manager["user"]->id
        ));

        $data = $obj->process();

        //Make sure closed amounts match
        $this->assertEquals($closed, $data["closed_amount"], "Closed Amount Incorrect.");
    }

    /* Check for a manager with reps and submanagers with reps with committed opps.. make sure the cascade works after
     * a simulated multisave (commiting multiple times) and marking some as close won/lost (with different currencies)
     *
     * @group forecasts
     * @group forecastsprogress
     */
    public function testManagerWithSubManagerAndReps_multisave_withCloseLostWon_diffCurrencies()
    {
        $this->markTestIncomplete("This needs to be refactored to work with the totals from the worksheets");
        $manager = SugarTestForecastUtilities::createForecastUser(array(
                'opportunities' => array(
                    'total' => 2,
                    'include_in_forecast' => 2
                )
            ));
        $subManager1 = SugarTestForecastUtilities::createForecastUser(array(
                'user' => array(
                    'reports_to' => $manager["user"]->id
                ),
                'opportunities' => array(
                    'total' => 2,
                    'include_in_forecast' => 1
                ),
            ));
        $subManager2 = SugarTestForecastUtilities::createForecastUser(array(
                'user' => array(
                    'reports_to' => $manager["user"]->id
                ),
                'opportunities' => array(
                    'total' => 2,
                    'include_in_forecast' => 1
                ),
            ));
        $reportee1 = SugarTestForecastUtilities::createForecastUser(array(
                'user' => array(
                    'reports_to' => $subManager1["user"]->id
                ),
                'opportunities' => array(
                    'total' => 2,
                    'include_in_forecast' => 2
                ),
            ));
        $reportee2 = SugarTestForecastUtilities::createForecastUser(array(
                'user' => array(
                    'reports_to' => $subManager2["user"]->id
                ),
                'opportunities' => array(
                    'total' => 2,
                    'include_in_forecast' => 2
                ),
            ));
        $reportee3 = SugarTestForecastUtilities::createForecastUser(array(
                'user' => array(
                    'reports_to' => $manager["user"]->id
                ),
                'opportunities' => array(
                    'total' => 2,
                    'include_in_forecast' => 2
                ),
            ));

        //now we want to change the stage to close lost/close won of a few opps, commit, and make sure they are excluded
        $manager['opportunities'][0]->sales_stage = Opportunity::STAGE_CLOSED_WON;
        $manager['opportunities'][0]->currency_id = self::$currency->id;
        $manager['opportunities'][0]->save();
        $reportee3['opportunities'][0]->sales_stage = Opportunity::STAGE_CLOSED_WON;
        $reportee3['opportunities'][0]->save();
        $reportee3['opportunities'][1]->currency_id = self::$currency->id;
        $reportee3['opportunities'][1]->save();
        $reportee2['opportunities'][0]->sales_stage = Opportunity::STAGE_CLOSED_LOST;
        $reportee2['opportunities'][0]->save();

        //sleep needed so that the new committed forecasts are clearly newer
        sleep(1);
        //recommit
        $reportee3['forecast'] = SugarTestForecastUtilities::createRepDirectForecast($reportee3);
        $reportee2['forecast'] = SugarTestForecastUtilities::createRepDirectForecast($reportee2);
        $subManager1['forecast'] = SugarTestForecastUtilities::createManagerRollupForecast($subManager1, $reportee1);
        $subManager2['forecast'] = SugarTestForecastUtilities::createManagerRollupForecast($subManager2, $reportee2);
        $manager['forecast'] = SugarTestForecastUtilities::createManagerRollupForecast($manager, $subManager1, $subManager2, $reportee3);

        //calculate what the amount should be
        $totals = $this->calculatePipelineAmount($manager, $subManager1, $subManager2, $reportee3);
        $amount = $totals["amount"];
        $closed = $totals["closed"];

        $obj = new SugarForecasting_Progress_Manager(array(
            "timeperiod_id" => SugarTestForecastUtilities::getCreatedTimePeriod()->id,
            "user_id" => $manager["user"]->id
        ));

        $data = $obj->process();

        //Make sure closed amounts match
        $this->assertEquals($closed, $data["closed_amount"], "Closed Amount Incorrect.");
    }

    /**
     * Dataset Provider
     *
     * @return array
     */
    public function dataProviderDatasets()
    {
        // keys are as follows
        // 1 -> quota amount
        // 2 -> quota currency id

        return array(
            array(false, false, 0, 0, -99),
            array(true, false, 15000, 0, -99),
            array(false, true, 0, 30000, -99),
            array(true, true, 15000, 30000, -99),
        );
    }

    /**
     * check top level manager quota to make sure it returns the expected sum of values for manager that doesn't report to anyone
     *
     * @dataProvider dataProviderDatasets
     * @group forecasts
     * @group forecastsprogress
     */
    public function testGetTopLevelManagerQuota($createManagerWorksheet, $createRepWorksheet, $managerQuotaAmount, $repQuotaAmount, $quotaCurrencyId)
    {
        $this->markTestSkipped('Test needs updated as part of projected panel migration to using new worksheets.');
        global $current_user;
        $current_user = self::$users['top_manager']['user'];
        self::$args['user_id'] = self::$users['top_manager']['user']->id;

        if($createManagerWorksheet) {
            //create worksheet data based on dataprovider
            $managerWorksheet = SugarTestWorksheetUtilities::createWorksheet();
            $managerWorksheet->user_id = self::$users['top_manager']['user']->id;
            $managerWorksheet->related_id = self::$users['top_manager']['user']->id;
            $managerWorksheet->timeperiod_id = self::$args['timeperiod_id'];
            $managerWorksheet->quota = $managerQuotaAmount;
            $managerWorksheet->currency_id = $quotaCurrencyId;
            $managerWorksheet->forecast_type = 'Rollup';
            $managerWorksheet->related_forecast_type = 'Direct';
            $managerWorksheet->version = 0;
            $managerWorksheet->save();
        }
        if($createRepWorksheet) {
            //create worksheet data based on dataprovider
            $repWorksheet = SugarTestWorksheetUtilities::createWorksheet();
            $repWorksheet->user_id = self::$users['top_manager']['user']->id;
            $repWorksheet->related_id = self::$users['manager']['user']->id;
            $repWorksheet->timeperiod_id = self::$args['timeperiod_id'];
            $repWorksheet->quota = $repQuotaAmount;
            $repWorksheet->currency_id = $quotaCurrencyId;
            $repWorksheet->forecast_type = 'Rollup';
            $repWorksheet->related_forecast_type = 'Rollup';
            $repWorksheet->version = 0;
            $repWorksheet->save();
        }
        $obj = new SugarForecasting_Progress_Manager(self::$args);
        $quotaAmount = $obj->getQuotaTotalFromData();

        $expectedQuotaAmount = 0;
        //determine how much quota should be
        foreach(SugarTestQuotaUtilities::getCreatedQuotaIds() as $quotaID) {
            $quota = BeanFactory::getBean('Quotas', $quotaID);
            if($quota->timeperiod_id == self::$args['timeperiod_id'])
            {
                if($quota->quota_type == "Direct" && $quota->user_id == self::$args['user_id'])
                {
                    //personal quota for the top level manager
                    //use worksheet quota number as it will cause an override in the function
                    $expectedQuotaAmount += ($createManagerWorksheet ? $managerQuotaAmount : $quota->amount);
                }
                if($quota->quota_type == "Rollup" && $quota->user_id == self::$users['manager']['user']->id)
                {
                    $expectedQuotaAmount += ($createRepWorksheet ? $repQuotaAmount : $quota->amount);
                }

            }
        }
        //compare expected and actual
        $this->assertEquals($expectedQuotaAmount, $quotaAmount, "Quota not matching expected amount.  Expected: ".$expectedQuotaAmount." Actual: ".$quotaAmount);
    }
}
