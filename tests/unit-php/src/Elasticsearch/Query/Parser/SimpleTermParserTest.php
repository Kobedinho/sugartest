<?php
/*
 * Your installation OR use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do NOT agree to all of the applicable terms OR do NOT have the
 * authority to bind the entity as an authorized representative, then do not
 * install OR use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Query\Parser;

use Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\SimpleTermParser;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\TermParserHelper;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\SimpleTermParser
 *
 */
class SimpleTermParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::parse
     * @covers ::preProcess
     * @covers ::compressTerms
     * @covers ::createAndNotTerms
     * @covers ::setDefaultOperator
     * @covers ::getDefaultOperator
     *
     * @dataProvider providerParseTest
     *
     */
    public function testParse($terms, $defaultOperator, $expectecd)
    {
        $parser = new SimpleTermParser();
        $parser->setDefaultOperator($defaultOperator);
        if (TermParserHelper::getOperator($defaultOperator)) {
            $this->assertSame(TermParserHelper::getOperator($defaultOperator), $parser->getDefaultOperator());
        }

        $this->assertSame($parser->parse($terms), $expectecd);
    }

    public function providerParseTest()
    {
        return array(
            // empty term always returns BasicTerm with 'OR'
            array('', 'OR', array('OR' => array(' '))),
            array('', '&', array('OR' => array(' '))),
            // to treat '-' as char if it is NOT leading by space
            array('a b-c d', 'OR', array('OR' => array('a b-c d'))),
            // leading 'NOT' operator
            array('-a b c', 'OR', array('OR' => array(array('NOT' => array('a')), 'b c'))),
            // combined cases without bracket
            array('a b OR c', 'OR', array('OR' => array('a b c'))),
            array('a b|c', 'OR', array('OR' => array('a b c'))),
            array('a b AND c', '&', array('AND' => array('a', 'b', 'c'))),
            array('a b&c', '&', array('AND' => array('a', 'b', 'c'))),
            array(
                'a b NOT c',
                'OR',
                array(
                    'OR' => array(array('AND' => array(array('AND' => array('b')), array('NOT' => array('c')))), 'a'),
                ),
            ),
            array(
                'a b -c',
                'OR',
                array(
                    'OR' => array(array('AND' => array(array('AND' => array('b')), array('NOT' => array('c')))), 'a'),
                ),
            ),
            array(
                'a b OR c d',
                '&',
                array('OR' => array(array('AND' => array('a', 'b')), array('AND' => array('c', 'd')))),
            ),
            array(
                'a AND b OR c & d',
                '|',
                array('OR' => array(array('AND' => array('a', 'b')), array('AND' => array('c', 'd')))),
            ),
            array(
                'a b NOT c',
                'AND',
                array('AND' => array(array('AND' => array('a', 'b')), array('NOT' => array('c')))),
            ),
            // expression with brackets
            array('(a b OR c)', 'OR', array('OR' => array('a b c'))),
            array(
                'a (b OR c) AND d',
                'OR',
                array('OR' => array(array('AND' => array(array('OR' => array('b c')), 'd')), 'a')),
            ),
            array(
                'a (b OR c) AND d',
                '&',
                array('AND' => array('a', array('OR' => array('b c')), 'd')),
            ),
            array(
                '(a OR b) AND (c OR d)',
                'OR',
                array('AND' => array(array('OR' => array('a b')), array('OR' => array('c d')))),
            ),
            // nested structure
            array(
                'a OR (b AND (c OR d))',
                'OR',
                array('OR' => array(array('AND' => array('b', array('OR' => array('c d')))), 'a')),
            ),
            // unbalanced braces, make a best guess
            array(
                'a OR (b AND (c OR d)',
                'OR',
                array('OR' => array(
                    array('OR' => array('a')),
                    array('AND' => array('b', array('OR' => array('c d'))))),
                ),
            ),
            // wrong parentheses
            array('a (b', 'OR', array('OR' => array(array('OR' => array('a')), 'b'))),
            array('a b) c', 'OR', array('OR' => array(array('OR' => array('a b')), 'c'))),
            array('a b &) c', 'OR', array('OR' => array(array('OR' => array('a b')), 'c'))),
            array('Johnson &) johnson', 'AND', array('AND' => array(array('OR' => array('Johnson')), 'johnson'))),
            array('Johnson & (johnson', 'AND', array('AND' => array(array('OR' => array('Johnson')), 'johnson'))),
            // end with operators
            array('a b c & AND OR | ', 'OR', array('OR' => array('a b c'))),
            // duplicated operators
            array('a b OR AND OR c &', 'OR', array('OR' => array('a b c'))),
            // phone number
            array('(128) 123-7944 text', 'OR', array('OR' => array(array('OR' => array('128')), '123-7944 text'))),
            array('(128) 123-7944 text', 'AND', array('AND' => array(array('OR' => array('128')), '123-7944', 'text'))),
            // using default operator
            array('(128) 123-7944 text', 'nt', array('AND' => array(array('OR' => array('128')), '123-7944', 'text'))),
            array(
                'gmail.com (-kate OR -smith OR dean)',
                '&',
                array('AND' =>
                    array(
                        'gmail.com',
                        array(
                            'OR' => array(
                                array('NOT' => array('kate')),
                                array('NOT' => array('smith')),
                                'dean',
                            ),
                        ),
                    ),
                ),
            ),
            // test lower case 'and', 'or' and 'or', which are not operators
            array('a and b or c not d', 'OR', array('OR' => array('a and b or c not d'))),
            array('a Or AND and', '&', array('AND' => array('a', 'Or', 'and'))),
            // terms with spaces
            array('a   b   AND    and', '&', array('AND' => array('a', 'b', 'and'))),
            array('        ', '&', array('OR' => array(' '))),
            array('   AND     ', '&', array('OR' => array(' '))),
        );
    }
}
