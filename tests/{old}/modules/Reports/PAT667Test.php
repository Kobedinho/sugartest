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
 * Filtering Report on Multiselect field with "Is One Of" returns "false positives"
 * @ticket PAT-667
 * @author bsitnikovski@sugarcrm.com
 */
class BugPAT667Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Report
     */
    private $report;

    /**
     * @var array
     */
    private static $custom_field_def = array(
        'name'        => 'test_bugpat667',
        'type'        => 'multienum',
        'module'      => 'ModuleBuilder',
        'view_module' => 'Accounts',
        'options'     => 'aaa_list',
        'default'     => '^Consultants^,^International Consultants^',
    );

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('custom_field', array('Accounts', static::$custom_field_def));

        $this->report = new Report();
        $this->report->layout_manager->setAttribute("context", "Filter");
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Test correct filter for Multienum field.
     */
    public function testReportsFilterMultienum()
    {
        $res = '';
        $data = array(
            "operator" => "AND",
            0 => array(
                "name" => self::$custom_field_def['name'] . '_c',
                "table_key" => "self",
                "qualifier_name" => "one_of",
                "input_name0" => array("Consultants")
            )
        );

        $expected = "LIKE '%^Consultants^%'";
        $this->report->filtersIterate($data, $res);
        $this->assertContains($expected, $res);
    }
}
