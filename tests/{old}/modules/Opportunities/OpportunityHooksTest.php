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


class OpportunityHooksTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestForecastUtilities::setUpForecastConfig(array(
            'sales_stage_won' => array('Closed Won'),
            'sales_stage_lost' => array('Closed Lost')
        ));
    }

    public function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestHelper::tearDown();
    }

    public function dataProviderSetOpportunitySalesStatus()
    {
        // utility method to to return an array
        $count_to_array = function ($count) {
            return array_pad(array(), $count, '-');
        };

        // # of won, # of lost, #total, #status
        return array(
            // all closed_won
            array($count_to_array(2), $count_to_array(0), $count_to_array(2), Opportunity::STATUS_CLOSED_WON),
            // closed won and closed lost
            array($count_to_array(2), $count_to_array(2), $count_to_array(4), Opportunity::STATUS_CLOSED_WON),
            // all closed lost
            array($count_to_array(0), $count_to_array(2), $count_to_array(2), Opportunity::STATUS_CLOSED_LOST),
            // only closed lost but higher total
            array($count_to_array(0), $count_to_array(2), $count_to_array(4), Opportunity::STATUS_IN_PROGRESS),
            // only cosed won but higher total
            array($count_to_array(2), $count_to_array(0), $count_to_array(4), Opportunity::STATUS_IN_PROGRESS),
            // no closed won or lost but still a total
            array($count_to_array(0), $count_to_array(0), $count_to_array(4), Opportunity::STATUS_IN_PROGRESS),
            // no closed won, closed lost and total
            array($count_to_array(0), $count_to_array(0), $count_to_array(0), Opportunity::STATUS_NEW),
        );
    }

    /**
     * @group opportunities
     */
    public function testSetOpportunitySalesStatusOnNewOpp()
    {
        $oppMock = $this->createPartialMock('Opportunity', array('get_linked_beans', 'save', 'retrieve'));

        /* @var $hookMock OpportunityHooks */
        $hookMock = new MockOpportunityHooks();
        $hookMock::$useRevenueLineItems = true;

        $hookMock::setSalesStatus($oppMock, 'before_save', array());

        // assert the status is what it should be
        $this->assertEquals($oppMock->sales_status, Opportunity::STATUS_NEW);
    }

    /**
     * @dataProvider dataProviderSetOpportunitySalesStatus
     * @group opportunities
     * @group revenuelineitems
     */
    public function testSetOpportunitySalesStatusWithAccess($won_count, $lost_count, $total_count, $status)
    {
        $oppMock = $this->createPartialMock('Opportunity', array('get_linked_beans', 'save', 'retrieve', 'ACLFieldAccess'));
        $oppMock->id = 'test';
        $oppMock->fetched_row['id'] = 'test';

        /* @var $hookMock OpportunityHooks */
        $hookMock = new MockOpportunityHooks();
        $hookMock::$useRevenueLineItems = true;

        $closed_won = array('won');
        $closed_lost = array('lost');

        $hr = new ReflectionClass($hookMock);
        $hr->setStaticPropertyValue(
            'settings',
            array(
                'is_setup' => 1,
                'sales_stage_won' => $closed_won,
                'sales_stage_lost' => $closed_lost
            )
        );

        // generate a map for the get_linked_beans call, the first 7 params are for the method call
        // the final param, it what gets returned  this is used below
        $map = array(
            array(
                'revenuelineitems',
                'RevenueLineItems',
                array(),
                0,
                -1,
                0,
                "sales_stage in ('" . join("', '", $closed_won) . "')",
                $won_count
            ),
            array(
                'revenuelineitems',
                'RevenueLineItems',
                array(),
                0,
                -1,
                0,
                "sales_stage in ('" . join("', '", $closed_lost) . "')",
                $lost_count
            ),
            array(
                'revenuelineitems',
                'RevenueLineItems',
                array(),
                0,
                -1,
                0,
                '',
                $total_count
            )
        );

        // we want to run get_linked_bean 3 times. each time will iterate though the $map and return the lats param
        // this is the magic of ->will($this->returnValueMap($map));
        $oppMock->expects($this->exactly(3))
            ->method('get_linked_beans')
            ->will($this->returnValueMap($map));

        $oppMock->expects($this->any())
            ->method('ACLFieldAccess')
            ->will($this->returnValue(true));

        $hookMock::setSalesStatus($oppMock, 'before_save', array());

        // assert the status is what it should be
        $this->assertEquals($oppMock->sales_status, $status);
    }

    public function testSetOpportunitySalesStatusWithoutAccess()
    {
        $oppMock = $this->createPartialMock('Opportunity', array('get_linked_beans', 'save', 'retrieve', 'ACLFieldAccess'));

        /* @var $hookMock OpportunityHooks */
        $hookMock = new MockOpportunityHooks();

        $closed_won = array('won');
        $closed_lost = array('lost');

        $hr = new ReflectionClass($hookMock);
        $hr->setStaticPropertyValue(
            'settings',
            array(
                'is_setup' => 1,
                'sales_stage_won' => $closed_won,
                'sales_stage_lost' => $closed_lost
            )
        );

        $oppMock->expects($this->any())
            ->method('ACLFieldAccess')
            ->will($this->returnValue(false));

        $oppMock->sales_status = 'testing1';

        $hookMock::setSalesStatus($oppMock, 'before_save', array());

        // assert the status is what it should be
        $this->assertEquals('testing1', $oppMock->sales_status);
    }

    /**
     * @dataProvider beforeSaveIncludedCheckProvider
     */
    public function testBeforeSaveIncludedCheck($sales_stage, $commit_stage, $probability, $expected)
    {
        $hookMock = new MockOpportunityHooks();
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $opp->probability = $probability;
        $opp->sales_stage = $sales_stage;
        $opp->commit_stage = $commit_stage;

        $hookMock->beforeSaveIncludedCheck($opp, 'before_save', null);
        $this->assertEquals($opp->commit_stage, $expected);
    }

    public function beforeSaveIncludedCheckProvider()
    {
        return array(
            array('Closed Won', 'exclude', 100, 'include'),
            array('Closed Lost', 'include', 0, 'exclude')
        );
    }

    /**
     * @dataProvider fixWorksheetAccountAssignmentProvider
     */
    public function testfixWorksheetAccountAssignment($useRlis, $useForecast, $args, $result)
    {
        $hookMock = new MockOpportunityHooks();
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $hookMock::$bypassSaveWorksheet = true;
        $hookMock::$useRevenueLineItems = $useRlis;
        $hookMock::$forecastIsSetup = $useForecast;

        $returnVal = $hookMock::fixWorksheetAccountAssignment($opp, '', $args);
        $this->assertEquals($returnVal, $result);
    }

    public function fixWorksheetAccountAssignmentProvider()
    {
        return array(
            array(false, true, array('relationship' => 'accounts_opportunities', 'related_id' => 'foo'), true),
            array(true, true, array('relationship' => 'accounts_opportunities'), false),
            array(true, false, array('relationship' => 'accounts_opportunities'), false),
            array(false, false, array('relationship' => 'accounts_opportunities'), false),
            array(true, true, array('relationship' => 'foo'), false),
            array(false, true, array('relationship' => 'foo'), false),
            array(true, false, array('relationship' => 'foo'), false),
            array(false, false, array('relationship' => 'foo'), false),
            array(true, true, array(), false),
            array(false, true, array(), false),
            array(true, false, array(), false),
            array(false, false, array(), false),
            array(true, true, null, false),
            array(false, true, null, false),
            array(true, false, null, false),
            array(false, false, null, false),
        );
    }
}

class MockOpportunityHooks extends OpportunityHooks
{
    public static $useRevenueLineItems = false;
    public static $forecastIsSetup = true;
    public static $bypassSaveWorksheet = false;

    public static function useRevenueLineItems()
    {
        return self::$useRevenueLineItems;
    }


    public static function isForecastSetup()
    {
        return self::$forecastIsSetup;
    }

    public static function saveWorksheet(Opportunity $bean, $event, $args)
    {
        if (self::$bypassSaveWorksheet) {
            return true;
        } else {
            return parent::saveWorksheet($bean, $event, $args);
        }
    }
}
