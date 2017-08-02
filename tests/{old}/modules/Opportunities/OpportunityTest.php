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

/**
 * Class OpportunityTest
 * @coversDefaultClass Opportunity
 */
class OpportunityTest extends Sugar_PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestCurrencyUtilities::createCurrency('MonkeyDollars', '$', 'MOD', 2.0);

        SugarTestForecastUtilities::setUpForecastConfig(array(
                'sales_stage_won' => array('Closed Won'),
                'sales_stage_lost' => array('Closed Lost'),
            ));
    }

    public function tearDown()
    {
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestHelper::tearDown();
    }

    public function dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty()
    {
        return array(array('best_case'), array('worst_case'));
    }

    /**
     * @dataProvider dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty
     * @group opportunities
     * @covers ::save
     */
    public function testCaseFieldEqualsAmountWhenCaseFieldEmpty($case)
    {
        $id = create_guid();
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $opportunity = SugarTestOpportunityUtilities::createOpportunity($id);
        $opportunity->revenuelineitems->add($rli);
        $rli->$case = '';
        $rli->opportunity_id = $id;
        $rli->save();
        $opportunity->save();
        $this->assertEquals($opportunity->$case, $opportunity->amount);
    }


    /**
     * @dataProvider dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty
     * @group opportunities
     * @covers ::save
     */
    public function testCaseFieldEqualsZeroWhenCaseFieldSetToZero($case)
    {
        $id = create_guid();
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $opportunity = SugarTestOpportunityUtilities::createOpportunity($id);
        $opportunity->revenuelineitems->add($rli);
        $opportunity->sales_stage = "Prospecting";
        $rli->$case = $rli->likely_case = 0;
        $rli->opportunity_id = $id;
        $rli->save();
        $opportunity->$case = 0;
        $opportunity->save();
        $this->assertEquals(0, $opportunity->$case);
    }

    /**
     * Test that the base_rate field is populated with rate of currency_id
     * @group forecasts
     * @group opportunities
     * @covers ::save
     */
    public function testCurrencyRate()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        // if Euro does not exist, will use default currency
        $opportunity->currency_id = $currency->id;
        $opportunity->name = "Test Opportunity Delete Me";
        $opportunity->amount = "5000.00";
        $opportunity->date_closed = TimeDate::getInstance()->getNow()->modify("+10 days")->asDbDate();
        $opportunity->best_case = "1000.00";
        $opportunity->worst_case = "600.00";
        $opportunity->save();
        $this->assertEquals(
            sprintf('%.6f', $opportunity->base_rate),
            sprintf('%.6f', $currency->conversion_rate)
        );
    }

    /**
     * Test that base currency exchange rates from EUR are working properly.
     * @group forecasts
     * @group opportunities
     * @covers ::save
     */
    public function testBaseCurrencyAmounts()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        // if Euro does not exist, will use default currency
        $opportunity->currency_id = $currency->id;
        $opportunity->name = "Test Opportunity Delete Me";
        $opportunity->amount = "5000.00";
        $opportunity->date_closed = TimeDate::getInstance()->getNow()->modify("+10 days")->asDbDate();
        $opportunity->best_case = "1000.00";
        $opportunity->worst_case = "600.00";
        $opportunity->save();

        $this->assertEquals(
            sprintf('%.6f', $opportunity->base_rate),
            sprintf('%.6f', $currency->conversion_rate)
        );
    }


    /**
     * @group opportunities
     * @covers ::getClosedStages
     */
    public function testGetClosedStages()
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(array('save'))
            ->disableOriginalConstructor()
            ->getMock();
        $closedStages = $opp->getClosedStages();
        $this->assertTrue(is_array($closedStages));
    }

    /**
     * @dataProvider dataProviderMapProbabilityFromSalesStage
     * @group opportunities
     * @covers ::mapProbabilityFromSalesStage
     * @param string $sales_stage
     * @param string $probability
     */
    public function testMapProbabilityFromSalesStage($sales_stage, $probability)
    {
        /* @var $oppMock Opportunity */
        $oppMock = $this->createPartialMock('Opportunity', array('save'));
        $oppMock->sales_stage = $sales_stage;
        // use the Reflection Helper to call the Protected Method
        SugarTestReflection::callProtectedMethod($oppMock, 'mapProbabilityFromSalesStage');

        $this->assertEquals($probability, $oppMock->probability);
    }

    public static function dataProviderMapProbabilityFromSalesStage()
    {
        return array(
            array('Prospecting', '10'),
            array('Qualification', '20'),
            array('Needs Analysis', '25'),
            array('Value Proposition', '30'),
            array('Id. Decision Makers', '40'),
            array('Perception Analysis', '50'),
            array('Proposal/Price Quote', '65'),
            array('Negotiation/Review', '80'),
            array('Closed Won', '100'),
            array('Closed Lost', '0')
        );
    }

    /**
     * @covers ::isCurrencyIdChanged
     * @dataProvider dataProviderIsCurrencyIdChanged
     * @param string $currency_id
     * @param string $fetched_row_id
     * @param bool $expected
     */
    public function testIsCurrencyIdChanged($currency_id, $fetched_row_id, $expected)
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(array('save'))
            ->disableOriginalConstructor()
            ->getMock();

        $opp->currency_id = $currency_id;
        if (!is_null($fetched_row_id)) {
            $opp->fetched_row = array('currency_id' => $fetched_row_id);
        }

        $actual = SugarTestReflection::callProtectedMethod($opp, 'isCurrencyIdChanged');

        $this->assertSame($expected, $actual);
    }

    public static function dataProviderIsCurrencyIdChanged()
    {
        return array(
            array(
                'test_currency_id',
                'test_currency_id',
                false
            ),
            array(
                'test_currency_id',
                'test-currency-id',
                true
            ),
            array(
                null,
                'test-currency-id',
                true
            ),
            array(
                'test_currency_id',
                null,
                true
            ),
        );
    }

    /**
     * @covers ::updateCurrencyBaseRate
     * @dataProvider dataProviderUpdateCurrencyBaseRate
     * @param string $sales_stage
     * @param array $closed_stages
     * @param bool $expected
     */
    public function testUpdateCurrencyBaseRate($sales_stage, $closed_stages, $expected)
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(array('save', 'getClosedStages'))
            ->disableOriginalConstructor()
            ->getMock();

        $opp->sales_stage = $sales_stage;

        $opp->expects($this->once())
            ->method('getClosedStages')
            ->willReturn($closed_stages);

        $actual = $opp->updateCurrencyBaseRate();
        $this->assertSame($expected, $actual);
    }

    public static function dataProviderUpdateCurrencyBaseRate()
    {
        return array(
            array(
                'test_not_in_array',
                array(
                    'test_1',
                    'test_2',
                    'test_3',
                    'test_4'
                ),
                true
            ),
            array(
                'test_in_array',
                array(
                    'test_1',
                    'test_2',
                    'test_3',
                    'test_4',
                    'test_in_array'
                ),
                false
            )
        );
    }

    /**
     * @covers ::save_relationship_changes
     */
    public function testSaveRelationshipChanges()
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(
                array(
                    'set_opportunity_contact_relationship',
                    'set_relationship_info',
                    'handle_preset_relationships',
                    'handle_remaining_relate_fields',
                    'update_parent_relationships',
                    'handle_request_relate',
                    'load_relationship'
                )
            )
            ->disableOriginalConstructor()
            ->getMock();

        $opp->id = 'test_opp_id';
        $opp->account_id = 'test_account_id1';
        $opp->rel_fields_before_value['account_id'] = 'test_account_id2';

        $linkAccounts = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('delete'))
            ->getMock();

        $linkAccounts->expects($this->once())
            ->method('delete')
            ->with($opp->id, 'test_account_id2');

        $opp->accounts = $linkAccounts;

        $linkProducts = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeans'))
            ->getMock();

        $mockProduct = $this->getMockBuilder('Product')
            ->setMethods(array('save'))
            ->getMock();

        $mockProduct->expects($this->once())
            ->method('save');

        $linkProducts->expects($this->once())
            ->method('getBeans')
            ->willReturn(array($mockProduct));

        $opp->products = $linkProducts;

        $linkRLIs = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeans'))
            ->getMock();

        $mockRLI = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('save'))
            ->getMock();

        $mockRLI->expects($this->once())
            ->method('save');

        $linkRLIs->expects($this->once())
            ->method('getBeans')
            ->willReturn(array($mockRLI));

        $opp->revenuelineitems = $linkRLIs;

        $opp->expects($this->exactly(3))
            ->method('load_relationship')
            ->willReturnOnConsecutiveCalls($linkAccounts, $linkProducts, $linkRLIs);


        $opp->save_relationship_changes(true);

        $this->assertEquals('test_account_id1', $mockProduct->account_id);
        $this->assertEquals('test_account_id1', $mockRLI->account_id);

    }

    /**
     * @covers ::build_generic_where_clause
     */
    public function testBuildGenericWhereClause()
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(array('save'))
            ->disableOriginalConstructor()
            ->getMock();

        $actual = $opp->build_generic_where_clause('test');

        $this->assertEquals("opportunities.name like 'test%' or accounts.name like 'test%'", $actual);
    }

    /**
     * @covers ::set_opportunity_contact_relationship
     */
    public function testSetOpportunityContactRelationship()
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(array('load_relationship'))
            ->disableOriginalConstructor()
            ->getMock();

        $opp->contacts = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('add'))
            ->getMock();

        $opp->contacts->expects($this->once())
            ->method('add')
            ->with('test_contact_id', array('contact_role' => 'default'));

        $opp->expects($this->once())
            ->method('load_relationship');

        $GLOBALS['app_list_strings'] = array(
            'opportunity_relationship_type_default_key' => 'default'
        );

        $opp->set_opportunity_contact_relationship('test_contact_id');

    }

}
