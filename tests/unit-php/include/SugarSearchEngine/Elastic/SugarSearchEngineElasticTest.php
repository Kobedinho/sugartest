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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarSearchEngine\Elastic;

/**
 *
 * @coversDefaultClass \SugarSearchEngineElastic
 *
 */
class SugarSearchEngineElasticTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::search
     * @dataProvider providerTestSearch
     */
    public function testSearch($query, $offset, $limit, array $options)
    {
        $engineMethods = array(
            'isAvailable',
            'search',
            'term',
        );

        $engine = $this->getMockBuilder('Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\GlobalSearchCapable')
            ->setMethods($engineMethods)
            ->getMockForAbstractClass();

        // stub availability check
        $engine->expects($this->once())
            ->method('isAvailable')
            ->will($this->returnValue(true));

        // stub search
        $resultSet = $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Adapter\ResultSet')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $engine->expects($this->any())
            ->method('search')
            ->will($this->returnValue($resultSet));

        /* wire frame testing */

        $engine->expects($this->once())
            ->method('term')
            ->with($this->equalTo($query));

        $engine->expects($this->once())
            ->method('offset')
            ->with($this->equalTo($offset));

        $engine->expects($this->once())
            ->method('limit')
            ->with($this->equalTo($limit));

        $engine->expects($this->once())
            ->method('highlighter')
            ->with($this->equalTo(true));

        if (isset($options['moduleFilter'])) {
            $engine->expects($this->once())
                ->method('from')
                ->with($this->equalTo($options['moduleFilter']));
        }

        // mock logger
        $logger = $this->getMockBuilder('LoggerManager')
            ->disableOriginalConstructor()
            ->getMock();

        // tests search
        $sut = $this->getMockBuilder('SugarSearchEngineElastic')
            ->setConstructorArgs(array(array(), $engine, $logger))
            ->setMethods(array('createResultSet'))
            ->getMock();

        $sut->search($query, $offset, $limit, $options);
    }

    public function providerTestSearch()
    {
        return array(
            array(
                'find this',
                10,
                30,
                array(),
            ),
            array(
                'find this',
                10,
                30,
                array(
                    'moduleFilter' => array('Accounts', 'Contacts'),
                ),
            ),
        );
    }

    /**
     * @param null|array $methods
     * @return \SugarSearchEngineElastic
     */
    protected function getElasticMock($methods = null)
    {
        return $this->getMockBuilder('SugarSearchEngineElastic')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
