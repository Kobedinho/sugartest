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
 * Class ProductTest
 * @coversDefaultClass Product
 */
class ProductTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanList');
    }

    public function tearDown()
    {}

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }


    /**
     * @param String $amount
     * @param String $quantity
     * @param String $discount
     * @param String $discount_select
     * @param String $likely_expected
     * @throws SugarMath_Exception
     * @dataProvider productDataProvider
     * @covers ::convertToRevenueLineItem
     */
    public function testConvertProductToRLI($amount, $quantity, $discount, $discount_select, $likely_expected)
    {
        /* @var $product Product */
        $product = $this->getMockBuilder('Product')->setMethods(array('save'))->getMock();

        $product->expects($this->any())
            ->method('save')
            ->will($this->returnValue(true));

        $discount_amount = $discount;
        if ($discount_select === 1) {
            $discount_amount = SugarMath::init()->exp('(?*?)*(?/100)', array($amount, $quantity, $discount))->result();
        }

        $product->name = 'Hello World';
        $product->total_amount = SugarMath::init()->exp('((?*?)-?)', array($amount, $quantity, $discount_amount))->result();
        $product->discount_price = $amount;
        $product->quantity = $quantity;
        $product->discount_amount = $discount;
        $product->discount_select = $discount_select;
        $product->fetched_row = array();

        foreach ($product->getFieldDefinitions() as $field) {
            $product->fetched_row[$field['name']] = $product->{$field['name']};
        }

        SugarTestReflection::callProtectedMethod($product, 'calculateDiscountPrice');

        $rli = $product->convertToRevenueLineItem();

        $this->assertEquals($product->revenuelineitem_id, $rli->id);
        $this->assertEquals($product->name, $rli->name);
        $this->assertEquals(
            $likely_expected,
            $rli->likely_case,
            'Likely Case Is Wrong'
        );
        // lets make sure that the discount_amount is correct
        $this->assertEquals(
            $discount_amount,
            $rli->discount_amount,
            'Discount Amount Is Wrong'
        );
    }

    /**
     * productDataProvider
     */
    public function productDataProvider()
    {
        // $amount, $quantity, $discount, $discount_select, $likely_expected
        return array(
            array('100.00', '1', '0', null, '100.00'),
            array('1000.00', '10', '0', null, '10000.00'),
            array('100.00', '10', '1', null, '999.00'),
            array('100.00', '1', '0', 1, '100.00'),
            array('100.00', '1', '10', 1, '90.00'),
            array('100.00', '2', '20', 1, '160.00'),
            array('0.13', '1000', '10', 1, '117.00'),
            array('0.25', '89765', '21456.00', null, '985.25')
        );
    }

    /**
     *
     * @dataProvider dataProviderUpdateCurrencyBaseRate
     * @param string $stage
     * @param boolean $expected
     * @covers ::updateCurrencyBaseRate
     */
    public function testUpdateCurrencyBaseRate($stage, $expected)
    {
        $product = $this->createPartialMock('Product', array('save', 'load_relationship'));
        $product->expects($this->once())
            ->method('load_relationship')
            ->with('product_bundles')
            ->willReturn(true);

        $bundle = $this->createPartialMock('ProductBundle', array('save', 'load_relationship'));

        $bundle->expects($this->once())
            ->method('load_relationship')
            ->with('quotes')
            ->willReturn(true);

        /* @var $quote Quote */
        $quote = $this->createPartialMock('Quote', array('save'));

        $quote->quote_stage = $stage;

        $quote_link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeans'))
            ->getMock();

        $quote_link2->expects($this->once())
            ->method('getBeans')
            ->willReturn(
                array(
                    $quote
                )
            );

        /* @var $product Product */
        $bundle->quotes = $quote_link2;

        $bundle_link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeans'))
            ->getMock();

        $bundle_link2->expects($this->once())
            ->method('getBeans')
            ->willReturn(
                array(
                    $bundle
                )
            );

        /* @var $product Product */
        $product->product_bundles = $bundle_link2;

        $this->assertEquals($expected, $product->updateCurrencyBaseRate());
    }

    public function dataProviderUpdateCurrencyBaseRate()
    {
        return array(
            array('Draft', true),
            array('Negotiation', true),
            array('Delivered', true),
            array('On Hold', true),
            array('Confirmed', true),
            array('Closed Accepted', false),
            array('Closed Lost', false),
            array('Closed Dead', false)
        );
    }

    /**
     * @covers ::updateCurrencyBaseRate
     */
    public function testUpdateCurrencyBaseRateWithNotQuoteReturnTrue()
    {
        $product = $this->createPartialMock('Product', array('save', 'load_relationship'));
        $product->expects($this->once())
            ->method('load_relationship')
            ->with('product_bundles')
            ->willReturn(true);

        $link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeans'))
            ->getMock();

        $link2->expects($this->once())
            ->method('getBeans')
            ->willReturn(
                array()
            );

        /* @var $product Product */
        $product->product_bundles = $link2;

        $this->assertTrue($product->updateCurrencyBaseRate());
    }

    /**
     * @covers ::get_summary_text
     */
    public function testGetSummaryText()
    {
        $product = $this->createPartialMock('Product', array('save', 'load_relationship'));
        $product->name = 'test';

        $this->assertEquals('test', $product->get_summary_text());
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

    public static function dataProviderPopulateFromTemplateWillReturnFalse()
    {
        return array(
            array(null),
            array('one_id')
        );
    }

    /**
     * @dataProvider dataProviderPopulateFromTemplateWillReturnFalse
     * @param string $template_id
     * @covers ::populateFromTemplate
     */
    public function testPopulateFromTemplateWillReturnFalse($template_id)
    {
        $product = $this->createPartialMock('Product', array('save'));

        $product->product_template_id = $template_id;
        $product->fetched_row = array(
            'product_template_id' => $template_id
        );

        $actual = SugarTestReflection::callProtectedMethod($product, 'populateFromTemplate');

        $this->assertFalse($actual);
    }

    /**
     * @covers ::calculateDiscountPrice
     */
    public function testCalculateDiscountPriceDoesNotRunIfFieldEmpty()
    {
        $fields = array(
            'pricing_formula',
            'cost_price',
            'list_price',
            'discount_price',
            'pricing_factor'
        );

        $product = $this->createPartialMock('Product', array('save', 'getPriceFormula'));

        $product->expects($this->never())
            ->method('getPriceFormula');

        foreach($fields as $field) {
            $product->$field = null;
        }

        SugarTestReflection::callProtectedMethod($product, 'calculateDiscountPrice');
    }

    /**
     * @covers ::calculateDiscountPrice
     */
    public function testCalculateDiscountPrice()
    {
        $product = $this->createPartialMock('Product', array('save', 'getPriceFormula'));
        $product->pricing_formula = 'PercentageDiscount';
        $product->cost_price = '100.000000';
        $product->list_price = '150.000000';
        $product->discount_price = '25.000000';
        $product->pricing_factor = '12.00';


        SugarAutoLoader::load('modules/ProductTemplates/formulas/price_list_discount.php');

        $formula = $this->getMockBuilder('PercentageDiscount')
            ->setMethods(array('calculate_price'))
            ->getMock();

        $formula->expects($this->once())
            ->method('calculate_price')
            ->with(
                $product->cost_price,
                $product->list_price,
                $product->discount_price,
                $product->pricing_factor
            );

        $product->expects($this->once())
            ->method('getPriceFormula')
            ->willReturn($formula);

        SugarTestReflection::callProtectedMethod($product, 'calculateDiscountPrice');
    }

    public function dataProviderGetPriceFormula()
    {
        return array(
            array('Fixed'),
            array('ProfitMargin'),
            array('PercentageMarkup'),
            array('PercentageDiscount'),
            array('IsList')
        );
    }

    /**
     * @dataProvider dataProviderGetPriceFormula
     * @covers ::getPriceFormula
     * @param string $formula
     */
    public function testGetPriceFormula($formula)
    {
        $product = $this->createPartialMock('Product', array('save'));

        $actual = SugarTestReflection::callProtectedMethod($product, 'getPriceFormula', array($formula));

        $this->assertInstanceOf($formula, $actual);

        unset($GLOBALS['price_formulas']);
    }

    /**
     * @dataProvider dataProviderCheckQuantity
     * @covers ::checkQuantity
     * @param mixed $actual
     * @param integer $expected
     */
    public function testQuantityNotDefaulted($actual, $expected)
    {
        $product = $this->getMockBuilder('Product')
            ->setMethods(array('save'))
            ->getMock();

        $product->quantity = $actual;

        SugarTestReflection::callProtectedMethod($product, 'checkQuantity');

        $this->assertEquals($expected, $product->quantity);
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


}
