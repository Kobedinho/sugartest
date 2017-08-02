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
 * @covers ForecastWorksheet
 */

require_once 'tests/{old}/SugarTestDatabaseMock.php';

class ForecastWorksheetTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var SugarTestDatabaseMock
     */
    protected static $db;

    public static function setUpBeforeClass()
    {
        self::$db = SugarTestHelper::setUp('mock_db');
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @param array $methods
     * @return PHPUnit_Framework_MockObject_MockObject|ForecastWorksheet
     */
    protected function getMockWorksheet(array $methods = array('save', 'getBean'))
    {
        if (!in_array('save', $methods)) {
            $methods[] = 'save';
        }

        if (!in_array('getBean', $methods)) {
            $methods[] = 'getBean';
        }

        return $this->getMockBuilder('ForecastWorksheet')
            ->setMethods($methods)
            ->getMock();
    }

    public static function dataProviderSaveWorksheet()
    {
        return array(
            array(
                'RevenueLineItem',
                'RevenueLineItems',
                'likely_case'
            ),
            array(
                'Opportunity',
                'Opportunities',
                'amount'
            )
        );
    }

    /**
     * @dataProvider dataProviderSaveWorksheet
     * @covers ForecastWorksheet::saveWorksheet
     */
    public function testSaveWorksheet($klass_name, $module_name, $likely_field)
    {
        $bean = $this->getMockBuilder($klass_name)
            ->setMethods(array('save'))
            ->getMock();

        $bean->id = 'test_bean_1';
        $bean->worst_case = '60.000000';
        $bean->best_case = '60.000000';

        $worksheet = $this->getMockWorksheet(array('ACLFieldAccess'));

        $worksheet->expects($this->exactly(2))
            ->method('ACLFieldAccess')
            ->withConsecutive(
                array('best_case', 'write'),
                array('worst_case', 'write')
            )
            ->willReturn(true);

        $worksheet->expects($this->once())
            ->method('getBean')
            ->with($module_name, $bean->id)
            ->willReturn($bean);

        $worksheet->parent_type = $module_name;
        $worksheet->parent_id = 'test_bean_1';
        $worksheet->likely_case = '50.000000';
        $worksheet->best_case = '50.000000';
        $worksheet->worst_case = '50.000000';
        $worksheet->date_closed = 'test';

        $worksheet->saveWorksheet(false);

        $this->assertEquals($worksheet->likely_case, $bean->$likely_field);
        $this->assertEquals($worksheet->best_case, $bean->best_case);
        $this->assertEquals($worksheet->worst_case, $bean->worst_case);
    }

    /**
     * @dataProvider dataProviderSaveWorksheet
     * @covers ForecastWorksheet::saveWorksheet
     */
    public function testSaveWorksheetDoesNotUpdateDateClosed($klass_name, $module_name, $likely_field)
    {
        $bean = $this->getMockBuilder($klass_name)
            ->setMethods(array('save'))
            ->getMock();

        $bean->id = 'test_bean_1';
        $bean->worst_case = '60.000000';
        $bean->best_case = '60.000000';
        $bean->date_closed = 'unit_test';

        $worksheet = $this->getMockWorksheet(array('ACLFieldAccess'));

        $worksheet->expects($this->exactly(2))
            ->method('ACLFieldAccess')
            ->withConsecutive(
                array('best_case', 'write'),
                array('worst_case', 'write')
            )
            ->willReturn(true);

        $worksheet->expects($this->once())
            ->method('getBean')
            ->with($module_name, $bean->id)
            ->willReturn($bean);

        $worksheet->parent_type = $module_name;
        $worksheet->parent_id = 'test_bean_1';
        $worksheet->likely_case = '50.000000';
        $worksheet->best_case = '50.000000';
        $worksheet->worst_case = '50.000000';
        $worksheet->date_closed = '';

        $worksheet->saveWorksheet(false);

        $this->assertEquals($worksheet->likely_case, $bean->$likely_field);
        $this->assertEquals('unit_test', $bean->date_closed);
    }

    /**
     * @dataProvider dataProviderSaveWorksheet
     * @covers ForecastWorksheet::saveWorksheet
     */
    public function testSaveWorksheetDoesNotOverwriteBestWorst($klass_name, $module_name, $likely_field)
    {
        $bean = $this->getMockBuilder($klass_name)
            ->setMethods(array('save'))
            ->getMock();

        $bean->id = 'test_bean_1';
        $bean->worst_case = '60.000000';
        $bean->best_case = '60.000000';

        $worksheet = $this->getMockWorksheet(array('ACLFieldAccess'));

        $worksheet->expects($this->exactly(2))
            ->method('ACLFieldAccess')
            ->withConsecutive(
                array('best_case', 'write'),
                array('worst_case', 'write')
            )
            ->willReturn(false);

        $worksheet->expects($this->once())
            ->method('getBean')
            ->with($module_name, $bean->id)
            ->willReturn($bean);

        $worksheet->parent_type = $module_name;
        $worksheet->parent_id = 'test_bean_1';
        $worksheet->likely_case = '50.000000';
        $worksheet->best_case = '50.000000';
        $worksheet->worst_case = '50.000000';
        $worksheet->date_closed = 'test';

        $worksheet->saveWorksheet(false);

        $this->assertEquals($worksheet->likely_case, $bean->$likely_field);
        $this->assertEquals('60.000000', $bean->best_case);
        $this->assertEquals('60.000000', $bean->worst_case);
    }

    /**
     * @covers ForecastWorksheet::setWorksheetArgs
     */
    public function testSetWorksheetArgs()
    {
        $args = array(
            'likely_case' => '50.00',
            'best_case' => '50.00',
        );
        $worksheet = $this->getMockWorksheet();
        $worksheet->setWorksheetArgs($args);
        $this->assertSame($args, $worksheet->args);
        foreach($args as $key => $val) {
            $this->assertSame($val, $worksheet->$key);
        }
    }

    public static function dataProviderSaveRelatedOpportunity()
    {
        return array(
            array(
                false,
                array(
                    'id' => 'test_opp_id',
                    'amount' => '50.000000',
                    'account_id' => 'test_account_id',
                ),
                true
            ),
            array(
                true,
                array(
                    'id' => 'test_opp_id',
                    'amount' => '50.000000',
                    'account_id' => 'test_account_id',
                    'account_name' => 'Test Account'
                ),
                false
            )
        );
    }

    /**
     * @dataProvider dataProviderSaveRelatedOpportunity
     * @covers ForecastWorksheet::saveRelatedOpportunity
     * @param $isCommit
     * @param $opp_values
     */
    public function testSaveRelatedOpportunity($isCommit, $opp_values, $acc_check)
    {
        $worksheet = $this->getMockWorksheet(array('retrieve_by_string_fields', 'copyValues', 'getRelatedName', 'removeMigratedRow'));

        $mockOpp = $this->getMockBuilder('Opportunity')
            ->setMethods(array('save'))
            ->getMock();

        foreach($opp_values as $key => $val) {
            $mockOpp->$key = $val;
        }

        $worksheet->expects($this->once())
            ->method('removeMigratedRow')
            ->with($mockOpp);

        if ($acc_check) {
            $worksheet->expects($this->once())
                ->method('getRelatedName')
                ->with('Accounts', $mockOpp->account_id)
                ->willReturn('Test Account');
        } else {
            $worksheet->expects($this->never())
                ->method('getRelatedName');
        }

        $worksheet->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->with(
                array(
                    'parent_type' => 'Opportunities',
                    'parent_id' => 'test_opp_id',
                    'draft' => ($isCommit === false) ? 1 : 0,
                    'deleted' => 0
                ),
                true,
                false
            );

        $worksheet->saveRelatedOpportunity($mockOpp, $isCommit);
    }

    /**
     * @covers ForecastWorksheet::saveRelatedProduct
     */
    public function testSaveRelatedProduct()
    {
        $worksheet = $this->getMockWorksheet(array('retrieve_by_string_fields', 'copyValues', 'getRelatedName', 'removeMigratedRow'));

        $mockRli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('save'))
            ->getMock();

        $values = array(
            'id' => 'test_rli_id',
            'account_name' => 'test_acc_name',
            'account_id' => 'test_acc_id',
            'opportunity_name' => 'opportunity_name',
            'opportunity_id' => 'opportunity_id',
            'product_template_name' => 'product_template_name',
            'product_template_id' => 'product_template_id',
            'category_name' => 'category_name',
            'category_id' => 'category_id',
        );

        foreach($values as $key => $val) {
            $mockRli->$key = $val;
        }

        $worksheet->expects($this->once())
            ->method('removeMigratedRow')
            ->with($mockRli);

        $worksheet->expects($this->never())
            ->method('getRelatedName');

        $worksheet->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->with(
                array(
                    'parent_type' => 'RevenueLineItems',
                    'parent_id' => 'test_rli_id',
                    'draft' => 1,
                    'deleted' => 0
                ),
                true,
                false
            );

        $worksheet->saveRelatedProduct($mockRli, false);
    }

    /**
     * @covers ForecastWorksheet::saveRelatedProduct
     */
    public function testSaveRelatedProductFetchedRelatedName()
    {
        $worksheet = $this->getMockWorksheet(array('retrieve_by_string_fields', 'copyValues', 'getRelatedName', 'removeMigratedRow'));

        $mockRli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('save'))
            ->getMock();

        $values = array(
            'id' => 'test_rli_id',
            'account_id' => 'account_id',
            'opportunity_id' => 'opportunity_id',
            'product_template_id' => 'product_template_id',
            'category_id' => 'category_id',
        );

        foreach($values as $key => $val) {
            $mockRli->$key = $val;
        }

        $worksheet->expects($this->once())
            ->method('removeMigratedRow')
            ->with($mockRli);



        $worksheet->expects($this->exactly(4))
            ->method('getRelatedName')
            ->withConsecutive(
                array('Accounts', 'account_id'),
                array('Opportunities', 'opportunity_id'),
                array('ProductTemplates', 'product_template_id'),
                array('ProductCategories', 'category_id')
            )
            ->willReturn('Unit Test');

        $worksheet->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->with(
                array(
                    'parent_type' => 'RevenueLineItems',
                    'parent_id' => 'test_rli_id',
                    'draft' => 1,
                    'deleted' => 0
                ),
                true,
                false
            );

        $worksheet->saveRelatedProduct($mockRli, false);
    }

    public function dataProviderCommitWorksheetReturnFalse()
    {
        return array(
            array(
                false,
                'test_timperiod_id',
            ),
            array(
                true,
                null,
            )
        );
    }

    /**
     * @covers ForecastWorksheet::commitWorksheet
     * @param $forecastSetup
     * @param $timePeriodId
     * @dataProvider dataProviderCommitWorksheetReturnFalse
     */
    public function testCommitWorksheetReturnFalse($forecastSetup, $timePeriodId)
    {
        Forecast::$settings = array(
            'is_setup' => $forecastSetup
        );

        $worksheet = $this->getMockWorksheet();

        $count = intval($forecastSetup);

        $tp = $this->createPartialMock('Timeperiod', array('save'));
        $tp->id = $timePeriodId;

        $worksheet->expects($this->exactly($count))
            ->method('getBean')
            ->with('TimePeriods', $timePeriodId)
            ->willReturn($tp);

        $actual = $worksheet->commitWorksheet('test_user_id', $timePeriodId);

        $this->assertFalse($actual);
    }


    public static function dataProviderCommitWorksheet()
    {
        return array(
            array(
                'RevenueLineItems',
                'RevenueLineItem',
                'account_link'
            ),
            array(
                'Opportunities',
                'Opportunity',
                'accounts'
            )
        );
    }

    /**
     * @dataProvider dataProviderCommitWorksheet
     * @covers ForecastWorksheet::commitWorksheet
     * @param string $type
     * @param string $bean
     */
    public function testCommitWorksheet($type, $bean, $link_name)
    {
        Forecast::$settings = array(
            'is_setup' => true,
            'forecast_by' => $type
        );

        $worksheet = $this->getMockWorksheet(
            array(
                'getSugarQuery',
                'removeReassignedItems',
                'processWorksheetDataChunk',
                'createUpdateForecastWorksheetJob'
            )
        );

        $worksheet->expects($this->once())
            ->method('removeReassignedItems')
            ->with('test_user_id', 'test_timeperiod_id', 1);

        $tp = $this->getMockBuilder('Timeperiod')->setMethods(array('save'))->getMock();
        $tp->id = 'test_timeperiod_id';

        $sq = $this->getMockBuilder('SugarQuery')->setMethods(array('execute'))->getMock();

        $bean = $this->getMockBuilder($bean)->setMethods(array('save', 'load_relationship'))->getMock();
        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(array('buildJoinSugarQuery'))
            ->disableOriginalConstructor()
            ->getMock();

        $link2->expects($this->once())
            ->method('buildJoinSugarQuery')
            ->with($sq, array('joinTableAlias' => 'account'));

        $bean->expects($this->once())
            ->method('load_relationship')
            ->with($link_name)
            ->willReturn(true);

        $bean->$link_name = $link2;

        $worksheet->expects($this->exactly(2))
            ->method('getBean')
            ->withConsecutive(
                array('TimePeriods', 'test_timeperiod_id'),
                array($type)
            )
            ->willReturnOnConsecutiveCalls(
                $tp,
                $bean
            );

        $worksheet->expects($this->once())
            ->method('getSugarQuery')
            ->willReturn($sq);

        $sq->expects($this->once())
            ->method('execute')
            ->willReturn(array(
                array('one'),
                array('two'),
                array('three'),
                array('four')
            ));

        $worksheet->expects($this->once())
            ->method('processWorksheetDataChunk')
            ->with($type, array(array('one')));

        $worksheet->expects($this->exactly(3))
            ->method('createUpdateForecastWorksheetJob')
            ->withConsecutive(
                array($type, array(array('two'))),
                array($type, array(array('three'))),
                array($type, array(array('four')))
            );

        $actual = $worksheet->commitWorksheet('test_user_id', 'test_timeperiod_id', 1);

        $this->assertTrue($actual);
    }


    public static function dataProviderRemoveMigratedRow()
    {
        return array(
            array(
                true
            ),
            array(
                false
            )
        );
    }
    /**
     * @dataProvider dataProviderRemoveMigratedRow
     * @covers ForecastWorksheet::removeMigratedRow
     */
    public function testRemoveMigratedRow($hasMigrated)
    {
        $worksheet = $this->getMockWorksheet(array('timeperiodHasMigrated', 'retrieve_by_string_fields'));

        $worksheet->fetched_row['date_closed'] = 'date_1';

        $bean = $this->getMockForAbstractClass('SugarBean', array('save'));
        $bean->fetched_row['date_closed'] = 'date_2';
        $bean->module_name = 'unit_test';
        $bean->id = 'unit_test_id';

        if($hasMigrated) {
            $worksheet->expects($this->once())
                ->method('getBean')
                ->willReturn($worksheet);

            $worksheet->expects($this->once())
                ->method('retrieve_by_string_fields')
                ->with(
                    array(
                        "parent_type" => 'unit_test',
                        "parent_id" => 'unit_test_id',
                        "draft" => 0,
                        "deleted" => 0,
                    ),
                    true,
                    false
                );

            $worksheet->expects($this->once())
                ->method('save');
        } else {
            $worksheet->expects($this->never())
                ->method('getBean');

            $worksheet->expects($this->never())
                ->method('retrieve_by_string_fields');

            $worksheet->expects($this->never())
                ->method('save');
        }

        $worksheet->expects($this->once())
            ->method('timeperiodHasMigrated')
            ->with('date_1', 'date_2')
            ->willReturn($hasMigrated);

        $actual = $worksheet->removeMigratedRow($bean);
        $this->assertSame($hasMigrated, $actual);
    }

    public function dataProviderCopyValues()
    {
        $bean = $this->createPartialMock('Opportunity', array('save', 'toArray'));
        $bean->amount = '50.000000';
        return array(
            array(
                array(
                    array('likely_case' => 'amount')
                ),
                array(
                    'amount' => '50.000000'
                )
            ),
            array(
                array(
                    'likely_case'
                ),
                array(
                    'likely_case' => '50.000000'
                )
            ),
            array(
                array(
                    array('likely_case' => 'amount')
                ),
                $bean
            ),

        );
    }
    /**
     * @dataProvider dataProviderCopyValues
     * @covers ForecastWorksheet::copyValues
     */
    public function testCopyValues($fields, $values)
    {
        if ($values instanceof SugarBean) {
            $values->expects($this->once())
                ->method("toArray")
                ->with()
                ->will($this->returnValue(array(
                    'amount' => '50.000000'
                )));
        }

        $worksheet = $this->getMockWorksheet();
        $worksheet->copyValues($fields, $values);
        $this->assertEquals('50.000000', $worksheet->likely_case);
    }

    public static function dataProviderProcessWorksheetDataChunk()
    {
        return array(
            array(
                'RevenueLineItems',
                'RevenueLineItem',
                array(
                    array('id' => 'test_rli')
                )
            ),
            array(
                'Opportunities',
                'Opportunity',
                array(
                    array('id' => 'test_opp')
                )
            )
        );
    }

    /**
     * @dataProvider dataProviderProcessWorksheetDataChunk
     * @covers ForecastWorksheet::processWorksheetDataChunk
     */
    public function testProcessWorksheetDataChunk($type, $bean, $data)
    {
        $worksheet = $this->getMockWorksheet(array('saveRelatedOpportunity', 'saveRelatedProduct'));
        $obj = $this->getMockBuilder($bean)
            ->setMethods(array('save', 'loadFromRow'))
            ->getMock();

        $worksheet->expects($this->exactly(2))
            ->method('getBean')
            ->withConsecutive(
                array($type),
                array('ForecastWorksheets')
            )
            ->willReturnOnConsecutiveCalls(
                $obj,
                $worksheet
            );

        $obj->expects($this->once())
            ->method('loadFromRow')
            ->with($data[0]);

        $method = ($type === 'Opportunities') ? 'saveRelatedOpportunity' : 'saveRelatedProduct';

        $worksheet->expects($this->once())
            ->method($method);

        $worksheet->processWorksheetDataChunk($type, $data);

    }

    /**
     * @covers ForecastWorksheet::createUpdateForecastWorksheetJob
     */
    public function testCreateUpdateForecastWorksheetJob()
    {
        $data = array('forecast_by' => 'unit_test', 'data' => array());
        $user_id = "bar";
        $sj = $this->createMock('SchedulersJob');
        $jq = $this->createPartialMock('SugarJobQueue', array('submitJob'));
        $fw = $this->getMockWorksheet(array('getJobQueue'));

        $jq->expects($this->once())
            ->method('submitJob')
            ->with($sj);

        $fw->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($sj));

        $fw->expects($this->once())
            ->method('getJobQueue')
            ->will($this->returnValue($jq));

        SugarTestReflection::callProtectedMethod($fw, 'createUpdateForecastWorksheetJob', array('unit_test', array(), $user_id));

        $this->assertEquals('Update ForecastWorksheets', $sj->name);
        $this->assertEquals('class::SugarJobUpdateForecastWorksheets', $sj->target);
        $this->assertEquals(json_encode($data), $sj->data);
        $this->assertEquals(0, $sj->retry_count);
        $this->assertEquals($user_id, $sj->assigned_user_id);

    }

    /**
     * @covers ForecastWorksheet::getRelatedName
     */
    public function testGetRelatedNameReturnsEmpty()
    {
       self::$db->addQuerySpy(
            'accountQuery',
            '/my_test_id/',
            array(
                array(
                    'name' => 'My Test Account'
                ),
            )
        );

        $forecast_worksheet = BeanFactory::newBean('ForecastWorksheets');
        $return = SugarTestReflection::callProtectedMethod(
            $forecast_worksheet,
            'getRelatedName',
            array('Accounts', 'test_id')
        );
        $this->assertEmpty($return);
    }

    /**
     * @covers ForecastWorksheet::getRelatedName
     */
    public function testGetRelatedNameReturnsName()
    {
        $this->markTestIncomplete('[BR-3362] Query Spy doesn\'t capture parameters in prepared statements');

        $acc_name = 'My Test Account';
        $acc_id = 'my_test_id';
        self::$db->addQuerySpy(
            'accountQuery',
            '/' . $acc_id . '/',
            array(
                array(
                    'name' => $acc_name
                ),
            )
        );

        $forecast_worksheet = BeanFactory::newBean('ForecastWorksheets');
        $return = SugarTestReflection::callProtectedMethod(
            $forecast_worksheet,
            'getRelatedName',
            array('Accounts', $acc_id)
        );
        $this->assertEquals($acc_name, $return);
    }

    /**
     * @dataProvider removeReassignedItemsProvider
     * @covers ForecastWorksheet::removeReassignedItems
     */
    public function testRemoveReassignedItems($settings, $expected, $tp_id, $execute_count, $beans)
    {
        $this->markTestIncomplete('[BR-3362] Testing SQL doesn\'t work with prepared statements');

        $fw = $this->getMockbuilder('ForecastWorksheet')
            ->setMethods(array(
                'getForecastSettings',
                'getBean',
                'getSugarQuery',
                'processRemoveChunk',
                'createRemoveReassignedJob',
            ))
            ->getMock();
        $tp = $this->createMock('TimePeriod');

        $sq = $this->getMockBuilder('SugarQuery')->setMethods(array('execute'))->getMock();

        $tp->id = $tp_id;

        $sql = "SELECT  fw.id id FROM forecast_worksheets fw JOIN forecast_worksheets fw2 ON "
            . "fw2.parent_id = fw.parent_id AND fw2.assigned_user_id != fw.assigned_user_id  WHERE fw.deleted = 0 "
            . "AND fw.draft = 0 AND (fw.assigned_user_id = 'foo')";

        $sq->expects($this->exactly($execute_count))
            ->method('execute')
            ->will($this->returnValue($beans));

        $fw->expects($this->once())
            ->method('getForecastSettings')
            ->will($this->returnValue($settings));

        $fw->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($tp));

        $fw->expects($this->exactly($execute_count))
            ->method('getSugarQuery')
            ->will($this->returnValue($sq));

        $fw->expects($this->atLeast(((count($beans) == 0) || ($execute_count == 0))? 0:1))
            ->method('processRemoveChunk');

        if (count($beans) > 50) {
            $fw->expects($this->atleastonce())
                ->method('createRemoveReassignedJob');
        }

        $result = $fw->removeReassignedItems('foo', $tp_id);
        $this->assertEquals($expected, $result);

        if ($execute_count > 0) {
            $this->assertEquals($sql, $sq);
        }
    }

    public function removeReassignedItemsProvider()
    {
        $fw = array('id' => 'foo');
        $fwArray = array();

        //generate 51 items to test going past the standard chunk size
        for ($i = 0; $i <= 50; $i++) {
            array_push($fwArray, $fw);
        }

        return array(
            array(
                array('is_setup' => true),
                true,
                'tpid',
                1,
                array($fw)
            ),

            array(
                array('is_setup' => true),
                true,
                'tpid',
                1,
                $fwArray
            ),

            array(array('is_setup' => false),
                false,
                'tpid',
                0,
                array($fw)
            ),

            array(array('is_setup' => true),
                false,
                '',
                0,
                array($fw)
            ),
            array(
                array('is_setup' => true),
                false,
                'tpid',
                1,
                array()
            )
        );
    }

    /**
     * @covers ForecastWorksheet::processRemoveChunk
     */
    public function testProcessRemoveChunk()
    {
        $fw = $this->createPartialMock('ForecastWorksheet', array('mark_deleted'));
        $bean = array('id' => 'foo');

        $fw->expects($this->once())
            ->method('mark_deleted');

        $fw->processRemoveChunk(array($bean));
    }

    /**
     * @covers ForecastWorksheet::createRemoveReassignedJob
     */
    public function testCreateRemoveReassignedJob()
    {
        $data = array("foo");
        $user_id = "bar";
        $sj = $this->createMock('SchedulersJob');
        $jq = $this->createPartialMock('SugarJobQueue', array('submitJob'));
        $fw = $this->createPartialMock('ForecastWorksheet', array('getBean',
                                                        'getJobQueue'));

        $jq->expects($this->once())
            ->method('submitJob')
            ->with($sj);

        $fw->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($sj));

        $fw->expects($this->once())
            ->method('getJobQueue')
            ->will($this->returnValue($jq));

        $fw->createRemoveReassignedJob($data, $user_id);

        $this->assertEquals('Remove Reassigned Items', $sj->name);
        $this->assertEquals('class::SugarJobRemoveReassignedItems', $sj->target);
        $this->assertEquals(json_encode($data), $sj->data);
        $this->assertEquals(0, $sj->retry_count);
        $this->assertEquals($user_id, $sj->assigned_user_id);

    }

    /**
     * @covers ForecastWorksheet::worksheetTotals
     */
    public function testWorksheetTotalsReturnFalse()
    {
        $worksheet = $this->getMockWorksheet();

        $tp = $this->createPartialMock('Timeperiod', array('save'));
        $tp->id = null;

        $worksheet->expects($this->once())
            ->method('getBean')
            ->with('TimePeriods', 'test_timeperiod_id')
            ->willReturn($tp);

        $actual = $worksheet->worksheetTotals('test_timeperiod_id', 'test_user_id');

        $this->assertFalse($actual);
    }

    /**
     * @covers ForecastWorksheet::worksheetTotals
     */
    public function testWorksheetTotalsReturnsDefaultEmptyArrayWhenNoValuesAreFound()
    {
        $GLOBALS['current_user'] = $this->createPartialMock('User', array('save'));
        $GLOBALS['current_user']->id = 'current_user_id';

        $worksheet = $this->getMockWorksheet(array('getSugarQuery', 'getTableName'));

        $tp = $this->createPartialMock('Timeperiod', array('save'));
        $tp->id = 'test_timeperiod_id';
        $tp->start_date_timestamp = '10000';
        $tp->end_date_timestamp = '10000';

        $worksheet->expects($this->atLeastOnce())
            ->method('getTableName')
            ->willReturn('forecast_worksheets');

        $worksheet->expects($this->exactly(2))
            ->method('getBean')
            ->withConsecutive(
                array('TimePeriods', 'test_timeperiod_id'),
                array('ForecastWorksheets')
            )
            ->willReturnOnConsecutiveCalls(
                $tp,
                $worksheet
            );

        $sq = $this->getMockBuilder('SugarQuery')
            ->setMethods(array('execute'))
            ->getMock();

        $sq->expects($this->once())
            ->method('execute')
            ->willReturn(array());

        $worksheet->expects($this->once())
            ->method('getSugarQuery')
            ->willReturn($sq);


        $actual = $worksheet->worksheetTotals('test_timeperiod_id', 'test_user_id', 'unit_test');

        $expected = array(
            'amount' => '0',
            'best_case' => '0',
            'worst_case' => '0',
            'overall_amount' => '0',
            'overall_best' => '0',
            'overall_worst' => '0',
            'timeperiod_id' => 'test_timeperiod_id',
            'lost_count' => '0',
            'lost_amount' => '0',
            'lost_best' => '0',
            'lost_worst' => '0',
            'won_count' => '0',
            'won_amount' => '0',
            'won_best' => '0',
            'won_worst' => '0',
            'included_opp_count' => 0,
            'total_opp_count' => 0,
            'includedClosedCount' => 0,
            'includedClosedAmount' => '0',
            'includedClosedBest' => '0',
            'includedClosedWorst' => '0',
            'pipeline_amount' => '0',
            'pipeline_opp_count' => 0,
            'closed_amount' => '0',
            'includedIdsInLikelyTotal' => array()
        );

        $this->assertSame($expected, $actual);

        unset($GLOBALS['current_user']);
    }

    /**
     * @covers ForecastWorksheet::worksheetTotals
     */
    public function testWorksheetTotals()
    {
        $GLOBALS['current_user'] = $this->createPartialMock('User', array('save'));
        $GLOBALS['current_user']->id = 'current_user_id';

        Forecast::$settings = array(
            'sales_stage_won' => array('Closed Won'),
            'sales_stage_lost' => array('Closed Lost'),
            'commit_stages_included' => array('Include')
        );

        $worksheet = $this->getMockWorksheet(array('getSugarQuery', 'getTableName'));

        $tp = $this->createPartialMock('Timeperiod', array('save'));
        $tp->id = 'test_timeperiod_id';
        $tp->start_date_timestamp = '10000';
        $tp->end_date_timestamp = '10000';

        $worksheet->expects($this->atLeastOnce())
            ->method('getTableName')
            ->willReturn('forecast_worksheets');

        $worksheet->expects($this->exactly(2))
            ->method('getBean')
            ->withConsecutive(
                array('TimePeriods', 'test_timeperiod_id'),
                array('ForecastWorksheets')
            )
            ->willReturnOnConsecutiveCalls(
                $tp,
                $worksheet
            );

        $sq = $this->getMockBuilder('SugarQuery')
            ->setMethods(array('execute'))
            ->getMock();

        $sq->expects($this->once())
            ->method('execute')
            ->willReturn(
                array(
                    array(
                        'likely_case' => '50',
                        'best_case' => '50',
                        'worst_case' => '50',
                        'base_rate' => '1',
                        'sales_stage' => 'Closed Won',
                        'commit_stage' => 'Include',
                        'parent_id' => 'test_1'
                    ),
                    array(
                        'likely_case' => '50',
                        'best_case' => '50',
                        'worst_case' => '50',
                        'base_rate' => '1',
                        'sales_stage' => 'Test',
                        'commit_stage' => 'Include',
                        'parent_id' => 'test_3'
                    ),
                    array(
                        'likely_case' => '50',
                        'best_case' => '50',
                        'worst_case' => '50',
                        'base_rate' => '1',
                        'sales_stage' => 'Closed Lost',
                        'commit_stage' => 'Exclude',
                        'parent_id' => 'test_2'
                    )
                )
            );

        $worksheet->expects($this->once())
            ->method('getSugarQuery')
            ->willReturn($sq);


        $actual = $worksheet->worksheetTotals('test_timeperiod_id', 'test_user_id', 'unit_test');

        $expected = array(
            'amount' => '50.000000',
            'best_case' => '50.000000',
            'worst_case' => '50.000000',
            'overall_amount' => '150.000000',
            'overall_best' => '150.000000',
            'overall_worst' => '150.000000',
            'timeperiod_id' => 'test_timeperiod_id',
            'lost_count' => 1,
            'lost_amount' => '50.000000',
            'lost_best' => '50.000000',
            'lost_worst' => '50.000000',
            'won_count' => 1,
            'won_amount' => '50.000000',
            'won_best' => '50.000000',
            'won_worst' => '50.000000',
            'included_opp_count' => 2,
            'total_opp_count' => 3,
            'includedClosedCount' => 1,
            'includedClosedAmount' => '50.000000',
            'includedClosedBest' => '50.000000',
            'includedClosedWorst' => '50.000000',
            'pipeline_amount' => '0',
            'pipeline_opp_count' => 0,
            'closed_amount' => '0',
            'includedIdsInLikelyTotal' => Array (
                0 => 'test_3'
            )
        );

        $this->assertSame($expected, $actual);

        unset($GLOBALS['current_user']);
    }
}
