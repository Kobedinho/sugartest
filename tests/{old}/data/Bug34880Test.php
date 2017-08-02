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
 * Bug #34880 : Non-reportable fields unavailable to workflow
 *
 * @author myarotsky@sugarcrm.com
 * @ticket 34880
 */
class Bug34880Test extends Sugar_PHPUnit_Framework_TestCase
{
    public static function provider()
    {
        return array(
            array('standard_display'),
            array('normal_trigger'),
            array('normal_date_trigger'),
            array('action_filter'),
            array('template_filter'),
            array('alert_trigger')
        );
    }
    /**
     * Reportable fields must be available in workflow
     * @dataProvider provider
     * @group 34880
     */
    public function testReportableFieldsMustBeAvailableInWorkflow($action)
    {
        $def = array(
            'reportable' => ''
        );
        $obj = new VarDefHandler('', $action);
        $this->assertTrue($obj->compare_type($def), "reportable fields should be available in workflow");
    }
}
