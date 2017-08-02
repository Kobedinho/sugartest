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

require_once 'include/workflow/field_utils.php';

/**
 * Class FieldUtilsTest
 *
 * Test field_utils.php functions
 */
class FieldUtilsTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Test value_calc advanced workflow action
     *
     * @dataProvider dataProviderAdvancedActionValueCalc
     */
    public function testAdvancedActionValueCalc($operation, $operationValue, $field, $fieldValue, $expected)
    {
        $bean = BeanFactory::newBean('Accounts');

        $metaArray = array(
            'adv_type' => 'value_calc',
            'ext1' => $operation,
            'value' => $operationValue,
        );

        $bean->$field = $fieldValue;

        $value = process_advanced_actions($bean, $field, $metaArray, $bean);
        $this->assertEquals($expected, $value, 'Value calc returns incorrect value');
    }

    public static function dataProviderAdvancedActionValueCalc()
    {
        return array(
            array('+', 1, 'test', 1, 2),
            array('-', 1, 'test', 3, 2),
            array('*', 1, 'test', 3, 3),
            array('/', 3, 'test', 3, 1),
        );
    }
}
