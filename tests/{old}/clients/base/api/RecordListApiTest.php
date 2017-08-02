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


/**
 * RecordList Api Test
 */
class RecordListApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var RecordListApi
     */
    protected $recordListApi;

    /**
     * @var RestService
     */
    protected $serviceMock;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->recordListApi = new RecordListApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for testRecordListCreate
     *
     * @return array
     */
    public function recordListCreateDataProvider()
    {
        return array(
            array(
                array(
                    'records' => array(1, 2, 3),
                    'module' => 'Accounts',
                ),
                'Accounts',
                array(1, 2, 3),
            ),
            array(
                array(
                    'records' => array(),
                    'module' => 'Contacts',
                ),
                'Contacts',
                array(),
            ),
            array(
                array(
                    'records' => array(3, 2, 1),
                    'module' => 'Contacts',
                ),
                'Contacts',
                array(3, 2, 1),
            ),
        );
    }

    /**
     * Test asserts behavior of recordListCreate
     *
     * @dataProvider recordListCreateDataProvider
     */
    public function testRecordListCreate($args, $moduleName, $records)
    {
        $result = $this->recordListApi->recordListCreate($this->serviceMock, $args);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('module_name', $result);
        $this->assertArrayHasKey('records', $result);

        $this->assertNotEmpty($result['id']);
        $this->assertEquals($moduleName, $result['module_name']);

        $this->assertEquals($records, $result['records']);
    }

    /**
     * Data provider for testRecordListDelete
     *
     * @return array
     */
    public function recordListDeleteDataProvider()
    {
        return array(
            array(
                'Accounts',
                array(1, 2, 3),
            ),
            array(
                'Accounts',
                array(),
            ),
            array(
                'Contacts',
                array(3, 2, 1),
            ),
        );
    }

    /**
     * Test asserts behavior of recordListDelete
     *
     * @dataProvider recordListDeleteDataProvider
     */
    public function testRecordListDelete($moduleName, array $records)
    {
        $recordListId = RecordListFactory::saveRecordList($records, $moduleName);

        $result = $this->recordListApi->recordListDelete($this->serviceMock, array(
            'module' => $moduleName,
            'record_list_id' => $recordListId,
        ));

        $this->assertNotEmpty($result);
        $this->assertTrue($result);
    }

    /**
     * Data provider for testRecordListGet
     *
     * @return array
     */
    public function recordListGetDataProvider()
    {
        return array(
            array(
                'Accounts',
                array(1, 2, 3),
            ),
            array(
                'Accounts',
                array(),
            ),
            array(
                'Contacts',
                array(3, 2, 1),
            ),
        );
    }

    /**
     * Test asserts behavior of recordListGet
     *
     * @dataProvider recordListGetDataProvider
     */
    public function testRecordListGet($moduleName, array $records)
    {
        $recordListId = RecordListFactory::saveRecordList($records, $moduleName);
        $result = $this->recordListApi->recordListGet($this->serviceMock, array(
            'module' => $moduleName,
            'record_list_id' => $recordListId,
        ));

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('module_name', $result);
        $this->assertArrayHasKey('records', $result);

        $this->assertNotEmpty($result['id']);
        $this->assertEquals($moduleName, $result['module_name']);
        $this->assertEquals($records, $result['records']);
    }
}
