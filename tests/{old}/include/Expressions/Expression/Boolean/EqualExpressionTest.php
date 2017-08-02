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

class EqualExpressionTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function dataProviderTestEqual()
    {
        return array(
            array('equal(true, 1)', 'true'),
            array('equal(0, "")', 'true'),
            array('equal(1, "true")', 'false'),
            array('equal(true, "true")', 'true'),
            array('equal("true", 1)', 'false'),
            array('equal(false, equal(0, ""))', 'false'),
            array('equal(false, 0)', 'true'),
            array('equal(false, "")', 'true'),
            array('equal(false, "false")', 'true'),
        );
    }

    /**
     * @dataProvider dataProviderTestEqual
     *
     * @param $status
     * @param $expected
     * @throws PHPUnit_Framework_Exception
     * @throws Exception
     */
    public function testIsForecastClosedEvaluate($expr, $expected)
    {

        $context = $this->getMockBuilder('SugarBean')->getMock();

        $result = Parser::evaluate($expr, $context)->evaluate();

        $this->assertSame($expected, strtolower($result));
    }
}
