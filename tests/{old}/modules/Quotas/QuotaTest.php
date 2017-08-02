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

class QuotaTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestCurrencyUtilities::createCurrency('MonkeyDollars','$','MOD',2.0);
    }

    public function tearDown()
    {
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestHelper::tearDown();
    }

    /*
     * Test that the base_rate field is populated with rate
     * of currency_id
     *
     */
    public function testQuotaRate()
    {
        $quota = SugarTestQuotaUtilities::createQuota(500);
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        // if Euro does not exist, will use default currency
        $quota->currency_id = $currency->id;
        $quota->save();
        $this->assertEquals(
            sprintf('%.6f',$quota->base_rate),
            sprintf('%.6f',$currency->conversion_rate)
        );
    }

    public function testGetRollupQuotaReturnsArrayForEmptyQuota()
    {
        $quota = SugarTestQuotaUtilities::createQuota();
        $quota->db = $this->getMockForAbstractClass('DBManager', array('fetchByAssoc'));
        $quota->db->expects($this->any())->method('limitQuery')->will($this->returnValue('foo'));
        $quota->db->expects($this->any())->method('fetchByAssoc')->will($this->returnValue(false));
        $this->assertEquals(
            array(
                'currency_id' => -99,
                'amount' => 0,
                'formatted_amount' => '$0.00',
            ),
            $quota->getRollupQuota(1)
        );
    }

    public function testGetRollupQuota()
    {
        $this->markTestIncomplete('[BR-3362] Query Spy doesn\'t capture parameters in prepared statements');

        SugarTestHelper::setUp('mock_db');

        $test_tp_id = create_guid();

        /* @var $quota Quota */
        $quota = BeanFactory::newBean('Quotas');

        $db = DBManagerFactory::getInstance();
        $db->addQuerySpy(
            'quota_select',
            '/quotas.timeperiod_id = \'' . $test_tp_id . '\'/',
            array(
                array(
                    'currency_id' => -99,
                    'amount' => 10,
                )
            )
        );

        $this->assertEquals(
            array(
                'currency_id' => -99,
                'amount' => 10,
                'formatted_amount' => '$10.00',
            ),
            $quota->getRollupQuota($test_tp_id, 'test_user_id')
        );
    }

    /**
     * @covers Quota::get_summary_text
     */
    public function testGetSummaryText()
    {
        $tpname = "Test TimePeriod";
        $userFullName = "Test User Full Name";
        $expectedSummary = "$tpname - $userFullName";

        $mocktp = $this->createMock('TimePeriod');

        BeanFactory::setBeanClass('TimePeriods', get_class($mocktp));

        $tp = BeanFactory::newBean('TimePeriods');
        $tp->id = create_guid();
        $tp->name = $tpname;

        BeanFactory::registerBean($tp);

        $quota = BeanFactory::newBean('Quotas');
        $quota->timeperiod_id = $tp->id;
        $quota->user_full_name = $userFullName;

        $this->assertSame($expectedSummary, $quota->get_summary_text());

        BeanFactory::unregisterBean($tp);
        BeanFactory::setBeanClass('TimePeriods');
    }
}

