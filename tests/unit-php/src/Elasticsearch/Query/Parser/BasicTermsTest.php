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

use Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\BasicTerms;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\TermParserHelper;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\BasicTerms
 *
 */
class BasicTermsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getTerms
     * @covers ::getOperator
     * @covers ::addTerm
     * @covers ::toArray
     * @dataProvider providerBasicTermsTest
     *
     */
    public function testBasicTerms($operaor, array $terms, $additionalTerm, $expectecd)
    {
        $term = new BasicTerms($operaor, $terms);

        $this->assertSame(TermParserHelper::getOperator($operaor), $term->getOperator());
        $this->assertSame($terms, $term->getTerms());

        $term->addTerm($additionalTerm);
        $this->assertEquals($term->toArray(), $expectecd);
    }

    public function providerBasicTermsTest()
    {
        return array(
            // add no new term
            array(
                'AND',
                array('James', 'Bond'),
                '',
                array('AND' => array('James', 'Bond')),
            ),
            // normal case, 'AND'
            array(
                'AND',
                array('James', 'Bond'),
                'Movie',
                array('AND' => array('James', 'Bond', 'Movie')),
            ),
            // normal case, 'NOT'
            array(
                'NOT',
                array('James', 'Bond'),
                'Movie',
                array('NOT' => array('James', 'Bond', 'Movie')),
            ),
            // normal case, 'OR'
            array(
                'OR',
                array('James', 'Bond'),
                'Movie',
                array('OR' => array('James Bond Movie')),
            ),
            // 'AND', symbol operator
            array(
                '&',
                array('James', 'Bond'),
                'Movie',
                array('AND' => array('James', 'Bond', 'Movie')),
            ),
            // 'NOT', symbol operator
            array(
                '-',
                array('James', 'Bond'),
                'Movie',
                array('NOT' => array('James', 'Bond', 'Movie')),
            ),
            // symbol operator, 'OR' operator
            array(
                '|',
                array('James', 'Bond'),
                'Movie',
                array('OR' => array('James Bond Movie')),
            ),
            // 'OR' operator, combine terms into a string
            array(
                'OR',
                array('James', 'Bond'),
                'Movie',
                array('OR' => array('James Bond Movie')),
            ),
            // nexted terms operator, combine terms into a string
            array(
                'OR',
                array('James', 'Bond'),
                new BasicTerms('AND', array('M6', '007')),
                array('OR' => array(array('AND' => array('M6', '007')), 'James Bond')),
            ),
        );
    }

    /**
     * @covers ::__construct
     * @dataProvider providerBasicTermsTestException
     *
     * @expectedException Sugarcrm\Sugarcrm\Elasticsearch\Exception\QueryBuilderException
     */
    public function testBasicTermsException($operaor)
    {
        new BasicTerms($operaor, array('abc'));
    }

    public function providerBasicTermsTestException()
    {
        return array(
            array('ands'),
            array('&&'),
        );
    }
}
