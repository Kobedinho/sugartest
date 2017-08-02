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

namespace Sugarcrm\SugarcrmTestsUnit\inc\Expression\Parser;

/**
 * Class ParserTest
 *
 * @coversDefaultClass Parser
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider dataProviderIsRelatedExpression
     *
     * @covers ::isRelatedExpression
     * @param string $formula
     * @param array $function_map
     * @param boolean $expected
     */
    public function testIsRelatedExpression($formula, $function_map, $expected)
    {
        \Parser::$function_cache = $function_map;
        $expr = \Parser::evaluate($formula);
        $this->assertEquals($expected, \Parser::isRelatedExpression($expr));
    }

    public static function dataProviderIsRelatedExpression()
    {
        return array(
            array(
                'count($revenuelineitems)',
                array(
                    'count' => array(
                        'class' => 'CountRelatedExpression',
                        'src' => 'include/Expressions/Expression/Numeric/CountRelatedExpression.php',
                    )
                ),
                true
            ),
            array(
                'ceil(5.53)',
                array(
                    'ceil' => array(
                        'class' => 'CeilingExpression',
                        'src' => 'include/Expressions/Expression/Numeric/CeilingExpression.php',
                    )
                ),
                false
            )
        );
    }

    /**
     * @covers ::getFormulaRelateFields
     * @dataProvider dataProviderGetFormulaRelatedFields
     *
     */
    public function testGetFormulaRelateFields($formula, $linkName, $function_map, $expected)
    {
        \Parser::$function_cache = $function_map;
        $expr = \Parser::evaluate($formula);
        $this->assertSame($expected, \Parser::getFormulaRelateFields($expr, $linkName));
    }

    public static function dataProviderGetFormulaRelatedFields()
    {
        return array(
            array(
                'count($revenuelineitems)',
                '',
                array(
                    'count' => array(
                        'class' => 'CountRelatedExpression',
                        'src' => 'include/Expressions/Expression/Numeric/CountRelatedExpression.php',
                    )
                ),
                array()
            ),
            array(
                'countConditional($revenuelineitems,"sales_stage",createList("Closed Won","Closed Lost"))',
                '',
                array(
                    'countConditional' => array(
                        'class' => 'CountConditionalRelatedExpression',
                        'src' => 'include/Expressions/Expression/Numeric/CountConditionalRelatedExpression.php',
                    ),
                    'createList' => array(
                        'class' => 'DefineEnumExpression',
                        'src' => 'include/Expressions/Expression/Enum/DefineEnumExpression.php'
                    )
                ),
                array('sales_stage')
            ),
            // will return all rollup fields since we don't have a linkname specified
            array(
                'rollupSum($revenuelineitems, "likely_case")',
                '',
                array(
                    'rollupSum' => array(
                        'class' => 'SumRelatedExpression',
                        'src' => 'include/Expressions/Expression/Numeric/SumRelatedExpression.php',
                    )
                ),
                array('likely_case')
            ),
            // will return all rollup fields since we don't have a linkname specified
            array(
                'add(rollupSum($revenuelineitems, "likely_case"),rollupSum($opportunities, "amount"))',
                '',
                array(
                    'add' => array(
						'class'	=>	'AddExpression',
						'src'	=>	'include/Expressions/Expression/Numeric/AddExpression.php',
			        ),
                    'rollupSum' => array(
                        'class' => 'SumRelatedExpression',
                        'src' => 'include/Expressions/Expression/Numeric/SumRelatedExpression.php',
                    )
                ),
                array('likely_case', 'amount')
            ),
            // will return only amount since linkName is opportunities
            array(
                'add(rollupSum($revenuelineitems, "likely_case"),rollupSum($opportunities, "amount"))',
                'opportunities',
                array(
                    'add' => array(
						'class'	=>	'AddExpression',
						'src'	=>	'include/Expressions/Expression/Numeric/AddExpression.php',
			        ),
                    'rollupSum' => array(
                        'class' => 'SumRelatedExpression',
                        'src' => 'include/Expressions/Expression/Numeric/SumRelatedExpression.php',
                    )
                ),
                array('amount')
            ),
            // this should return an empty array since we are looking for opportunities, but we have revenuelineitems
            // in the formula
            array(
                'rollupSum($revenuelineitems, "amount")',
                'opportunities',
                array(
                    'rollupSum' => array(
                        'class' => 'SumRelatedExpression',
                        'src' => 'include/Expressions/Expression/Numeric/SumRelatedExpression.php',
                    )
                ),
                array()
            ),
        );
    }
}
