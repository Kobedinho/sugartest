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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Mapping;

use Sugarcrm\Sugarcrm\Elasticsearch\Provider\ProviderCollection;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Mapping\MappingManager
 *
 */
class MappingManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers ::buildMapping
     * @dataProvider providerTestBuildMapping
     */
    public function testBuildMapping($modules, $mapping1, $mapping2)
    {
        $numCalls = count($modules);
        $provider1 = $this->getProviderMock(array('buildMapping'));
        $provider1->expects($this->exactly($numCalls))
            ->method('buildMapping')
            ->will($this->returnValue($mapping1));

        $provider2 = $this->getProviderMock(array('buildMapping'));
        $provider2->expects($this->exactly($numCalls))
            ->method('buildMapping')
            ->will($this->returnValue($mapping2));

        $providers = new ProviderCollection($this->getContainerMock(), array($provider1, $provider2));

        $mappingManager = $this->getMappingManagerMock();
        $mappingManager->buildMapping($providers, $modules);

    }

    public function providerTestBuildMapping()
    {
        return array(
            array(
                array('Accounts', 'Contacts', 'Leads'),
                array('mapping1' => array('type' => 'string')),
                array('mapping2' => array('type' => 'text')),
            ),
        );
    }

    /**
     * Get Provider Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch
     */
    protected function getProviderMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Get MappingManagerTest Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Mapping\MappingManager
     */
    protected function getMappingManagerMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Mapping\MappingManager')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Get Container Mock
     * @param array $methods
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
