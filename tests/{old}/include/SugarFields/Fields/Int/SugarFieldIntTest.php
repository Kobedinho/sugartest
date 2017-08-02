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

class SugarFieldIntTest extends Sugar_PHPUnit_Framework_TestCase
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
        $field = SugarFieldHandler::getSugarField('int');
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
            array('1.000', '1'),
            array('1,000', '1000'),
            array('1,000.00', '1000'),
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

        $field = SugarFieldHandler::getSugarField('int');
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
            array('1.000,65', '1000'),
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
        $field = SugarFieldHandler::getSugarField('int');
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

    /**
     * @dataProvider apiValidateProvider
     * @param $bean
     * @param $name
     * @param $value
     * @param $vardef
     * @param $valid
     */
    public function testApiValidate($bean, $name, $value, $vardef, $valid)
    {
        $field = SugarFieldHandler::getSugarField('int');
        $this->assertEquals($valid, $field->apiValidate($bean, array($name=>$value), $name, $vardef));
    }
    
    /**
     * testApiValidate data provider
     * @access public
     */
    public function apiValidateProvider()
    {
        $bean = new SugarBean();
        $vardef = array('name'=>'test','type'=>'int');
        $data = array(
            'MySQL' => array(
                array($bean, 'test', 0, $vardef, true),
                array($bean, 'test', -12345678901, $vardef, false),
                array($bean, 'test', 12345678901, $vardef, false),
            ),
            'Oracle' => array(
                array($bean, 'test', 0, $vardef, true),
                array($bean, 'test', -12345678901, $vardef, false),
                array($bean, 'test', 12345678901, $vardef, false),
            ),
            'IBM_DB2' => array(
                array($bean, 'test', 0, $vardef, true),
                array($bean, 'test', -12345678901, $vardef, false),
                array($bean, 'test', 12345678901, $vardef, false),
            ),
            'SQL Server' => array(
                array($bean, 'test', 0, $vardef, true),
                array($bean, 'test', -12345678901, $vardef, false),
                array($bean, 'test', 12345678901, $vardef, false),
            ),
        );

        $data = isset($data[$bean->db->dbName]) ? $data[$bean->db->dbName] : array();

        $sugarMinInt = SugarConfig::getInstance()->get('sugar_min_int');
        if (!empty($sugarMinInt)) {
            $data[] = array($bean, 'test', $sugarMinInt - 1, $vardef, false);
            $data[] = array($bean, 'test', $sugarMinInt, $vardef, true);
        }
        $sugarMaxInt = SugarConfig::getInstance()->get('sugar_max_int');
        if (!empty($sugarMaxInt)) {
            $data[] = array($bean, 'test', $sugarMaxInt + 1, $vardef, false);
            $data[] = array($bean, 'test', $sugarMaxInt, $vardef, true);
        }

        $vardef['min'] = -100;
        $data[] = array($bean, 'test', $vardef['min'] - 1, $vardef, false);
        $data[] = array($bean, 'test', $vardef['min'], $vardef, true);
        $vardef['max'] = 100;
        $data[] = array($bean, 'test', $vardef['max'] + 1, $vardef, false);
        $data[] = array($bean, 'test', $vardef['max'], $vardef, true);
        
        return $data;
    }
}
