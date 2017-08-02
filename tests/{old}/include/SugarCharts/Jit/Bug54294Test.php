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
 * Bug #54294
 * Reports Do Not Format Currency Fields on Charts
 *
 * @author mgusev@sugarcrm.com
 * @ticked 54294
 */
class Bug54294Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Currency
     */
    protected $currency = null;

    /**
     * @var TimeDate
     */
    protected $timeDate = null;

    /**
     * @var Account
     */
    protected $account = null;

    /**
     * @var Opportunity
     */
    protected $opportunity = null;

    /**
     * @var SavedReport
     */
    protected $savedReport = null;

    public function setUp()
    {
        $this->markTestIncomplete('This test is not written correctly, need to merge changes from 6_5_3');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('mod_strings', array('Opportunities'));
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');

        $this->currency = new Currency();
        $this->timeDate = new TimeDate($GLOBALS['current_user']);

        $this->account = SugarTestAccountUtilities::createAccount();

        $this->opportunity = new Opportunity();
        $this->opportunity->name = __CLASS__;
        $this->opportunity->currency_id = -99;
        $this->opportunity->amount = 1000000;
        $this->opportunity->sales_stage = 'Prospecting';
        $this->opportunity->account_id = $this->account->id;
        $this->opportunity->account_name = $this->account->name;
        $this->opportunity->date_closed = $this->timeDate->asUser(new DateTime('+7 days'));
        $this->opportunity->assigned_user_id = $GLOBALS['current_user']->id;
        $this->opportunity->assigned_user_name = $GLOBALS['current_user']->name;
        $this->opportunity->save();

        $reportDef = '
            {
                "display_columns":[],
                "module":"Opportunities",
                "group_defs":
                [
                    {
                        "name":"sales_stage",
                        "label":"Sales Stage",
                        "table_key":"self",
                        "type":"enum"
                    }
                ],
                "summary_columns":
                [
                    {
                        "name":"sales_stage",
                        "label":"Sales Stage",
                        "table_key":"self"
                    },
                    {
                        "name":"amount_usdollar",
                        "label":"SUM: Amount",
                        "field_type":"currency",
                        "group_function":"sum",
                        "table_key":"self"
                    }
                ],
                "report_name":"' . __CLASS__ . '",
                "chart_type":"vBarF",
                "do_round":0,"chart_description":"",
                "numerical_chart_column":"self:amount_usdollar:sum",
                "numerical_chart_column_type":"currency",
                "assigned_user_id":"' . $GLOBALS['current_user']->id . '",
                "report_type":"summary",
                "full_table_list":
                {
                    "self":
                    {
                        "value":"Opportunities",
                        "module":"Opportunities",
                        "label":"Opportunities"
                    },
                    "Opportunities:accounts":
                    {
                        "name":"Opportunities  >  Accounts",
                        "parent":"self",
                        "link_def":
                        {
                            "name":"accounts",
                            "relationship_name":"accounts_opportunities",
                            "bean_is_lhs":false,
                            "link_type":"many",
                            "label":"Accounts",
                            "module":"Accounts",
                            "table_key":"Opportunities:accounts"
                        },
                        "dependents":
                        [
                            "Filter.1_table_filter_row_1"
                        ],
                        "module":"Accounts",
                        "label":"Accounts"
                    }
                },
                "filters_def":
                {
                    "Filter_1":
                    {
                        "operator":"AND",
                        "0":
                        {
                            "name":"id",
                            "table_key":"Opportunities:accounts",
                            "qualifier_name":"is",
                            "input_name0":"' . $this->account->id . '",
                            "input_name1":"' . addslashes($this->account->name) .'"
                        }
                    }
                }
            }
        ';

        $this->savedReport = new SavedReport();
        $this->savedReport->assigned_user_id = $GLOBALS['current_user']->id;
        $this->savedReport->assigned_user_name = $GLOBALS['current_user']->name;
        $this->savedReport->chart_type = 'vBarF';
        $this->savedReport->team_id = '1';
        $this->savedReport->save_report(-1, $GLOBALS['current_user']->id, __CLASS__, 'Opportunities', 'summary', $reportDef, 0, 1, 'vBarF');
    }

    /*
    public function tearDown()
    {
        $this->opportunity->mark_deleted($this->opportunity->id);
        $this->savedReport->mark_deleted($this->savedReport->id);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }
    */

    /**
     * @outputBuffering disabled
     * @group 54294
     * @return void
     */
    public function testCurrencySymbolInChart()
    {
        $report = new Report($this->savedReport->content);
        $report->is_saved_report = true;
        $report->saved_report = &$this->savedReport;
        $report->saved_report_id = $this->savedReport->id;

        $report->run_summary_query();
        while ($report->get_summary_next_row() != false)
        {
            // grabbing records
        }

        if ($report->has_summary_columns()) {
            $report->run_total_query();
            $report->get_summary_header_row();
            $report->get_summary_total_row();
        }


        $cd = new ChartDisplayMock();
        $cd->setReporter($report);
        $cd->legacyDisplay(null, false);
        $jsonFile = str_replace(".xml",".js", $cd->get_cache_file_name($report));
        $jsonObject = sugar_file_get_contents($jsonFile);
        $json = getJSONobj();
        $jsonObject = $json->decode($jsonObject);

        $this->assertEquals($this->opportunity->amount, $jsonObject['values'][0]['values'][0], 'Value in chart should be equal to opportunity amount');
        $this->assertStringStartsWith(
            currency_format_number($this->opportunity->amount, array('currency_symbol' => $cd->print_currency_symbol($report->report_def))),
            $jsonObject['values'][0]['valuelabels'][0],
            'Label in chart should be localized'
        );
    }
}

class ChartDisplayMock extends ChartDisplay
{
    public function print_currency_symbol()
    {
        return parent::print_currency_symbol();
    }
}
