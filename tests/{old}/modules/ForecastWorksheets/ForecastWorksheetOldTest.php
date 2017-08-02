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

class ForecastWorksheetOldTest extends Sugar_PHPUnit_Framework_TestCase
{

    protected static $settings = array();

    /**
     * @var TimePeriod
     */
    protected static $timeperiod;

    /**
     * @var User
     */
    protected static $user;

    public function setUp()
    {
        $this->markTestIncomplete("SFA, please resolve the issue, where in strict mode the test fails because forecast_worksheet does not have a date closed set");
        parent::setUp();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        SugarTestForecastUtilities::setUpForecastConfig();

        self::$timeperiod = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');

        self::$user = SugarTestUserUtilities::createAnonymousUser();

        // setup 10 products
        for ($x = 0; $x < 10; $x++) {
            $product = SugarTestProductUtilities::createProduct();
            $product->date_closed = '2009-02-01';
            $product->assigned_user_id = self::$user->id;
            $product->save();
        }
    }

    public function tearDown()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();

        SugarTestWorksheetUtilities::removeAllWorksheetsForParentIds(SugarTestProductUtilities::getCreatedProductIds());
        SugarTestWorksheetUtilities::removeAllWorksheetsForParentIds(SugarTestOpportunityUtilities::getCreatedOpportunityIds());
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestJobQueueUtilities::removeAllCreatedJobs();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();

        parent::tearDown();
    }

    /**
     * @group forecasts
     */
    public function testCommitWorksheetDoesNotCreateAnyJobsAndHasTenCommittedRecords()
    {
        /* @var $bean ForecastWorksheet */
        $bean = BeanFactory::newBean('ForecastWorksheets');
        $bean->commitWorksheet(self::$user->id, self::$timeperiod->id);

        // query for jobs based on name
        $sq = new SugarQuery();
        $sq->select(array('id'));
        $sq->from(BeanFactory::newBean('SchedulersJobs'))->where()
            ->equals('target', 'class::SugarJobUpdateForecastWorksheets');
        $results = $sq->execute();

        foreach($results as $result) {
            SugarTestJobQueueUtilities::setCreatedJobs(array($result['id']));
        }

        $this->assertEquals(0, count($results));

        $worksheets = SugarTestWorksheetUtilities::loadWorksheetForBeans(
            'Products',
            SugarTestProductUtilities::getCreatedProductIds(),
            true
        );
        $this->assertEquals(10, count($worksheets));
    }

    /**
     * @group forecasts
     */
    public function testCommitWorksheetsCreatesOneJobAndHasFiveCommittedRecords()
    {
        /* @var $bean ForecastWorksheet */
        $bean = BeanFactory::newBean('ForecastWorksheets');
        $bean->commitWorksheet(self::$user->id, self::$timeperiod->id, 5);

        // query for jobs based on name
        $sq = new SugarQuery();
        $sq->select(array('id'));
        $sq->from(BeanFactory::newBean('SchedulersJobs'))->where()
            ->equals('target', 'class::SugarJobUpdateForecastWorksheets');
        $results = $sq->execute();

        foreach($results as $result) {
            SugarTestJobQueueUtilities::setCreatedJobs(array($result['id']));
        }

        // we should have 1 job created
        $this->assertEquals(1, count($results));

        // make sure we have 5 worksheets committed (the other 5 are in the job)
        $worksheets = SugarTestWorksheetUtilities::loadWorksheetForBeans(
            'Products',
            SugarTestProductUtilities::getCreatedProductIds(),
            true
        );
        $this->assertEquals(5, count($worksheets));
    }

    /**
     * Make sure closed_date is being updated on worksheet save
     *
     * @ticket SFA-704
     * @group forecasts
     * @group products
     * @group opportunities
     */
    public function testExpectedCloseDateRollupWorks()
    {
        $this->markTestIncomplete('Failing on PRO. Need to be fixed by SFA team');
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $product = SugarTestProductUtilities::createProduct();

        $product->date_closed = '2009-02-01';
        $product->opportunity_id = $opp->id;
        $product->likely_case = '1000.00';

        $product->save();

        // test to make sure opp was updated
        /* @var $oppBean Opportunity */
        $oppBean = BeanFactory::newBean($opp->module_name);
        $oppBean->retrieve($opp->id);

        // test the db formatted version
        $this->assertEquals($product->date_closed, $oppBean->fetched_row['date_closed']);

        // find the product worksheet
        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($product);

        // make sure a worksheet is actually returned
        $this->assertInstanceOf('ForecastWorksheet', $worksheet);
        // make sure it has the correct date closed on it
        $this->assertEquals($product->date_closed, $worksheet->date_closed);

        // update the worksheet date_closed
        $worksheet->date_closed = '2009-03-16';
        $worksheet->saveWorksheet();

        // get the product and make sure it was updated
        /* @var $prodBean Product */
        $prodBean = BeanFactory::newBean($product->module_name);
        $prodBean->retrieve($product->id);

        // test the db formatted version
        $this->assertEquals($worksheet->date_closed, $prodBean->fetched_row['date_closed']);

        // test the the opp to make sure it's updated as well
        unset($oppBean);
        /* @var $oppBean Opportunity */
        $oppBean = BeanFactory::newBean($opp->module_name);
        $oppBean->retrieve($opp->id);

        // test the db formatted version
        $this->assertEquals($worksheet->date_closed, $oppBean->fetched_row['date_closed']);
    }

    /**
     * @dataProvider dataProviderSaveWorksheetUpdatesBeanValues
     * @group forecasts
     * @param $field
     * @param $start_value
     * @param $updated_value
     */
    public function testSaveWorksheetUpdatesBeanValues($field, $start_value, $updated_value)
    {
        $this->markTestIncomplete('Failing on PRO. Need to be fixed by SFA team');
        $product = SugarTestProductUtilities::createProduct();
        $product->$field = $start_value;
        $product->save();

        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($product);

        $worksheet->$field = $updated_value;
        $worksheet->saveWorksheet();

        //load the product and test to see if it's the updated value
        $prodBean = BeanFactory::newBean($product->module_name);
        $prodBean->retrieve($product->id);

        $this->assertEquals($updated_value, $prodBean->fetched_row[$field]);
    }

    public function dataProviderSaveWorksheetUpdatesBeanValues()
    {
        return array(
            array('likely_case', '1000', '1500'),
            array('best_case', '1000', '1500'),
            array('worst_case', '1000', '1500'),
            array('probability', '50', '80'),
            array('date_closed', '2009-02-01', '2009-03-01'),
            array('commit_stage', 'include', 'exclude'),
            array('sales_stage', 'test1', 'test2')
        );
    }
    
    public function dataProviderTimePeriodsHasMigrated() {
        return array(
            array(null, null, false),                   // invalid dates
            array('2013-02-01', null, false),           // one invalid date
            array(null, '2013-02-01', false),           // one invalid date
            array('2013-02-01', '2013-02-01', false),    // equal dates
            array('2013-02-01', '2013-05-01', true)    // equal dates
        );
    }
    
    /**
     * @group forecasts
     * @dataProvider dataProviderTimePeriodsHasMigrated
     */
    public function testTimeperiodHasMigrated($date1, $date2, $expectedReturn)
    {
        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('2013-01-01', '2013-03-31');
        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('2013-04-01', '2013-06-30');
        $worksheet = BeanFactory::newBean("ForecastWorksheets");                    
        
        $hasMigrated = SugarTestReflection::callProtectedMethod($worksheet, 'timeperiodHasMigrated', array($date1, $date2));
        $this->assertEquals($expectedReturn, $hasMigrated);        
    }   

    /**
     * @group forecasts
     */
    public function testTimeperiodMigratedDeleteCommittedOpp()
    {
        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('2013-01-01', '2013-03-31');
        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('2013-04-01', '2013-06-30');
        
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->date_closed = '2013-01-01';
        $opp->save();
        $opp->retrieve($opp->id);
        
        $product = SugarTestProductUtilities::createProduct();
        $product->opportunity_id = $opp->id;
        $product->date_closed = '2013-01-01';
        $product->save();
        
        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($opp);
        $worksheet2 = SugarTestWorksheetUtilities::loadWorksheetForBean($opp);
        $worksheet2->id = "";
        $worksheet2->draft = 0;
        $worksheet2->save();
                
        $worksheet->date_closed = '2013-05-01';
        $worksheet->save();
        
        $worksheet->saveRelatedOpportunity($opp);
        $worksheet2 = BeanFactory::getBean("ForecastWorksheets", $worksheet2->id);
                
        $this->assertEquals(1, $worksheet2->deleted);        
    }
    
    /**
     * @group forecasts
     */
    public function testTimeperiodMigratedDeleteCommittedProduct()
    {
        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('2013-01-01', '2013-03-31');
        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('2013-04-01', '2013-06-30');
        
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->date_closed = '2013-01-01';
        $opp->save();
        
        $product = SugarTestProductUtilities::createProduct();
        $product->opportunity_id = $opp->id;
        $product->date_closed = '2013-01-01';
        $product->save();
        $product->retrieve($product->id);
        
        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($product);
        $worksheet2 = SugarTestWorksheetUtilities::loadWorksheetForBean($product);
        $worksheet2->id = "";
        $worksheet2->draft = 0;
        $worksheet2->save();
               
        $worksheet->date_closed = '2013-05-01';
        $worksheet->save();
        
        $worksheet->saveRelatedProduct($product);
        $worksheet2 = BeanFactory::getBean("ForecastWorksheets", $worksheet2->id);
        
        $this->assertEquals(1, $worksheet2->deleted);        
    }

    /**
     * @group forecasts
     */
    public function testDeleteProductMarksDraftWorksheetRecordAsDeleted()
    {
        SugarTestTimePeriodUtilities::createTimePeriod('2013-01-01', '2013-03-31');

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->date_closed = '2013-01-01';
        $opp->save();

        $product = SugarTestProductUtilities::createProduct();
        $product->opportunity_id = $opp->id;
        $product->date_closed = '2013-01-01';
        $product->save();

        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($product);

        // assert that worksheet is not deleted
        $this->assertEquals(0, $worksheet->deleted);

        // delete the product
        $product->deleted = 1;
        $product->save();

        $this->assertEquals(1, $product->deleted);

        // fetch the worksheet again
        unset($worksheet);
        $worksheet = SugarTestWorksheetUtilities::loadWorksheetForBean($product, false, true);
        $this->assertEquals(1, $worksheet->deleted);
    }

    public function testGetAccountNameReturnsEmpty()
    {

    }
}
