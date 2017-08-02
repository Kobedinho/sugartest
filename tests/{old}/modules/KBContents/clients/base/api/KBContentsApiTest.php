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


class KBContentsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var RestService
     */
    protected $service = null;

    /**
     * @var KBContentsApi
     */
    protected $api = null;

    /**
     * @var KBContentsMock
     */
    protected $bean;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = $this->createPartialMock('KBContentsApi', array('getElasticQueryBuilder'));
        $this->bean = SugarTestKBContentUtilities::createBean();
    }

    public function tearDown()
    {
        $this->service = null;
        $this->api = null;

        SugarTestKBContentUtilities::removeAllCreatedBeans();
        SugarTestHelper::tearDown();
    }

    public function testRelatedDocuments()
    {
        $builderMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Query\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('setQuery', 'executeSearch', 'addFilter'))
            ->getMock();

        $resultSetMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Adapter\ResultSet')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        $builderMock->expects($this->any())->method('executeSearch')->will($this->returnValue($resultSetMock));
        $this->api->expects($this->any())->method('getElasticQueryBuilder')->will($this->returnValue($builderMock));

        $result = $this->api->relatedDocuments($this->service, array(
            'module' => $this->bean->module_name,
            'record' => $this->bean->id,
        ));

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('next_offset', $result);
        $this->assertArrayHasKey('records', $result);
        $this->assertInternalType('array', $result['records']);
    }
}
