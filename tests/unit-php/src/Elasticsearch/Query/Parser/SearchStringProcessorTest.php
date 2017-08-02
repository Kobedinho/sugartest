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

use Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\SearchStringProcessor;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\Parser\SearchStringProcessor
 *
 */
class SearchStringProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::parse
     * @covers ::push
     * @dataProvider providerParseTest
     *
     */
    public function testParse($terms, $expectecd)
    {
        $result = SearchStringProcessor::parse($terms);
        $this->assertSame($result, $expectecd);
    }

    public function providerParseTest()
    {
        return array(
            array('', array()),
            array('a b or c', array('a', 'b', 'or', 'c')),
            array('(a b or c)', array(array('a', 'b', 'or', 'c'))),
            array('a b or c and d', array('a', 'b', 'or', 'c', 'and', 'd')),
            array('a (b or c) and d', array('a', array('b', 'or', 'c'), 'and', 'd')),
            array('(a or b) and (c or d)', array(array('a', 'or', 'b'), 'and', array('c', 'or', 'd'))),
            // nested structure
            array('a or (b and (c or d))', array('a', 'or', array('b', 'and', array('c', 'or', 'd')))),
            // unbalanced braces, make a best guess
            array('a or (b and (c or d)', array(array('a', 'or'), 'b', 'and', array('c', 'or', 'd'))),
            array('a (b', array(array('a'), 'b')),
            array('a b) c', array(array('a', 'b'), 'c')),
            array('a b &) c', array(array('a', 'b', '&'), 'c')),
        );
    }
}
