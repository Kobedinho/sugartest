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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Index;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexPool;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexPool
 *
 */
class IndexPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::registerStrategies
     *  @covers ::addStrategy
     */
    public function testRegisterStrategies()
    {
        $indexPool = $this->getIndexPoolMock();
        TestReflection::callProtectedMethod($indexPool, 'registerStrategies');

        $strategies = TestReflection::getProtectedValue($indexPool, 'strategies');
        $this->assertEquals($strategies['static'], '\Sugarcrm\Sugarcrm\Elasticsearch\Index\Strategy\StaticStrategy');
    }

    /**
     * @covers ::normalizeIndexName
     * @dataProvider providerTestNormalizeIndexName
     */
    public function testNormalizeIndexName($prefix, $name, $output)
    {
        $indexPool = $this->getIndexPoolMock();
        TestReflection::setProtectedValue($indexPool, 'prefix', $prefix);
        $res = $indexPool->normalizeIndexName($name);
        $this->assertEquals($res, $output);
    }

    public function providerTestNormalizeIndexName()
    {
        return array(
            array(
                'foo',
                'bar',
                'foo_bar'
            ),
            array(
                '',
                'bar',
                'bar'
            ),
            array(
                '',
                'Bar',
                'bar'
            ),
            array(
                'Foo',
                'Bar',
                'foo_bar'
            ),
        );
    }

    /**
     * @covers ::getStrategy
     * @dataProvider providerTestGetStrategy
     */
    public function testTestGetStrategy($module, $config, $loadedId, $strategies, $output)
    {
        $indexPool = $this->getIndexPoolMock();
        TestReflection::setProtectedValue($indexPool, 'config', $config);

        $staticStrategy = $this->getStaticStrategyMock();
        $staticStrategy->setIdentifier($loadedId);

        $loaded = array($loadedId => $staticStrategy);
        TestReflection::setProtectedValue($indexPool, 'loaded', $loaded);

        TestReflection::setProtectedValue($indexPool, 'strategies', $strategies);
        $class = $indexPool->getStrategy($module);
        $this->assertEquals($class->getIdentifier(), $output);
    }

    public function providerTestGetStrategy()
    {
        return array(
            array(
                'foo',
                array(
                    'foo' => array(
                        'strategy' => 'archive',
                    )
                ),
                'archive',
                array(),
                'archive'
            ),
            array(
                'bar',
                array(),
                '',
                array(
                    IndexPool::DEFAULT_STRATEGY => '\Sugarcrm\Sugarcrm\Elasticsearch\Index\Strategy\StaticStrategy'
                ),
                IndexPool::DEFAULT_STRATEGY,
            ),
        );
    }

    /**
     * Get IndexPoolTest Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexPool
     */
    protected function getIndexPoolMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexPool')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Get StaticStrategy Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Index\Strategy\StaticStrategy
     */
    protected function getStaticStrategyMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Index\Strategy\StaticStrategy')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

}
