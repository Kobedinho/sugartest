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

require_once 'tests/{old}/upgrade/UpgradeTestCase.php';

class SugarUpgradeMigrateKBDocumentsReportsTest extends UpgradeTestCase
{
    /**
     * @var JSON $jsonObject
     */
    protected $jsonObject;

    /**
     * @var Report $report
     */
    protected $report;

    /**
     * @var array $reportDef KBDocument report defs.
     */
    protected $reportDef = [
        'display_columns' => [
            [
                'name' => 'kbdocument_name',
                'label' => 'Document Name',
                'table_key' => 'self',
            ],
            [
                'name' => 'active_date',
                'label' => 'Publish Date',
                'table_key' => 'self',
            ],
            [
                'name' => 'exp_date',
                'label' => 'Expiration Date',
                'table_key' => 'self',
            ],
            [
                'name' => 'description',
                'label' => 'Description',
                'table_key' => 'self',
            ],
            [
                'name' => 'status_id',
                'label' => 'Custom Label',
                'table_key' => 'self',
            ],
        ],
        'module' => 'KBDocuments',
        'group_defs' => [],
        'summary_columns' => [],
        'report_name' => 'REPORT_KB',
        'do_round' => 1,
        'numerical_chart_column' => '',
        'numerical_chart_column_type' => '',
        'assigned_user_id' => '1',
        'report_type' => 'tabular',
        'full_table_list' => [
            'self' => [
                'value' => 'KBDocuments',
                'module' => 'KBDocuments',
                'label' => 'KBDocuments',
            ],
        ],
        'filters_def' => [
            'Filter_1' => [
                'operator' => 'AND',
            ],
        ],
        'chart_type' => 'none',
    ];

    protected function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('current_user', [true, true]);
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        $this->jsonObject = getJSONobj();

        $this->report = new Report($this->jsonObject->encode($this->reportDef));
        $this->report->save('REPORT_KB');
    }

    protected function tearDown()
    {
        $this->report->saved_report->mark_deleted($this->report->saved_report->id);

        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Test that legacy KBDocument reports are converted to be compatible with KBContents module.
     */
    public function testConvertKBDocumentReportsToKBContent()
    {
        $expectedDefs = $this->reportDef;
        $expectedDefs['module'] = 'KBContents';
        $expectedDefs['full_table_list']['self'] = [
            'value' => 'KBContents',
            'module' => 'KBContents',
            'label' => 'KBContents',
        ];
        $expectedDefs['display_columns'][0]['name'] = 'name';
        $expectedDefs['display_columns'][4]['name'] = 'status';

        $script = $this->upgrader->getScript('post', '6_MigrateKBDocumentsReports');
        $script->from_version = 7.7;
        $script->to_version = 7.8;
        $script->run();

        $actualReport = BeanFactory::getBean('Reports', $this->report->saved_report->id, ['encode' => false]);
        $actualDefs = $this->jsonObject->decode($actualReport->content);

        $this->assertEquals('KBContents', $actualReport->module);
        $this->assertEquals($expectedDefs, $actualDefs);
    }
}
