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

require_once 'include/utils/expression_utils.php';

/**
 * Class ExpressionTranslateOperatorTest
 * Check if translate_operator works properly and covers all operators
 */
class ExpressionTranslateOperatorTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider operatorDataset
     */
    public function testExpressionTranslateOperator($operator, $type, $expected)
    {
        $operator = translate_operator($operator, $type);

        $this->assertEquals($expected, $operator, "Operator translated improperly");
    }

    public static function operatorDataset()
    {
        return array(
            array('Equals', 'php', '=='),
            array('Is empty', 'php', '=='),
            array('Less Than', 'php', '<'),
            array('More Than', 'php', '>'),
            array('Does not Equal', 'php', '!='),
            array('Is not empty', 'php', '!='),
            array('Equals', 'sql', '='),
            array('Is empty', 'sql', '='),
            array('Less Than', 'sql', '<'),
            array('More Than', 'sql', '>'),
            array('Does not Equal', 'sql', '!='),
            array('Is not empty', 'sql', '!='),
        );
    }
}
