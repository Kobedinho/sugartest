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

namespace Sugarcrm\SugarcrmTestsUnit\SearchEngine;

use Sugarcrm\Sugarcrm\SearchEngine\SearchEngine;
use Sugarcrm\Sugarcrm\SearchEngine\Engine\EngineInterface;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\SearchEngine\SearchEngine
 */
class SearchEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::hasCapability
     * @dataProvider dataProviderTestHasCapability
     *
     * @param string $interface
     * @param string $capability
     * @param boolean $expected
     */
    public function testHasCapability($interface, $capability, $expected)
    {
        $engine = new SearchEngine($this->createMock($interface));
        $this->assertSame($expected, $engine->hasCapability($capability));
    }

    public function dataProviderTestHasCapability()
    {
        return array(
            array(
                'Sugarcrm\Sugarcrm\SearchEngine\Engine\EngineInterface',
                'DoesNotExist',
                false,
            ),
            array(
                'Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\GlobalSearchCapable',
                'FakeCapability',
                false,
            ),
            array(
                'Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\GlobalSearchCapable',
                'GlobalSearch',
                true,
            )
        );
    }

    /**
     * @covers ::getEngine
     * @dataProvider dataProviderTestGetEngine
     *
     * @param EngineInterface $engineObject
     */
    public function testGetEngine(EngineInterface $engineObject)
    {
        $engine = new SearchEngine($engineObject);
        $this->assertSame($engineObject, $engine->getEngine());
    }

    public function dataProviderTestGetEngine()
    {
        return array(
            array($this->createMock('Sugarcrm\Sugarcrm\SearchEngine\Engine\EngineInterface')),
            array($this->createMock('Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\GlobalSearchCapable')),
        );
    }

    /**
     * @covers ::newEngine
     * @expectedException \RuntimeException
     */
    public function testNewEngineExceptions()
    {
        $engine = SearchEngine::newEngine('Unknown');
    }

    /**
     * @covers ::newEngine
     * @dataProvider dataProviderTestNewEngine
     *
     *
     * @param string $type
     * @param array $config
     */
    public function testNewEngine($type, array $config)
    {
        $engine = SearchEngine::newEngine($type, $config);
        $this->assertSame($config, $engine->getEngineConfig());
    }

    public function dataProviderTestNewEngine()
    {
        return array(
            array(
                'Elastic',
                array('foo' => 'bar', 0 => 'sweet', 'config' => true),
            ),
        );
    }
}
