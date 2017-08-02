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

class SugarFieldCurrencyTest extends Sugar_PHPUnit_Framework_TestCase
{
    static $currency, $currency2, $currency3;

    /**
     *
     * @access public
     */
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        self::$currency = SugarTestCurrencyUtilities::createCurrency('foo', 'f', 'f', .5);
        self::$currency2 = SugarTestCurrencyUtilities::createCurrency('Singapore', '$', 'SGD', 1.246171, 'currency-sgd');
        self::$currency3 = SugarTestCurrencyUtilities::createCurrency('Bitcoin', '฿', 'XBT', 0.001057, 'currency-btc');
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
     *
     * @group currency
     * @access public
     */
    public function testGetListViewSmarty()
    {
        global $current_user;

        $field = SugarFieldHandler::getSugarField('currency');

        $parentFieldArray = array (
            'CURRENCY_ID' => '-99',
            'BASE_RATE' => '1.000000',
            'TOTAL' => '4200.000000',
            'TOTAL_USDOLLAR' => '4200.000000',
        );
        $vardef = array (
            'type' => 'currency',
            'name' => 'TOTAL',
            'vname' => 'LBL_TOTAL',
            );
        $displayParams = array('labelSpan' => null, 'fieldSpan' => null);
        $col = null;

        // format base currency
        $value = $field->getListViewSmarty($parentFieldArray, $vardef, $displayParams, $col);
        $this->assertEquals('$4,200.00', $value);

        // format foo currency
        $parentFieldArray['CURRENCY_ID'] = self::$currency->id;
        $parentFieldArray['BASE_RATE'] = self::$currency->conversion_rate;
        $value = $field->getListViewSmarty($parentFieldArray, $vardef, $displayParams, $col);
        $this->assertEquals(self::$currency->symbol . '4,200.00', $value);

        // format as usdollar field (is base currency)
        $vardef['is_base_currency'] = true;
        $value = $field->getListViewSmarty($parentFieldArray, $vardef, $displayParams, $col);
        $this->assertEquals('$4,200.00', $value);

        // show base value in user preferred currency
        $current_user->setPreference('currency_show_preferred', true);
        $current_user->setPreference('currency', self::$currency3->id);
        $parentFieldArray['BASE_RATE'] = '1.000000';
        $value = $field->getListViewSmarty($parentFieldArray, $vardef, $displayParams, $col);
        $this->assertEquals(self::$currency3->symbol . '4.44', $value);

    }

    public function importSanitizeProvider()
    {
        return array(
            array('$123.123,00', '123123.00', '.', ','),
            array('$123,123.00', '123123.00', ',', '.'),
            array('$123A123z00', '123123.00', 'A', 'z'),
        );
    }

    /**
     * @dataProvider importSanitizeProvider
     */
    public function testImportSanitize($value, $expected, $group, $decimal)
    {
        $currency = SugarTestCurrencyUtilities::createCurrency('My Test Currency', '$', 'MTC', 1);
        $settings = new ImportFieldSanitize();
        $settings->currency_symbol = '$';
        $settings->currency_id = $currency->id;
        $settings->dec_sep = $decimal;
        $settings->num_grp_sep = $group;

        $vardef = array();

        /* @var $focus SugarBean */
        $focus = $this->createPartialMock('Opportunity', array('save'));

        /* @var $field SugarFieldCurrency */
        $field = SugarFieldHandler::getSugarField('currency');
        $return = $field->importSanitize($value, $vardef, $focus, $settings);

        $this->assertEquals($expected, $return);

        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
    }

    public function testImportSanitizeDoesNotThrowSugarMathException()
    {
        try {
            $vardef = array(
                'convertToBase' => true,
            );

            $currency = SugarTestCurrencyUtilities::createCurrency('My Test Currency', '$', 'MTC', 1);

            $settings = new ImportFieldSanitize();
            $settings->currency_symbol = '$';
            $settings->currency_id = $currency->id;
            $settings->dec_sep = '.';
            $settings->num_grp_sep = ',';

            /* @var $focus SugarBean */
            $focus = $this->createPartialMock('Opportunity', array('save'));

            /* @var $field SugarFieldCurrency */
            $field = SugarFieldHandler::getSugarField('currency');
            $return = $field->importSanitize('$123,123.00A', $vardef, $focus, $settings);

            $this->assertFalse($return);
        } catch (SugarMath_Exception $sme) {
            $this->fail($sme->getMessage());
        }

        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
    }

    /**
     *
     * @group export
     * @group currency
     * @access public
     */
    public function testExportSanitize()
    {
        global $sugar_config;
        $obj = BeanFactory::newBean('Opportunities');
        $obj->amount = '1000';
        $obj->base_rate = 1;
        $obj->currency_id = '-99';

        $vardef = $obj->field_defs['amount'];
        $vardef['convertToBase'] = true;
        $field = SugarFieldHandler::getSugarField('currency');

        // expect value in base currency
        $expectedValue = SugarCurrency::formatAmountUserLocale($obj->amount, -99);
        $value = $field->exportSanitize($obj->amount, $vardef, $obj);
        $this->assertEquals($expectedValue, $value);

        // value will still be base if currency type is changed on opp
        $obj->currency_id = self::$currency->id;
        $value = $field->exportSanitize($obj->amount, $vardef, $obj);
        $this->assertEquals($expectedValue, $value);

        //Test that we can use the row overload feature in exportSanitize
        $obj->currency_id = '';
        $value = $field->exportSanitize($obj->amount, $vardef, $obj, array('currency_id'=>self::$currency->id));
        $this->assertEquals($expectedValue, $value);

    }

    /**
     *
     * @group export
     * @group currency
     * @access public
     */
    public function testExportSanitizeConvertToBase()
    {
        global $sugar_config;
        $obj = BeanFactory::newBean('Opportunities');
        $obj->amount = '1000';
        $obj->base_rate = self::$currency2->conversion_rate;
        $obj->currency_id = self::$currency2->id;

        //Test conversion to base_rate
        $field = SugarFieldHandler::getSugarField('currency');
        $vardef['convertToBase'] = true;
        $convertedValue = '802.46';
        $expectedValue = SugarCurrency::formatAmountUserLocale($convertedValue, '-99');
        $value = $field->exportSanitize($obj->amount, $vardef, $obj);
        $this->assertEquals($expectedValue, $value);

    }

    /**
     * @dataProvider unformatFieldProvider
     * @param $value
     * @param $expectedValue
     */
    public function testUnformatField($value, $expectedValue)
    {
        $field = SugarFieldHandler::getSugarField('currency');
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

        $field = SugarFieldHandler::getSugarField('currency');
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
        $field = SugarFieldHandler::getSugarField('currency');
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

}
