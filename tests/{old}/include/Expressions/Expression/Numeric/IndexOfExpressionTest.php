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

class IndexOfExpressionTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderTestEvaluate
     */
    public function testEvaluate($test, $expected)
    {
        $result = Parser::evaluate($test)->evaluate();
        $this->assertEquals($expected, $result);
    }

    public function dataProviderTestEvaluate()
    {
        return array(
            array('indexOf("a", createList("a", "b", "c"))', 0),
            array('indexOf("b", createList("a", "b", "c"))', 1),
            array('indexOf("c", createList("a", "b", "c"))', 2),
            array('indexOf("foo", createList("a", "b", "c"))', -1)
        );
    }
}

