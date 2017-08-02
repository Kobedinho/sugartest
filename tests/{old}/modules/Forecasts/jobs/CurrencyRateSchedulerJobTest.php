<?php

//TODO: fix this up for when expected opps is added back in 6.8 - https://sugarcrm.atlassian.net/browse/SFA-255
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


class CurrencyRateSchedulerJobTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $currency;
    private $opportunity;
    private $opportunityClosed;
    private $quota;
    private $forecast;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        SugarTestForecastUtilities::setUpForecastConfig();
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        parent::tearDownAfterClass();
    }

    public function setUp()
    {
        global $current_user;
        $this->currency = SugarTestCurrencyUtilities::createCurrency('UpdateBaseRateSchedulerJob', 'UBRSJ', 'UBRSJ', 1.234);

        $this->opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $this->opportunity->currency_id = $this->currency->id;
        $this->opportunity->sales_stage = 'Prospecting';
        $this->opportunity->save();

        $this->opportunityClosed = SugarTestOpportunityUtilities::createOpportunity();
        $this->opportunityClosed->sales_stage = Opportunity::STAGE_CLOSED_WON;
        $this->opportunityClosed->currency_id = $this->currency->id;
        $this->opportunityClosed->save();

        $this->quota = SugarTestQuotaUtilities::createQuota(500);
        $this->quota->currency_id = $this->currency->id;
        $this->quota->save();

        $timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();

        $this->forecast = SugarTestForecastUtilities::createForecast($timeperiod, $current_user);
        // currency is always base, set by forecast save()
        $this->forecast->save();

    }

    public function tearDown()
    {
        SugarTestJobQueueUtilities::removeAllCreatedJobs();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
    }

    /**
     * @group forecasts
     */
    public function testCurrencyRateSchedulerJob()
    {
        $this->markTestIncomplete('SFA Team - test fails in suite');
        global $current_user;
        $db = DBManagerFactory::getInstance();

        $oppBaseRatePreJob = $db->getOne(sprintf("SELECT base_rate FROM opportunities WHERE id = '%s'", $this->opportunity->id));
        $oppUsDollarPreJob = $db->getOne(sprintf("SELECT amount_usdollar FROM opportunities WHERE id = '%s'", $this->opportunity->id));
        $oppBaseRateClosedPreJob = $db->getOne(sprintf("SELECT base_rate FROM opportunities WHERE id = '%s'", $this->opportunityClosed->id));
        $oppUsDollarClosedPreJob = $db->getOne(sprintf("SELECT amount_usdollar FROM opportunities WHERE id = '%s'", $this->opportunityClosed->id));
        $quotaBaseRatePreJob = $db->getOne(sprintf("SELECT base_rate FROM quotas WHERE id = '%s'", $this->quota->id));
        $forecastBaseRatePreJob = $db->getOne(sprintf("SELECT base_rate FROM forecasts WHERE id = '%s'", $this->forecast->id));

        // change the conversion rate
        $this->currency->conversion_rate = '2.345';
        $this->currency->save();

        $job = SugarTestJobQueueUtilities::createAndRunJob(
            'TestJobQueue',
            'class::SugarJobUpdateCurrencyRates',
            $this->currency->id,
            $current_user);

        //$this->assertTrue($job->runnable_ran);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");

        $oppBaseRate = $db->getOne(sprintf("SELECT base_rate FROM opportunities WHERE id = '%s'", $this->opportunity->id));
        $oppUsDollar = $db->getOne(sprintf("SELECT amount_usdollar FROM opportunities WHERE id = '%s'", $this->opportunity->id));
        $oppBaseRateClosed = $db->getOne(sprintf("SELECT base_rate FROM opportunities WHERE id = '%s'", $this->opportunityClosed->id));
        $oppUsDollarClosed = $db->getOne(sprintf("SELECT amount_usdollar FROM opportunities WHERE id = '%s'", $this->opportunityClosed->id));
        $quotaBaseRate = $db->getOne(sprintf("SELECT base_rate FROM quotas WHERE id = '%s'", $this->quota->id));
        $forecastBaseRate = $db->getOne(sprintf("SELECT base_rate FROM forecasts WHERE id = '%s'", $this->forecast->id));

        $this->assertNotEquals($oppBaseRatePreJob, $oppBaseRate, 'opportunities.base_rate was not modified by CurrencyRateSchedulerJob');
        $this->assertNotEquals($oppUsDollarPreJob, $oppUsDollar, 'opportunities.amount_usdollar was not modified by CurrencyRateSchedulerJob',2);
        $this->assertEquals($oppBaseRateClosedPreJob, $oppBaseRateClosed, 'opportunities.base_rate was modified by CurrencyRateSchedulerJob');
        $this->assertEquals($oppUsDollarClosedPreJob, $oppUsDollarClosed, 'opportunities.amount_usdollar was modified by CurrencyRateSchedulerJob for closed opportunity',2);
        $this->assertNotEquals($quotaBaseRatePreJob, $quotaBaseRate, 'quotas.base_rate was not modified by CurrencyRateSchedulerJob');
        $this->assertEquals($forecastBaseRatePreJob, $forecastBaseRate, 'forecasts.base_rate was modified by CurrencyRateSchedulerJob');
    }

    /**
     * @group forecasts
     */
    public function testCurrencyRateDefinitions()
    {
        $test = new UpdateRateTest();
        $test->addRateColumnDefinition('foo','bar');
        $test->addRateColumnDefinition('foo','baz');
        $test->addRateColumnDefinition('foo','biz');
        $rates = $test->getRateColumnDefinitions('foo');
        $this->assertEquals(array('bar','baz','biz'),$rates);
        $test->removeRateColumnDefinition('foo','baz');
        $rates = $test->getRateColumnDefinitions('foo');
        $this->assertEquals(array('bar','biz'),$rates);

        $test->addUsDollarColumnDefinition('foo','bar','bar_usdollar');
        $test->addUsDollarColumnDefinition('foo','baz','baz_usdollar');
        $test->addUsDollarColumnDefinition('foo','biz','biz_usdollar');
        $rates = $test->getUsDollarColumnDefinitions('foo');
        $this->assertEquals(array(
            'bar'=>'bar_usdollar',
            'baz'=>'baz_usdollar',
            'biz'=>'biz_usdollar'
        ),$rates);
        $test->removeUsDollarColumnDefinition('foo','baz');
        $rates = $test->getUsDollarColumnDefinitions('foo');
        $this->assertEquals(array(
            'bar'=>'bar_usdollar',
            'biz'=>'biz_usdollar'
        ),$rates);
    }

}

class UpdateRateTest extends CurrencyRateUpdateAbstract {

}
