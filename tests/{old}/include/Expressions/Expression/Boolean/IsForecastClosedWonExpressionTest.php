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

class IsForecastClosedWonExpressionTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        Forecast::$settings = array();
        parent::tearDown();
    }

    public static function dataProviderCheckStatus()
    {
        return array(
            array('test stage 1', 'false'),
            array('Closed Won', 'true'),
            array('Closed Lost', 'false'),
        );
    }

    /**
     * @dataProvider dataProviderCheckStatus
     *
     * @param $status
     * @param $expected
     * @throws PHPUnit_Framework_Exception
     * @throws Exception
     */
    public function testIsForecastClosedWonEvaluate($status, $expected)
    {
        Forecast::$settings = array(
            'is_setup' => 1,
            'sales_stage_won' => array('Closed Won'),
            'sales_stage_lost' => array('Closed Lost'),
        );

        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('save'))
            ->getMock();

        $rli->sales_stage = $status;

        $expr = 'isForecastClosedWon($sales_stage)';
        $result = Parser::evaluate($expr, $rli)->evaluate();

        $this->assertSame($expected, strtolower($result));
    }
}
