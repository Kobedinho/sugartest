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

class NumericExpressionTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderTestIsCurrencyField
     * @param array $def
     * @param boolean $expected
     */
    public function testIsCurrencyField($def, $expected)
    {
        $bean = $this->getMockBuilder('Opportunity')
            ->setMethods(array('save', 'getFieldDefinition'))
            ->disableOriginalConstructor()
            ->getMock();

        $bean->expects($this->once())
            ->method('getFieldDefinition')
            ->will($this->returnValue($def));

        $numeric_expression = new MockNumericExpression();

        /* @var $bean Opportunity */
        $return = $numeric_expression->isCurrencyField($bean, 'test_field');

        $this->assertEquals($expected, $return);
    }

    public function dataProviderTestIsCurrencyField()
    {
        return array(
            array(
                array(
                    'type' => 'decimal',
                    'dbType' => 'decimal',
                    'custom_type' => 'currency'
                ),
                true
            ),
            array(
                array(
                    'type' => 'decimal',
                    'dbType' => 'currency',
                ),
                true
            ),
            array(
                array(
                    'type' => 'currency',
                ),
                true
            ),
            array(
                array(
                    'type' => 'decimal',
                    'dbType' => 'decimal',
                    'custom_type' => 'decimal'
                ),
                false
            ),
            array(
                array(
                    'type' => 'decimal',
                    'dbType' => 'decimal',
                ),
                false
            ),
            array(
                array(
                    'type' => 'decimal',
                ),
                false
            )

        );
    }
}

class MockNumericExpression extends NumericExpression
{
    public function evaluate()
    {
    }

    public static function getJSEvaluate()
    {

    }

    public static function getOperationName()
    {
        return 'MockNumeric';
    }

    public function isCurrencyField($bean, $field)
    {
        return parent::isCurrencyField($bean, $field);
    }
}
