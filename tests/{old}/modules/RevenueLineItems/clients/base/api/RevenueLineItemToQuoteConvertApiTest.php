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

class RevenueLineItemToQuoteConvertApiTests extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Opportunity
     */
    protected static $opp;

    /**
     * @var RevenueLineItem
     */
    protected static $revenueLineItem;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        parent::setUpBeforeClass();
        self::$opp = SugarTestOpportunityUtilities::createOpportunity();

        self::$revenueLineItem = new RevenueLineItem();
        self::$revenueLineItem->opportunity_id = self::$opp->id;
        self::$revenueLineItem->quantity = '50';
        self::$revenueLineItem->discount_amount = '10.00';
        self::$revenueLineItem->likely_case = '40.00';
        self::$revenueLineItem->discount_price = '1.00';
        self::$revenueLineItem->save();

        SugarTestRevenueLineItemUtilities::setCreatedRevenueLineItem(array(self::$revenueLineItem->id));
    }

    public static function tearDownAfterClass()
    {
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    /**
     * @group RevenueLineItems
     * @group quotes
     */
    public function testCreateQuoteFromRevenueLineItemApi()
    {
        /* @var $restService RestService */
        $restService = SugarTestRestUtilities::getRestServiceMock();

        $api = new RevenueLineItemToQuoteConvertApi();
        $return = $api->convertToQuote($restService, array('module' => 'RevenueLineItem', 'record' => self::$revenueLineItem->id));

        $this->assertNotEmpty($return['id']);

        SugarTestQuoteUtilities::setCreatedQuote(array($return['id']));

        // now pull up the quote to make sure it matches the stuff from the opp
        /* @var $quote Quote */
        $quote = BeanFactory::getBean('Quotes', $return['id']);

        $this->assertEquals(self::$opp->id, $quote->opportunity_id);

        // lets make sure the totals are correct
        $this->assertEquals('50.00', $quote->subtotal);
        $this->assertEquals('10.00', $quote->deal_tot);
        $this->assertEquals('40.00', $quote->new_sub);
        $this->assertEquals('40.00', $quote->total);

        $quote->load_relationship('revenuelineitems');
        $revenueLineItem = $quote->revenuelineitems->getBeans();
        $this->assertNotEmpty($revenueLineItem);
        $revenueLineItem = reset($revenueLineItem);

        $this->assertEquals(self::$revenueLineItem->id, $revenueLineItem->id);

        return $revenueLineItem;
    }

    /**
     * @param $revenueLineItem
     * @group RevenueLineItems
     * @group quotes
     * @depends testCreateQuoteFromRevenueLineItemApi
     */
    public function testRevenueLineItemStatusIsQuotes($revenueLineItem)
    {
        $this->assertEquals(RevenueLineItem::STATUS_QUOTED, $revenueLineItem->status);
    }

    /**
     * @expectedException SugarApiExceptionRequestMethodFailure
     */
    public function testCreateProductBundleFromRLIListThrowsException()
    {
        $mock_rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('canConvertToQuote'))
            ->getMock();

        $mock_rli->id = 'unit_test_1';

        $mock_rli->expects($this->once())
            ->method('canConvertToQuote')
            ->willReturn('Some Random String');

        BeanFactory::registerBean($mock_rli);

        $api = new RevenueLineItemToQuoteConvertApi();

        SugarTestReflection::callProtectedMethod($api, 'createProductBundleFromRLIList', array(array('unit_test_1')));

        BeanFactory::unregisterBean($mock_rli);
    }
}
