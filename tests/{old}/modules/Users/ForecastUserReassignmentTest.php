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


//require_once('modules/Forecasts/WorksheetSeedData.php');

/**
 * nutmeg:sfa-219
 * Fix reassignment of records when user set to Inactive
 *
 * @ticket sfa-219
 */
class ForecastUserReassignmentTest extends  Sugar_PHPUnit_Framework_TestCase
{
    private $_users;
    private $_users_ids;
    private $_users_opps;
    private $_users_worksheets_count;
    private $_timeperiod;
    private static $_isSetup;

    /**
     * create user
     *
     * @param $user String id of the user to create
     * @param $report_user String id of the user the created user should report to (defaults to null)
     */
    private function _createUser($user, $report_user = null)
    {
        $this->_users[$user] = SugarTestUserUtilities::createAnonymousUser($save = false, $is_admin=0);
        $this->_users[$user]->id = create_guid();
        $this->_users[$user]->new_with_id = true;
        $this->_users[$user]->user_name = $user;
        $this->_users[$user]->first_name = $user;
        $this->_users[$user]->reports_to_id = $report_user && isset($this->_users[$report_user]) ?  $this->_users[$report_user]->id : null;
        $this->_users[$user]->save();
        $this->_users_ids[] = $this->_users[$user]->id;
    }

    /**
     * create opportunities for the user
     *
     * @param $user String id of the user to create opportunities for
     * @param $count int value for number of opportunities to create
     */
    private function _createOpportunityForUser($user, $count)
    {
        for ( $i = 0; $i < $count; $i++ )
        {
            $opp = SugarTestOpportunityUtilities::createOpportunity();
            $opp->assigned_user_id = $this->_users[$user]->id;
            $opp->save();
            $this->_users_opps[$user][] = $opp;
        }

    }

    /**
     * return count of opportunities for user
     *
     * @param $user
     * @return int
     */
    private function _getOpportunitiesCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM opportunities WHERE assigned_user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];
    }

    /**
     * return count of products for user
     *
     * @param $user
     * @return int
     */
    private function _getProductsCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM products WHERE assigned_user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];
    }

    /**
     * return count of worksheets for user
     *
     * @param $user
     * @return int
     */
    private function _getWorksheetsCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM worksheet WHERE user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];
    }

    /**
     * return count of forecasts for user
     *
     * @param $user
     * @return int
     */
    private function _getForecastsCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM forecasts WHERE user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];
    }

    /**
     * return count of quotas for user
     *
     * @param $user
     * @return int
     */
    private function _getQuotasCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM quotas WHERE user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];
    }

    /**
     * return information about user's reportees
     * @param $user
     * @return array
     */
    private function getReporteeCountForUser($user)
    {
        $userID = $this->_users[$user]->id;

        $children = User::getReporteesWithLeafCount($userID);

        return count($children);
    }

    /**
     * call action reassignUserRecords
     * @param $fromUser
     * @param $toUser
     */
    private function _doReassign($fromUser, $toUser)
    {
        $_SESSION['reassignRecords'] = array();
        $_SESSION['reassignRecords']['assignedModuleListCache'] = array('ForecastWorksheets' => 'ForecastWorksheets');
        $_SESSION['reassignRecords']['assignedModuleListCacheDisp'] = array ('ForecastWorksheets' => 'ForecastWorksheet');

        $_POST = $_GET = array();
        $_POST['module'] = 'Users';
        $_POST['action'] = 'reassignUserRecords';
        $_POST['fromuser'] = $this->_users[$fromUser]->id;
        $_POST['touser'] = $this->_users[$toUser]->id;
        $_POST['modules'] = array('ForecastWorksheets');
        $_POST['steponesubmit'] = 'Next';

        unset($_GET['execute']);

        global $app_list_strings, $beanFiles, $beanList, $current_user, $mod_strings, $app_strings;
        include('modules/Users/reassignUserRecords.php');

        $_GET['execute'] = true;
        include('modules/Users/reassignUserRecords.php');
    }
    /*
        public static function setUpBeforeClass() {
            SugarTestHelper::setUp('beanList');
            SugarTestHelper::setUp('beanFiles');
            SugarTestHelper::setUp('app_list_strings');
            SugarTestHelper::setUp('app_strings');
            SugarTestHelper::setUp('mod_strings', array('Users'));
            SugarTestForecastUtilities::setUpForecastConfig();
        }
    */
    public function setUp()
    {
        $this->markTestIncomplete('needs to be refactored since WorksheetSeedData has been removed');
        /*
        //This reporting structure mimics our seed data hierarchy
        $this->_createUser('jim');
        $this->_createUser('sarah', 'jim');
        $this->_createUser('will', 'jim');
        $this->_createUser('sally', 'sarah');
        $this->_createUser('max', 'sarah');
        $this->_createUser('chris', 'will');
        SugarTestHelper::setUp('current_user', array(true, 1));
        global $current_user;
        //Set the type to be of UserAdministrator
        $current_user->userType = 'UserAdministrator';
        $this->_timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();
        */
    }
    /*
        public function tearDown()
        {
            SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
            SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
            SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
            SugarTestWorksheetUtilities::removeAllCreatedWorksheets();

            $db = DBManagerFactory::getInstance();
            $db->query("DELETE FROM forecasts WHERE timeperiod_id = '{$this->_timeperiod->id}'");
            $db->query("DELETE FROM quotas WHERE timeperiod_id = '{$this->_timeperiod->id}'");

            unset($_SESSION['reassignRecords']);
            $postVars = array('module', 'action', 'fromuser', 'touser', 'modules', 'steponesubmit');
            foreach($postVars as $key)
            {
                unset($_POST[$key]);
            }

            unset($this->_users, $this->_users_ids, $this->_users_opps, $this->_users_worksheets_count, $this->_timeperiod);
        }

        public static function tearDownAfterClass() {
            SugarTestForecastUtilities::tearDownForecastConfig();
            SugarTestHelper::tearDown();
        }
    */
    /**
     * test reassignment rep to rep
     * @group user_reassignment
     * @group forecast
     * @outputBuffering enabled
     */
    public function testReassignRepToRep()
    {
        $this->markTestIncomplete('Failing. Need to be fixed by SFA team, Currently its setup to work in PRO but not ENT. Should be fixed with SFA-687');
        //Create 10 opportunities for sally
        $this->_createOpportunityForUser('sally', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );

        //Create worksheet entries using WorksheetSeedData class
        $worksheets_ids = WorksheetSeedData::populateSeedData();
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);

        //Assert that 10 opportunities and 10 products are created for sally
        $count = $this->_getOpportunitiesCountForUser('sally');
        $this->assertEquals(10, $count);
        $count = $this->_getProductsCountForUser('sally');
        $this->assertEquals(10, $count);

        $expected['worksheets'] = $this->_getWorksheetsCountForUser('sally');
        $expected['opportunities'] = sizeof($this->_users_opps['sally']);

        //Now simulate the reassignment from sally to chris
        $this->_doReassign('sally', 'chris');

        // from sally
        $count = $this->_getOpportunitiesCountForUser('sally');
        $this->assertEquals(0, $count, 'Opportunities are not reassigned.');
        $count = $this->_getProductsCountForUser('sally');
        $this->assertEquals(0, $count, 'Products are not reassigned.');
        $count = $this->_getWorksheetsCountForUser('sally');
        $this->assertEquals(0, $count, 'Worksheets are not reassigned.');
        $count = $this->_getForecastsCountForUser('sally');
        $this->assertEquals(0, $count, 'Forecasts are not deleted.');
        $count = $this->_getQuotasCountForUser('sally');
        $this->assertEquals(0, $count, 'Quotas are not deleted.');

        // check that the opportunities, products and worksheet entries have been assigned to chris
        $count = $this->_getOpportunitiesCountForUser('chris');
        $this->assertEquals($expected['opportunities'], $count, 'Opportunities are not reassigned.');
        $count = $this->_getProductsCountForUser('chris');
        $this->assertEquals($expected['opportunities'], $count, 'Products are not reassigned.');
        $count = $this->_getWorksheetsCountForUser('chris');
        $this->assertEquals($expected['worksheets'], $count, 'Worksheets are not reassigned.');
    }

    /**
     * test reassignment manager to manager
     * @group user_reassignment
     * @group forecasts
     * @outputBuffering enabled
     */
    public function testReassignManagerToManager()
    {
        $this->markTestSkipped("Skipping as this doesn't work.  Should be fixed with SFA-687");
        $this->_createOpportunityForUser('sarah', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        $worksheets_ids = WorksheetSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);

        $count = $this->_getOpportunitiesCountForUser('sarah');
        $this->assertEquals(10, $count);
        $count = $this->_getProductsCountForUser('sarah');
        $this->assertEquals(10, $count);

        // subtract 1 from the total count because of the rollup entry created for sarah's manager (jim)
        $expected['worksheets'] = $this->_getWorksheetsCountForUser('sarah') - 1;
        $expected['opportunities'] = sizeof($this->_users_opps['sarah']);

        $this->_doReassign('sarah', 'will');

        // from sarah
        $count = $this->_getOpportunitiesCountForUser('sarah');
        $this->assertEquals(0, $count, 'Opportunities are not reassigned.');
        $count = $this->_getProductsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Products are not reassigned.');
        $count = $this->_getWorksheetsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Worksheets are not reassigned.');
        $count = $this->_getForecastsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Forecasts are not deleted.');
        $count = $this->_getQuotasCountForUser('sarah');
        $this->assertEquals(0, $count, 'Quotas are not deleted.');

        // check that the opportunities, products and worksheet entries have been assigned to will
        $count = $this->_getOpportunitiesCountForUser('will');
        $this->assertEquals($expected['opportunities'], $count, 'Opportunities are not reassigned.');
        $count = $this->_getProductsCountForUser('will');
        $this->assertEquals($expected['opportunities'], $count, 'Products are not reassigned.');
        $count = $this->_getWorksheetsCountForUser('will');
        $this->assertEquals($expected['worksheets'], $count, 'Worksheets are not reassigned.');
    }

    /**
     * test reassignment manager to rep
     * @group user_reassignment
     * @group forecast
     * @outputBuffering enabled
     */
    public function testReassignManagerToRep()
    {
        $this->markTestSkipped("Skipping as this doesn't work.  Should be fixed with SFA-687");
        $this->_createOpportunityForUser('sarah', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        $worksheets_ids = WorksheetSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);

        $count = $this->_getOpportunitiesCountForUser('sarah');
        $this->assertEquals(10, $count);
        $count = $this->_getProductsCountForUser('sarah');
        $this->assertEquals(10, $count);

        // subtract 1 from the total count because of the rollup entry created for sarah's manager (jim)
        $expected['worksheets'] = $this->_getWorksheetsCountForUser('sarah') - 1;
        $expected['opportunities'] = sizeof($this->_users_opps['sarah']);

        $this->_doReassign('sarah', 'sally');

        // from sarah
        $count = $this->_getOpportunitiesCountForUser('sarah');
        $this->assertEquals(0, $count, 'Opportunities are not reassigned.');
        $count = $this->_getProductsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Products are not reassigned.');
        $count = $this->_getWorksheetsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Worksheets are not reassigned.');
        $count = $this->_getForecastsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Forecasts are not deleted.');
        $count = $this->_getQuotasCountForUser('sarah');
        $this->assertEquals(0, $count, 'Quotas are not deleted.');

        // to will
        $count = $this->_getOpportunitiesCountForUser('sally');
        $this->assertEquals($expected['opportunities'], $count, 'Opportunities are not reassigned.');
        $count = $this->_getProductsCountForUser('sally');
        $this->assertEquals($expected['opportunities'], $count, 'Products are not reassigned.');
        $count = $this->_getWorksheetsCountForUser('sally');
        $this->assertEquals($expected['worksheets'], $count, 'Worksheets are not reassigned.');

        $objSally = new User();
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEquals($this->_users['sarah']->reports_to_id, $objSally->reports_to_id );
    }


    /**
     * Placeholder for now.  Not sure if we need to support Opportunities filtering and pass along filters
    public function testReassignRepToRepUsingFilters()
    {
        //Create 10 opportunities for sally
        $this->_createOpportunityForUser('sally', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );

        //Create worksheet entries using WorksheetSeedData class
        $worksheets_ids = WorksheetSeedData::populateSeedData();
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);

        $count = 0;
        $newWonCount = 0;
        foreach($this->_users_opps['sally'] as $opportunity)
        {
            $even = ($count++ % 2) == 0;
            if($even) {
                $newWonCount++;
            }
            $opportunity->opportunity_type = $even ? 'New Business' : 'Existing Business';
            $opportunity->sales_stage = $even ? 'Closed Won' : 'Closed Lost';
            $opportunity->save();
        }

        $expected['worksheets'] = $this->_getWorksheetsCountForUser('sally');
        $expected['opportunities'] = sizeof($this->_users_opps['sally']);

        $additionalParams = array(
            'modules' => array ( 'Opportunity' => array ( 'query' => 'select id from opportunities where opportunities.deleted=0 and opportunities.assigned_user_id = \'seed_chris_id\' and (opportunities.sales_stage in (\'Qualification\') ) and (opportunities.opportunity_type in (\'Existing Business\') )', 'update' => 'update opportunities set assigned_user_id = \'seed_sally_id\', date_modified = \'2012-10-17 19:43:24\', modified_user_id = \'1\' , team_id = \'1\', team_set_id = \'1\' where opportunities.deleted=0 and opportunities.assigned_user_id = \'seed_chris_id\' and (opportunities.sales_stage in (\'Qualification\') ) and (opportunities.opportunity_type in (\'Existing Business\') )', ), ), )
        );


        $_SESSION['reassignRecords'] ...
        $_SESSION['reassignRecords'] ...
        //Now simulate the reassignment from sally to chris
        $this->_doReassign('sally', 'chris');
    }
    */

    /**
     * test user's reportees if some user became inactive
     * @group user_reassignment
     * @group forecast
     */
    public function testInactiveChildren()
    {
        $this->_createOpportunityForUser('sarah', 10);

        $childCount = $this->getReporteeCountForUser('sarah');
        $this->assertEquals(2, $childCount); // sally & max

        $this->_users['sally']->status = 'Inactive';
        $this->_users['sally']->save();
        $childCount = $this->getReporteeCountForUser('sarah');
        $this->assertEquals(1, $childCount); // max

        $this->_users['max']->status = 'Inactive';
        $this->_users['max']->save();
        $childCount = $this->getReporteeCountForUser('sarah');
        $this->assertEquals(0, $childCount);
    }

    /**
     * test a user's reportees if some user is set to be deleted
     * @group user_reassignment
     * @group forecast
     */
    public function testDeletedChildren()
    {
        $this->_createOpportunityForUser('sarah', 10);

        $childCount = $this->getReporteeCountForUser('sarah');
        $this->assertEquals(2, $childCount); // sally, max

        $this->_users['sally']->deleted = 1;
        $this->_users['sally']->save();
        $childCount = $this->getReporteeCountForUser('sarah');
        $this->assertEquals(1, $childCount); // max

        $this->_users['max']->deleted = 1;
        $this->_users['max']->save();
        $childCount = $this->getReporteeCountForUser('sarah');
        $this->assertEquals(0, $childCount);
    }

    /**
     * test worksheets after reassignment rep to rep
     * @group user_reassignment
     * @group forecasts
     */
    public function testWorksheetRepToRep()
    {
        $this->markTestSkipped("Skipping as this doesn't work.  Should be fixed with SFA-687");
        $this->_createOpportunityForUser('sally', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        $worksheets_ids = WorksheetSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);


        global $current_user;
        $this->_users['sally']->is_admin = true;
        $this->_users['chris']->is_admin = true;
        $current_user = $this->_users['sally'];
        $api = new SugarForecasting_Individual( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['sally']->id) );
        $result = $api->process();
        $this->assertEquals(10, sizeof($result));

        $this->_doReassign('sally', 'chris');

        $current_user = $this->_users['chris'];
        $api = new SugarForecasting_Individual( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['chris']->id) );
        $result = $api->process();
        $this->assertEquals(10, sizeof($result));

        $current_user = $this->_users['sally'];
        $api = new SugarForecasting_Individual( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['sally']->id) );
        $result = $api->process();
        $this->assertEquals(0, sizeof($result));

    }

    /**
     * test worksheets after reassignment manager to manager
     * @group user_reassignment
     * @group forecasts
     */
    public function testWorksheetManagerToManager()
    {
        $this->markTestSkipped('This is skipped as this needs to point');
        $this->_createOpportunityForUser('sarah', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        $worksheets_ids = WorksheetSeedData::populateSeedData();
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);


        $api = new SugarForecasting_Manager( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['sarah']->id) );
        $result = $api->process();
        $this->assertEquals(3, sizeof($result)); // 3 sarah's opps + sally + max

        $this->_doReassign('sarah', 'will');

        $api = new SugarForecasting_Manager( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['will']->id) );
        $result = $api->process();
        $this->assertEquals(4, sizeof($result)); // 4 = will's opps + chris + (sally + max)

        $api = new SugarForecasting_Manager( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['sarah']->id) );
        $result = $api->process();
        $this->assertEquals(0, sizeof($result)); // this is really 0 since she it a manger any more
        // this is to suppress output. Need to fix properly with a good unit test.
        $this->expectOutputRegex('//');
    }

    /**
     * This is a test to move a manager's data (sarah) to a reportees (sally)
     * @group user_reassignment
     * @group forecasts
     */
    public function testReportsToSarahToSally()
    {
        $this->_doReassign('sarah', 'sally');

        $objJim = BeanFactory::newBean('Users');
        $objJim->retrieve($this->_users['jim']->id);
        $this->assertEmpty($objJim->reports_to_id, 'Jim report_to_id is not empty');

        $objSarah = BeanFactory::newBean('Users');
        $objSarah->retrieve($this->_users['sarah']->id);
        $this->assertEmpty($objSarah->reports_to_id, 'Sarah report_to_id is not empty');

        $objSally = BeanFactory::newBean('Users');
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEquals($this->_users['jim']->id, $objSally->reports_to_id, 'Sally does not report to Jim');

        $objMax = BeanFactory::newBean('Users');
        $objMax->retrieve($this->_users['max']->id);
        $this->assertEquals($this->_users['sally']->id, $objMax->reports_to_id, 'Max does not report to Sally');

        $objWill = BeanFactory::newBean('Users');
        $objWill->retrieve($this->_users['will']->id);
        $this->assertEquals($this->_users['jim']->id, $objWill->reports_to_id, 'Will does not report to Jim');

        $objChris = BeanFactory::newBean('Users');
        $objChris->retrieve($this->_users['chris']->id);
        $this->assertEquals($this->_users['will']->id, $objChris->reports_to_id, 'Chris does not report to Will');
        // this is to suppress output. Need to fix properly with a good unit test.
        $this->expectOutputRegex('//');
    }

    /**
     * This is a test to check the reporting structure changes when jim (top level manager) has his data reassigned to
     * sally (a reportee)
     *
     * @group user_reassignment
     * @group forecasts
     */
    public function testReportsToJimToSally()
    {
        $this->_doReassign('jim', 'sally');

        $objJim = BeanFactory::newBean('Users');
        $objJim->retrieve($this->_users['jim']->id);
        $this->assertEmpty($objJim->reports_to_id, 'Jim report_to_id is not empty');

        $objSally = BeanFactory::newBean('Users');
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEmpty($objSally->reports_to_id, 'Sally report_to_id is not empty');

        $objSarah = BeanFactory::newBean('Users');
        $objSarah->retrieve($this->_users['sarah']->id);
        $this->assertEquals($this->_users['sally']->id, $objSarah->reports_to_id, 'Sarah does not report to Sally');

        $objMax = BeanFactory::newBean('Users');
        $objMax->retrieve($this->_users['max']->id);
        $this->assertEquals($this->_users['sarah']->id, $objMax->reports_to_id, 'Max does not report to Sarah');

        $objWill = BeanFactory::newBean('Users');
        $objWill->retrieve($this->_users['will']->id);
        $this->assertEquals($this->_users['sally']->id, $objWill->reports_to_id, 'Will does not report to Sally');

        $objChris = BeanFactory::newBean('Users');
        $objChris->retrieve($this->_users['chris']->id);
        $this->assertEquals($this->_users['will']->id, $objChris->reports_to_id, 'Chris does not report to Will');
        // this is to suppress output. Need to fix properly with a good unit test.
        $this->expectOutputRegex('//');
    }

    /**
     * This is a test to check the reporting structure when sally (a reportee) has her data reassigned to chris (another reportee)
     * @group user_reassignment
     * @group forecasts
     */
    public function testReportsToSallyToChris()
    {
        $this->_doReassign('sally', 'chris');

        $objSally = BeanFactory::newBean('Users');
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEmpty($objSally->reports_to_id, 'Sally report_to_id is not empty');

        $objChris = BeanFactory::newBean('Users');
        $objChris->retrieve($this->_users['chris']->id);
        $this->assertEquals($this->_users['will']->id, $objChris->reports_to_id, 'Chris does not report to Will');

        $objWill = BeanFactory::newBean('Users');
        $objWill->retrieve($this->_users['will']->id);
        $this->assertEquals($this->_users['jim']->id, $objWill->reports_to_id, 'Will does not report to Jim');

        $objMax = BeanFactory::newBean('Users');
        $objMax->retrieve($this->_users['max']->id);
        $this->assertEquals($this->_users['sarah']->id, $objMax->reports_to_id, 'Max does not report to Sarah');

        $objSarah = BeanFactory::newBean('Users');
        $objSarah->retrieve($this->_users['sarah']->id);
        $this->assertEquals($this->_users['jim']->id, $objSarah->reports_to_id, 'Sarah does not report to Jim');
        // this is to suppress output. Need to fix properly with a good unit test.
        $this->expectOutputRegex('//');
    }

    /**
     * This is a test to move two manager's data (sarah and jim) to a reportee (sally)
     * @group user_reassignment
     * @group forecasts
     */
    public function testReportsToSarahToSallyAndThenJimToSally()
    {
        $this->_doReassign('sarah', 'sally');
        $this->_doReassign('jim', 'sally');

        $objSarah = BeanFactory::newBean('Users');
        $objSarah->retrieve($this->_users['sarah']->id);
        $this->assertEmpty($objSarah->reports_to_id, 'Sarah report_to_id is not empty');

        $objJim = BeanFactory::newBean('Users');
        $objJim->retrieve($this->_users['jim']->id);
        $this->assertEmpty($objJim->reports_to_id, 'Jim report_to_id is not empty');

        $objSally = BeanFactory::newBean('Users');
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEmpty($objSally->reports_to_id, 'Sally report_to_id is not empty');

        $objMax = BeanFactory::newBean('Users');
        $objMax->retrieve($this->_users['max']->id);
        $this->assertEquals($this->_users['sally']->id, $objMax->reports_to_id, 'Max does not report to Sally');
        // this is to suppress output. Need to fix properly with a good unit test.
        $this->expectOutputRegex('//');
    }


    /**
     * This is a test to move data between managers (sarah and will)
     * @group user_reassignment
     * @group forecasts
     */
    public function testReportsToSarahToWill()
    {
        $this->_doReassign('sarah', 'will');

        $objSarah = BeanFactory::newBean('Users');
        $objSarah->retrieve($this->_users['sarah']->id);
        $this->assertEmpty($objSarah->reports_to_id, 'Sarah report_to_id is not empty');

        $objSally = BeanFactory::newBean('Users');
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEquals($this->_users['will']->id, $objSally->reports_to_id, 'Sally does not report to Will');

        $objMax = BeanFactory::newBean('Users');
        $objMax->retrieve($this->_users['max']->id);
        $this->assertEquals($this->_users['will']->id, $objMax->reports_to_id, 'Sally does not report to Will');

        $objChris = BeanFactory::newBean('Users');
        $objChris->retrieve($this->_users['chris']->id);
        $this->assertEquals($this->_users['will']->id, $objChris->reports_to_id, 'Chris does not report to Will');

        $objWill = BeanFactory::newBean('Users');
        $objWill->retrieve($this->_users['will']->id);
        $this->assertEquals($this->_users['jim']->id, $objWill->reports_to_id, 'Will does not report to Jim');
        // this is to suppress output. Need to fix properly with a good unit test.
        $this->expectOutputRegex('//');
    }
}
