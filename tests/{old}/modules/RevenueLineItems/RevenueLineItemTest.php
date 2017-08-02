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
 * Class RevenueLineItemTest
 * @coversDefaultClass RevenueLineItem
 */
class RevenueLineItemTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('RevenueLineItems'));
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @group revenuelineitems
     * @covers ::convertToQuotedLineItem
     */
    public function testConvertToQuotedLineItemWithDiscountPriceSet()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(array('save'))->getMock();
        $rli->likely_case = '100.00';
        $rli->discount_price = '200.00';
        $rli->sales_stage = 'Test';
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals($rli->discount_price, $product->discount_price);
        $this->assertEquals($rli->id, $product->revenuelineitem_id, 'RLI to QLI Link is not Set');
        $this->assertEquals('Test', $product->sales_stage, "Product does not match RevenueLineItem");
    }

    /**
     * @group revenuelineitems
     * @covers ::convertToQuotedLineItem
     */
    public function testConvertToQuotedLineItemWithoutDiscountPriceSet()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(array('save'))->getMock();
        $rli->likely_case = '100.00';
        $rli->discount_price = '';
        $rli->sales_stage = 'Test';
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals($rli->likely_case, $product->discount_price);
        $this->assertEquals($rli->id, $product->revenuelineitem_id, 'RLI to QLI Link is not Set');
        $this->assertEquals('Test', $product->sales_stage, "Product does not match RevenueLineItem");
    }

    /**
     * @group revenuelineitems
     * @covers ::convertToQuotedLineItem
     */
    public function testConvertToQuoteLineItemsSetsCorrectDiscountAmount()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(array('save'))->getMock();
        $rli->discount_amount = '25.00';
        $rli->quantity = '50';
        $rli->discount_price = '1.00';
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals('25.00', $product->discount_amount);
    }

    /**
     * @group revenuelineitems
     * @covers ::convertToQuotedLineItem
     */
    public function testConvertToQuoteLineItemsSetCorrectDiscountAmountWhenPercent()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(array('save'))->getMock();
        $rli->discount_amount = '25.00';
        $rli->quantity = '50';
        $rli->discount_price = '1.00';
        $rli->discount_select = 1;
        $rli->deal_calc = 0.25; // (discount_amount/100)*discount_price
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals('25.00', $product->discount_amount);
    }

    /**
     * @dataProvider dataProviderSetDiscountPrice
     * @covers ::setDiscountPrice
     * @param string $likely
     * @param string $quantity
     * @param string $discount_price
     * @param string $expected_discount
     */
    public function testSetDiscountPrice($likely, $quantity, $discount_price, $expected_discount)
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(array('save'))->getMock();
        $rli->likely_case = $likely;
        $rli->quantity = $quantity;
        $rli->discount_price = $discount_price;

        SugarTestReflection::callProtectedMethod($rli, 'setDiscountPrice');

        $this->assertEquals($expected_discount, $rli->discount_price);
    }

    public function dataProviderSetDiscountPrice()
    {
        // values are likely, quantity, discount_price, expected_discount_price
        return array(
            array('100.00', '1', '', '100.00'),
            array('100.00', '1', '0.00', '0.00'),
            array('100.00', '1', '150', '150.00'),
        );
    }

    /**
     * @covers ::saveProductWorksheet
     */
    public function testSaveProductWorksheetReturnsFalse()
    {

        Forecast::$settings = array(
            'is_setup' => 0
        );
        $mock = $this->getMockBuilder('RevenueLineItem')
            ->getMock();

        $actual = SugarTestReflection::callProtectedMethod($mock, 'saveProductWorksheet');
        $this->assertFalse($actual);
        Forecast::$settings = array();
    }


    /**
     * @covers ::mapFieldsFromProductTemplate
     */
    public function testMapFieldsProductTemplate()
    {
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(array('save'))->getMock();

        $arrExpected = array(
            'category_id' => 'test_category_id',
            'mft_part_num' => 'test_mft_part_num',
            'list_price' => 'test_list_price',
            'cost_price' => 'test_cost_price',
            'discount_price' => 'test_discount_price',
            'list_usdollar' => 'test_list_usdollar',
            'cost_usdollar' => 'test_cost_usdollar',
            'discount_usdollar' => 'test_discount_usdollar',
            'tax_class' => 'test_tax_class',
            'weight' => 'test_weight',
        );

        $pt = $this->getMockBuilder('ProductTemplate')
            ->setMethods(array('save'))
            ->getMock();

        $pt->id = 'test_pt_id';
        foreach($arrExpected as $key => $val) {
            $pt->$key = $val;
        }

        $rli->product_template_id = $pt->id;
        BeanFactory::registerBean($pt);

        SugarTestReflection::callProtectedMethod($rli, 'mapFieldsFromProductTemplate');

        foreach($arrExpected as $key => $expected) {
            $this->assertEquals($expected, $rli->$key);
        }

        BeanFactory::unregisterBean($pt, $pt->id);
    }

    public static function dataProviderSetAccountIdForOpportunity()
    {
        return array(
            array(
                array(
                    'test_account_id'
                ),
                true
            ),
            array(
                array(),
                false
            )
        );
    }

    /**
     * @dataProvider dataProviderSetAccountIdForOpportunity
     * @covers ::setAccountIdForOpportunity
     */
    public function testSetAccountIdForOpportunity($accounts, $expected)
    {
        $product = $this->createPartialMock('Product', array('save', 'load_relationship'));

        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(array('save', 'load_relationship'))
            ->getMock();

        $opp->id = 'test_opp_id';

        $opp->expects($this->once())
            ->method('load_relationship')
            ->with('accounts')
            ->willReturn(true);

        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(array('get'))
            ->disableOriginalConstructor()
            ->getMock();

        $link2->expects($this->once())
            ->method('get')
            ->willReturn($accounts);


        $opp->accounts = $link2;

        BeanFactory::registerBean($opp);

        $actual = SugarTestReflection::callProtectedMethod($product, 'setAccountIdForOpportunity', array($opp->id));

        $this->assertEquals($expected, $actual);

        BeanFactory::unregisterBean($opp, $opp->id);
    }

    /**
     * @covers ::mapFieldsFromOpportunity
     */
    public function testMapFieldsFromOpportunity()
    {
        $product = $this->createPartialMock('Product', array('save'));

        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(array('save'))
            ->getMock();

        $opp->id = 'test_opp_id';
        $opp->opportunity_type = 'test_type';

        $product->opportunity_id = $opp->id;

        BeanFactory::registerBean($opp);

        SugarTestReflection::callProtectedMethod($product, 'mapFieldsFromOpportunity');

        $this->assertEquals('test_type', $product->product_type);

        BeanFactory::unregisterBean($opp, $opp->id);
    }

    /**
     * @covers ::setBestWorstFromLikely
     */
    public function testSetBestWorstFromLikelyDoesNotChangeBecauseOfAcl()
    {
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('ACLFieldAccess'))
            ->disableOriginalConstructor()
            ->getMock();

        $rli->expects($this->atLeast(2))
            ->method('ACLFieldAccess')
            ->willReturn(false);

        /* @var $rli RevenueLineItem */
        $rli->likely_case = 500;
        $rli->best_case = '';
        $rli->worst_case = 0;

        SugarTestReflection::callProtectedMethod($rli, 'setBestWorstFromLikely');

        $this->assertEquals(500, $rli->likely_case);
        $this->assertEquals('', $rli->best_case);
        $this->assertEquals(0, $rli->worst_case);
    }

    /**
     * @dataProvider dataProviderBestWorstAutoFill
     * @covers ::setBestWorstFromLikely
     */
    public function testBestWorstAutoFill($value, $likely, $expected)
    {
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('ACLFieldAccess'))
            ->disableOriginalConstructor()
            ->getMock();

        $rli->expects($this->atLeast(2))
            ->method('ACLFieldAccess')
            ->willReturn(true);

        /* @var $rli RevenueLineItem */
        $rli->likely_case = $likely;
        $rli->best_case = $value;
        $rli->worst_case = $value;

        SugarTestReflection::callProtectedMethod($rli, 'setBestWorstFromLikely');

        $this->assertSame($expected, $rli->best_case);
        $this->assertSame($expected, $rli->worst_case);
    }

    public function dataProviderBestWorstAutoFill()
    {
        return array(
            array(
                '',
                '100',
                '100'
            ),
            array(
                null,
                '100',
                '100'
            ),
            array(
                '42',
                '100',
                '42'
            ),
            array(
                '0',
                '100',
                '0'
            ),
            array(
                '0',
                100,
                '0'
            )
        );
    }

    /**
     * @dataProvider dataProviderCheckQuantity
     * @group revenuelineitems
     * @covers ::checkQuantity
     * @param mixed $actual
     * @param integer $expected
     */
    public function testQuantityNotDefaulted($actual, $expected)
    {
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('save'))
            ->getMock();

        $rli->quantity = $actual;

        SugarTestReflection::callProtectedMethod($rli, 'checkQuantity');

        $this->assertEquals($expected, $rli->quantity);
    }


    public static function dataProviderCheckQuantity()
    {
        return array(
            array('', 0),
            array(null, 0),
            array(0, 0),
            array(1, 1),
            array(42,42),
        );
    }

    /**
     * @dataProvider dataProviderMapProbabilityFromSalesStage
     * @covers ::mapProbabilityFromSalesStage
     * @group revenuelineitems
     */
    public function testMapProbabilityFromSalesStage($sales_stage, $probability)
    {
        $revenuelineitem = $this->createMock('RevenueLineItem');
        $revenuelineitem->sales_stage = $sales_stage;
        // use the Reflection Helper to call the Protected Method
        SugarTestReflection::callProtectedMethod($revenuelineitem, 'mapProbabilityFromSalesStage');

        $this->assertEquals($probability, $revenuelineitem->probability);
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
     * @dataProvider dataProviderCanConvertToQuote
     * @param $fields
     * @param $expected
     */
    public function testCanConvertToQuote($fields, $expected)
    {
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(null)
            ->getMock();

        foreach ($fields as $field => $value) {
            $rli->$field = $value;
        }

        $actual = $rli->canConvertToQuote();

        if ($expected === false) {
            // we have to assert not true, since it's returning a language string and testing against that
            // is bad!
            $this->assertNotTrue($actual);
        } else {
            $this->assertTrue($actual);
        }
    }

    public static function dataProviderCanConvertToQuote()
    {
        return array(
            array(
                array(
                    'category_id' => 'test_cat_id',
                ),
                false,
            ),
            array(
                array(
                    'quote_id' => 'test_quote_id',
                ),
                false,
            ),
            array(
                array(
                    'id' => 'test',
                ),
                true,
            ),
        );
    }
}
