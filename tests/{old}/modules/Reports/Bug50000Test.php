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


class Bug50000Test extends Sugar_PHPUnit_Framework_TestCase {

    var $reporter;

    public function setUp() {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['mod_strings'] = return_module_language('en_us', 'Reports');
        require_once('modules/Reports/templates/templates_reports.php');
        $this->reporter = new Bug50000MockReporter();
    }

    public function tearDown() {
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['mod_strings']);
        unset($this->reporter);
    }

    /**
     * @dataProvider bug50000DataProvider
     */
    public function testColumnLabelsAreCorrectForMatrixReport($report_def, $header_row, $expected) {
        $this->reporter->report_def = $report_def;

        $this->assertSame($expected, getHeaderColumnNamesForMatrix($this->reporter, $header_row, ''));
    }

    /**
     * Data provider for testColumnLabelsAreCorrectForMatrixReport()
     * @return array report_def, header_row, expected
     */
    public function bug50000DataProvider() {
        $strings = return_module_language('en_us', 'Reports');
        return array(
            array(
                array('group_defs' => array(
                    array('label'=> 'User Name', 'name' => 'user_name', 'table_key' => 'Opportunities:assigned_user_link', 'type'=>'user_name'),
                    array('label'=> 'Name', 'name' => 'name', 'table_key' => 'Opportunities:accounts', 'type'=>'name'),
                )),
                array('User Name', 'Account Name', 'Count'),
                array('User Name', 'Account Name', $strings['LBL_REPORT_GRAND_TOTAL']),
            ),
        );
    }
}


class Bug50000MockReporter {
    var $report_def;
    var $group_defs_Info;
}

?>
