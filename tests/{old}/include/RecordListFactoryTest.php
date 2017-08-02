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
 * RecordListFactory Test
 */
class RecordListFactoryTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for testGetRecordList()
     *
     * @return array
     */
    public function getRecordListDataProvider()
    {
        return array(
            array(
                'Accounts',
                array(1,2,3,4,5),
                array(1,2,3,4,5),
            ),
            array(
                'Cases',
                array(),
                array(),
            ),
            array(
                'Contacts',
                array(99, 30, 6),
                array(99, 30, 6),
            ),
        );
    }

    /**
     * Test for static method RecordListFactory::getRecordList()
     *
     * @dataProvider getRecordListDataProvider
     */
    public function testGetRecordList($module, array $recordList, array $expected)
    {
        $id = RecordListFactory::saveRecordList($recordList, $module);
        $this->assertNotEmpty($id);

        $result = RecordListFactory::getRecordList($id);

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);

        $this->arrayHasKey('records', $result);
        $this->arrayHasKey('module_name', $result);

        $this->assertEquals($expected, $result['records']);
        $this->assertEquals($module, $result['module_name']);
    }

    /**
     * Data provider for testSaveRecordList()
     *
     * @return array
     */
    public function saveRecordListDataProvider()
    {
        return array(
            array(
                'Accounts',
                array(1,2,3,4,5),
                array(7, 8, 9),
            ),
            array(
                'Contacts',
                array(),
                array(22, 34, 56),
            ),
            array(
                'Bugs',
                array(22, 34, 56),
                array(),
            ),
            array(
                'Cases',
                array(),
                array(),
            ),
        );
    }

    /**
     * Test for static method RecordListFactory::saveRecordList()
     *
     * @dataProvider saveRecordListDataProvider
     */
    public function testSaveRecordList($module, array $recordListForSave, array $recordListForUpdate)
    {
        // test create a new list.
        $recordListId = RecordListFactory::saveRecordList($recordListForSave, $module);

        $this->assertNotEmpty($recordListId);

        $records = RecordListFactory::getRecordList($recordListId);

        $this->assertNotEmpty($records);
        $this->assertInternalType('array', $records);
        $this->arrayHasKey('records', $records);
        $this->arrayHasKey('module_name', $records);
        $this->assertEquals($module, $records['module_name']);
        $this->assertEquals($recordListForSave, $records['records']);

        // test update created list
        $newRecordListId = RecordListFactory::saveRecordList($recordListForUpdate, $module, $recordListId);

        $this->assertEquals($recordListId, $newRecordListId);

        $records = RecordListFactory::getRecordList($newRecordListId);

        $this->assertNotEmpty($records);
        $this->assertInternalType('array', $records);
        $this->arrayHasKey('records', $records);
        $this->arrayHasKey('module_name', $records);
        $this->assertEquals($module, $records['module_name']);
        $this->assertEquals($recordListForUpdate, $records['records']);
    }


    /**
     * Test for static method RecordListFactory::deleteRecordList()
     */
    public function testDeleteRecordList()
    {
        $id = RecordListFactory::saveRecordList(array(1, 2, 3), 'Accounts');
        $this->assertNotEmpty($id);

        $result = RecordListFactory::deleteRecordList($id);
        $this->assertNotEmpty($result);

        $result = RecordListFactory::getRecordList($id);
        $this->assertEmpty($result);
    }
}
