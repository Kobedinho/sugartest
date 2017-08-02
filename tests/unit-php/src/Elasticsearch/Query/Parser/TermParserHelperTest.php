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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Query\Parser;

use Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\TermParserHelper;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\TermParserHelper
 *
 */
class TermParserHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::isOperator
     * @dataProvider providerIsOperatorTest
     *
     */
    public function testIsOperator($operaor, $expectecd)
    {
        $this->assertSame(TermParserHelper::isOperator($operaor), $expectecd);
    }

    public function providerIsOperatorTest()
    {
        return array(
            array('AND', true),
            array('OR', true),
            array('NOT', true),
            array('|', true),
            array('&', true),
            array('-', true),
            array('', false),
            array('notor', false),
            array('and', false),
            array('or', false),
            array('not', false),
            array('And', false),
            array('Or', false),
            array('Not', false),
        );
    }

    /**
     * @covers ::isAndOperator
     * @dataProvider providerIsAndOperatorTest
     *
     */
    public function testIsAndOperator($operaor, $expectecd)
    {
        $this->assertSame(TermParserHelper::isAndOperator($operaor), $expectecd);
    }

    public function providerIsAndOperatorTest()
    {
        return array(
            array('AND', true),
            array('OR', false),
            array('NOT', false),
            array('|', false),
            array('&', true),
            array('-', false),
            array('', false),
        );
    }

    /**
     * @covers ::isOrOperator
     * @dataProvider providerIsOrOperatorTest
     *
     */
    public function testIsOrOperator($operaor, $expectecd)
    {
        $this->assertSame(TermParserHelper::isOrOperator($operaor), $expectecd);
    }

    public function providerIsOrOperatorTest()
    {
        return array(
            array('AND', false),
            array('OR', true),
            array('NOT', false),
            array('|', true),
            array('&', false),
            array('-', false),
            array('', false),
        );
    }

    /**
     * @covers ::isNotOperator
     * @dataProvider providerIsNotOperatorTest
     *
     */
    public function testIsNotOperator($operaor, $expectecd)
    {
        $this->assertSame(TermParserHelper::isNotOperator($operaor), $expectecd);
    }

    public function providerIsNotOperatorTest()
    {
        return array(
            array('AND', false),
            array('OR', false),
            array('NOT', true),
            array('|', false),
            array('&', false),
            array('-', true),
            array('', false),
        );
    }
}
