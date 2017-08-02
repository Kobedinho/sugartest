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
class SugarForecasting_ReportingUsersTest extends Sugar_PHPUnit_Framework_TestCase
{

    protected static $users = array();

    /**
     * @var SugarForecasting_ReportingUsers
     */
    protected static $cls;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestForecastUtilities::setUpForecastConfig();

        self::$users['mgr'] = SugarTestUserUtilities::createAnonymousUser();

        $GLOBALS['current_user'] = self::$users['mgr'];

        self::$users['mgr2'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['mgr2']->reports_to_id = self::$users['mgr']->id;
        self::$users['mgr2']->save();

        self::$users['rep1'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['rep1']->reports_to_id = self::$users['mgr2']->id;
        self::$users['rep1']->save();

        self::$users['rep2'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['rep2']->reports_to_id = self::$users['mgr2']->id;
        self::$users['rep2']->save();

        self::$users['mgr3'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['mgr3']->reports_to_id = self::$users['mgr']->id;
        self::$users['mgr3']->save();

        self::$users['rep3'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['rep3']->reports_to_id = self::$users['mgr3']->id;
        self::$users['rep3']->save();

        self::$users['rep4'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['rep4']->reports_to_id = self::$users['mgr3']->id;
        self::$users['rep4']->save();

        self::$cls = new SugarForecasting_ReportingUsers(array('user_id' => self::$users['mgr']->id));
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        $GLOBALS['current_user'] = null;
        parent::tearDownAfterClass();
    }
    
    public function testReturnTreeShouldContain3Children()
    {
        $tree = self::$cls->process();

        $this->assertEquals(3, count($tree['children']));

        return $tree;
    }

    /**
     * @group forecasts
     * @depends testReturnTreeShouldContain3Children
     * @param $tree
     */
    public function testFirstChildIsManagerName($tree)
    {
        $this->assertEquals(self::$users['mgr']->full_name, $tree['children'][0]['data']);
    }

    /**
     * @group forecasts
     * @return array|string
     */
    public function testFetchReporteeContainsTwoNodes()
    {
        self::$cls->setArg('user_id', self::$users['mgr2']->id);

        $tree = self::$cls->process();

        $this->assertEquals(2, count($tree));

        return $tree;
    }

    /**
     * @group forecasts
     * @depends testFetchReporteeContainsTwoNodes
     * @param $tree
     */
    public function testReporteeFirstObjectIsParentLink($tree)
    {
        $this->assertEquals(self::$users['mgr']->full_name, $tree[0]['data']);
    }

    /**
     * @group forecasts
     * @depends testFetchReporteeContainsTwoNodes
     * @param $tree
     */
    public function testReporteeTreeContainsThreeChildren($tree)
    {
        $this->assertEquals(3, count($tree[1]['children']));
    }

    /**
     * @group forecasts
     * @depends testFetchReporteeContainsTwoNodes
     * @param $tree
     */
    public function testReporteeFirstChildIsManagerName($tree)
    {
        $this->assertEquals(self::$users['mgr2']->full_name, $tree[1]['children'][0]['data']);
    }

    public function testSubManagerParentCascadeStops()
    {
        $GLOBALS['current_user'] = self::$users['mgr2'];
        self::$cls->setArg('user_id', self::$users['rep1']->id);
        $tree = self::$cls->process();

        $this->assertEquals(self::$users['mgr2']->full_name, $tree['data']);
    }
}
