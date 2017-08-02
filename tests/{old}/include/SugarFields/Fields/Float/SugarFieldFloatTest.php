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

class SugarFieldFloatTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     *
     * @access public
     */
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    /**
     *
     * @access public
     */
    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
    }

    public function setUp()
    {
        parent::setUp();
        $current_user = $GLOBALS['current_user'];
        $current_user->setPreference('dec_sep', '.');
        $current_user->setPreference('num_grp_sep', ',');
        $current_user->setPreference('default_currency_significant_digits', 2);
        $current_user->save();
        //force static var reset
        get_number_seperators(true);
    }

    public function tearDown()
    {
        $current_user = $GLOBALS['current_user'];
        $current_user->setPreference('dec_sep', '.');
        $current_user->setPreference('num_grp_sep', ',');
        $current_user->setPreference('default_currency_significant_digits', 2);
        $current_user->save();
        //force static var reset
        get_number_seperators(true);
        parent::tearDown();
    }

    /**
     * @dataProvider unformatFieldProvider
     * @param $value
     * @param $expectedValue
     */
    public function testUnformatField($value, $expectedValue)
    {
        $field = SugarFieldHandler::getSugarField('float');
        $this->assertEquals($expectedValue, $field->unformatField($value, null));
    }

    /**
     * testUnformatField data provider
     *
     * @group currency
     * @access public
     */
    public static function unformatFieldProvider()
    {
        return array(
            array('1000', '1000'),
            array('1.000', '1.000'),
            array('1,000', '1000'),
            array('1,000.00', '1000.00'),
        );
    }

    /**
     * @dataProvider unformatFieldProviderCommaDotFlip
     * @param $value
     * @param $expectedValue
     */
    public function testUnformatFieldCommaDotFlip($value, $expectedValue)
    {
        $current_user = $GLOBALS['current_user'];
        $current_user->setPreference('dec_sep', ',');
        $current_user->setPreference('num_grp_sep', '.');
        $current_user->setPreference('default_currency_significant_digits', 2);
        $current_user->save();

        //force static var reset
        get_number_seperators(true);

        $field = SugarFieldHandler::getSugarField('float');
        $this->assertEquals($expectedValue, $field->unformatField($value, null));
    }

    /**
     * testUnformatFieldCommaDotFlip data provider
     *
     * @group currency
     * @access public
     */
    public static function unformatFieldProviderCommaDotFlip()
    {
        return array(
            array('1,000', '1'),
            array('1000,00', '1000'),
            array('1.000,65', '1000.65'),
            array('1.065', '1065'),
        );
    }

    /**
     * @dataProvider apiUnformatFieldProvider
     * @param $value
     * @param $expectedValue
     */
    public function testApiUnformatField($value, $expectedValue)
    {
        $field = SugarFieldHandler::getSugarField('float');
        $this->assertEquals($expectedValue, $field->apiUnformatField($value));
    }

    /**
     * testApiUnformatField data provider
     *
     * @group currency
     * @access public
     */
    public static function apiUnformatFieldProvider()
    {
        return array(
            array('1000', '1000'),
            array('1.000', '1.000'),
            array('1,000', '1,000'),
            array('1,000.00', '1,000.00'),
        );
    }

    public function dataProviderFixForForFloats()
    {
        return array(
            array('$equals', 10.69, '='),
            array('$not_equals', 10.69, '!='),
            array('$between', array(10.69, 100.69), 'BETWEEN'),
            array('$lt', 10.69, '<'),
            array('$lte', 10.69, '<='),
            array('$gt', 10.69, '>'),
            array('$gte', 10.69, '>='),
        );
    }

    /**
     *
     * @dataProvider dataProviderFixForForFloats
     * @param String $op                The Filer Operation
     * @param Number $value             The Value we are looking for
     * @param String $query_op          The value of $op in the query
     */
    public function testFixForFilterForFloats($op, $value, $query_op)
    {
        $bean = BeanFactory::newBean('RevenueLineItems');

        /* @var $where SugarQuery_Builder_Where */
        $where = $this->getMockBuilder('SugarQuery_Builder_Where')
            ->disableOriginalConstructor()
            ->getMock();

        /* @var $bean RevenueLineItem */
        $q = new SugarQuery();
        $q->from($bean);

        $field = new SugarFieldFloat('float');

        $ret = $field->fixForFilter($value, 'unit_test', $bean, $q, $where, $op);

        $this->assertFalse($ret);
        if (!is_array($value)) {
            $this->assertContains('(ROUND(unit_test, 2) ' . $query_op . ' ' . $value . ')', $q->compile()->getSQL());
        } else {
            $this->assertContains(
                '(ROUND(unit_test, 2) ' . $query_op . ' ' . $value[0] . ' AND ' . $value[1] . ')',
                $q->compile()->getSQL()
            );
        }
    }

    public function dataProviderFixForWholeNumbers()
    {
        return array(
            array('$equals', 10),
            array('$not_equals', 10),
            array('$between', array(10, 100)),
            array('$lt', 10),
            array('$lte', 10),
            array('$gt', 10),
            array('$gte', 10),
        );
    }

    /**
     *
     * @dataProvider dataProviderFixForWholeNumbers
     * @param String $op                The Filer Operation
     * @param Number $value             The Value we are looking for
     */
    public function testFixForFilterForWholeNumbers($op, $value)
    {
        $bean = BeanFactory::newBean('RevenueLineItems');

        /* @var $where SugarQuery_Builder_Where */
        $where = $this->getMockBuilder('SugarQuery_Builder_Where')
            ->disableOriginalConstructor()
            ->getMock();

        /* @var $bean RevenueLineItem */
        $q = new SugarQuery();
        $q->from($bean);

        $field = new SugarFieldFloat('float');

        $ret = $field->fixForFilter($value, 'unit_test', $bean, $q, $where, $op);

        // should always return true
        $this->assertTrue($ret);
    }

}
