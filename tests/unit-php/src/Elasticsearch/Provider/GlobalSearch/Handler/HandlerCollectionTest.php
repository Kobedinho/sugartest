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

use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\HandlerCollection;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\HandlerCollection
 *
 */
class HandlerCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::addHandler
     * @covers ::getIterator
     * @covers ::__construct
     */
    public function testAddHandler()
    {
        $provider = $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();

        $handler = $this->createMock('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\HandlerInterface');

        $handler->expects($this->once())
            ->method('setProvider')
            ->with($this->equalTo($provider));

        $collection = new HandlerCollection($provider);
        $collection->addHandler($handler);

        $iterator = $collection->getIterator();
        $this->assertInstanceOf('ArrayIterator', $iterator);

        foreach ($iterator as $item) {
            $this->assertSame($handler, $item);
        }
    }
}
