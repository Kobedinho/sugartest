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

require_once 'SugarTestForecastUtilities.php';

/**
 * Tests RevenueLineItemsGlobeChartApiTest.
 */
class RevenueLineItemsGlobeChartApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarApi
     */
    protected $api;

    /**
     * @var User
     */
    protected $current_user;

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $this->current_user = SugarTestHelper::setUp('current_user', array(true, false));
        $this->api = new RevenueLineItemsGlobeChartApi();
    }

    protected function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testSalesByCountry()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $account->billing_address_country = 'TestCountryName';
        $account->billing_address_state = 'TestStateName';
        $account->save();

        $opp = SugarTestOpportunityUtilities::createOpportunity(null, $account);
        $opp->teams->replace(array($this->current_user->team_id));
        $opp->save();

        $rli1 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli1->opportunity_id  = $opp->id;
        $rli1->sales_stage = 'Closed Won';
        $rli1->teams->replace(array($this->current_user->team_id));
        $rli1->save();

        $rli2 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli2->opportunity_id  = $opp->id;
        $rli2->sales_stage = 'Closed Won';
        $rli2->teams->replace(array($this->current_user->team_id));
        $rli2->save();

        $result = $this->api->salesByCountry(
            SugarTestRestUtilities::getRestServiceMock($this->current_user),
            array('module' => 'RevenueLineItems')
        );

        $this->assertArrayHasKey('TestCountryName', $result);

        $countryGroup = $result['TestCountryName'];
        $this->assertArrayHasKey('TestStateName', $countryGroup);
        $this->assertArrayHasKey('_total', $countryGroup);

        $stateGroup = $countryGroup['TestStateName'];
        $this->assertArrayHasKey('_total', $stateGroup);
    }

}
