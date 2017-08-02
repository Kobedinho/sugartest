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

class RevenueLineItemHooksTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var RevenueLineItem
     */
    protected $rli;

    public static function setUpBeforeClass()
    {
        SugarTestForecastUtilities::setUpForecastConfig(array(
            'sales_stage_won' => array('Closed Won'),
            'sales_stage_lost' => array('Closed Lost')
        ));
    }

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');

        $this->rli = $this->createPartialMock('RevenueLineItem', array('save'));
    }

    public function tearDown()
    {
        $this->rli = null;
        SugarTestHelper::tearDown();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
    }

    /**
     * @covers RevenueLineItemHooks::afterRelationshipDelete
     * @dataProvider dataAfterRelationshipDelete
     */
    public function testAfterRelationshipDelete($event, $link, $result, $deleted, $count)
    {
        $this->rli->deleted = $deleted;
        $hook = new RevenueLineItemHooks();

        $this->rli->expects($this->exactly($count))
                  ->method('save');

        $ret = $hook->afterRelationshipDelete($this->rli, $event, $link);

        $this->assertEquals($result, $ret);
    }

    public function dataAfterRelationshipDelete()
    {
        return array(
            array('after_relationship_delete', array('link' => 'account_link'), true, 0, 1),
            array('after_relationship_delete', array('link' => 'foo'), false, 0, 0),
            array('after_relationship_delete', array('link' => 'account_link'), false, 1, 0),
            array('after_relationship_delete', array('link' => 'foo'), false, 1, 0),
            array('foo', array('link' => 'account_link'), false, 0, 0),
            array('foo', array('link' => 'foo'), false, 0, 0 ),
        );
    }

    /**
     * @dataProvider beforeSaveIncludedCheckProvider
     */
    public function testBeforeSaveIncludedCheck($sales_stage, $commit_stage, $probability, $expected)
    {
        $hookMock = new RevenueLineItemHooks();
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();

        $rli->probability = $probability;
        $rli->sales_stage = $sales_stage;
        $rli->commit_stage = $commit_stage;

        $hookMock->beforeSaveIncludedCheck($rli, 'before_save', null);
        $this->assertEquals($rli->commit_stage, $expected);
    }

    public function beforeSaveIncludedCheckProvider(){
        return array(
            array('Closed Won', 'exclude', 100, 'include'),
            array('Closed Lost', 'include', 0, 'exclude')
        );
    }
}
