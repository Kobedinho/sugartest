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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch\Handler;

use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\HandlerFilterIterator;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\HandlerCollection;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\HandlerFilterIterator
 *
 */
class HandlerFilterIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::setInterface
     * @covers ::accept
     * @dataProvider providerTestIterator
     */
    public function testIterator(HandlerCollection $collection, $filter, $expected)
    {
        $iterator = new HandlerFilterIterator($collection->getIterator(), $filter);
        $this->assertCount(count($expected), $iterator);
        foreach ($iterator as $key => $item) {
            $this->assertInstanceOf($expected[$key], $item);
        }
    }

    public function providerTestIterator()
    {
        return array(
            // no filter
            array(
                $this->getCollectionFixture(array('Analysis', 'Mapping')),
                null,
                array(
                    0 => $this->getHandlerInterface('Analysis'),
                    1 => $this->getHandlerInterface('Mapping')
                ),
            ),
            // filter ourself
            array(
                $this->getCollectionFixture(array('Analysis')),
                'Analysis',
                array(
                    0 => $this->getHandlerInterface('Analysis')
                ),
            ),
            // no results
            array(
                $this->getCollectionFixture(array('Analysis')),
                'Mapping',
                array(),
            ),
            // multiple different interfaces
            array(
                $this->getCollectionFixture(array('Analysis', 'Mapping')),
                'Analysis',
                array(
                    0 => $this->getHandlerInterface('Analysis')
                ),
            ),
            // multiple different interfaces with duplicates
            array(
                $this->getCollectionFixture(array('Analysis', 'Mapping', 'SearchFields', 'Mapping')),
                'Mapping',
                array(
                    1 => $this->getHandlerInterface('Mapping'),
                    3 => $this->getHandlerInterface('Mapping'),
                ),
            ),
        );
    }

    /**
     * Get collection fixture
     * @param array $interfaces
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\HandlerCollection
     */
    protected function getCollectionFixture(array $interfaces)
    {
        $collection = new HandlerCollection($this->getProviderMock());
        foreach ($interfaces as $interface) {
            $handler = $this->createMock($this->getHandlerInterface($interface));
            $collection->addHandler($handler);
        }
        return $collection;
    }

    /**
     * Get fully qualified interfacename
     * @param string $interface
     * @return string
     */
    protected function getHandlerInterface($interface)
    {
        return sprintf(
            'Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\%sHandlerInterface',
            $interface
        );
    }

    /**
     * Get GlobalSearch provider mock
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch
     */
    protected function getProviderMock()
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
    }
}
