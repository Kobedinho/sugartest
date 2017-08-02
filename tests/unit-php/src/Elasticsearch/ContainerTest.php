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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch;

use Sugarcrm\Sugarcrm\Elasticsearch\Container;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Container
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__get
     * @dataProvider dataProviderTestGetOverloadLazyLoad
     *
     * @param string $property
     */
    public function testGetOverloadLazyLoad($property)
    {
        $initMethod = 'init' . ucfirst($property);
        $container = $this->getContainerMock(array($initMethod));
        $container->expects($this->once())
            ->method($initMethod);

        $container->$property;
    }

    public function dataProviderTestGetOverloadLazyLoad()
    {
        return array(
            array('logger'),
            array('metaDataHelper'),
            array('queueManager'),
            array('client'),
            array('indexPool'),
            array('indexManager'),
            array('mappingManager'),
            array('indexer'),
        );
    }

    /**
     * @covers ::getConfig
     * @covers ::setConfig
     */
    public function testSetConfig()
    {
        $container = $this->getContainerMock();

        // empty base values
        $this->assertEquals(array(), $container->getConfig('engine'));
        $this->assertEquals(array(), $container->getConfig('global'));

        // default value
        $this->assertEquals(array('default'), $container->getConfig('foo', array('default')));

        // setter existing key
        $container->setConfig('engine', array('bar'));
        $this->assertEquals(array('bar'), $container->getConfig('engine'));

        // setter new key
        $container->setConfig('new', array('beer'));
        $this->assertEquals(array('beer'), $container->getConfig('new'));
    }

    /**
     * @covers ::__construct
     * @covers ::registerProviders
     * @covers ::registerProvider
     * @covers ::getRegisteredProviders
     * @covers ::isProviderAvailable
     * @covers ::unregisterProvider
     */
    public function testRegisterProviders()
    {
        // test if default providers are properly registered
        $container = new Container();
        $this->assertEquals(array('GlobalSearch', 'Visibility'), $container->getRegisteredProviders());

        // Register/unregister new provider
        $this->assertFalse($container->isProviderAvailable('new'));
        $container->registerProvider('new', 'class');
        $this->assertTrue($container->isProviderAvailable('new'));
        $container->unregisterProvider('new');
        $this->assertFalse($container->isProviderAvailable('new'));
    }

    /**
     * @covers ::getProvider
     * @dataProvider dataProviderTestGetProvider
     *
     * @param string $provider
     * @param string $class
     */
    public function testGetProvider($provider, $class)
    {
        $user = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();

        $container = $this->getContainerMock(array('getCurrentUser'));

        $container->expects($this->once())
            ->method('getCurrentUser')
            ->will($this->returnValue($user));

        $container->registerProviders();

        $provider = $container->getProvider($provider);
        $this->assertInstanceOf($class, $provider);
    }

    public function dataProviderTestGetProvider()
    {
        return array(
            array(
                'GlobalSearch',
                '\Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch',
            ),
        );
    }

    /**
     * Get Container mock
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Container
     */
    protected function getContainerMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Container')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
