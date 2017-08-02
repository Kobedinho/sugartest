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
 * @ticket 45335
 */
class Bug45335Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        global $beanList, $beanFiles;
        require('include/modules.php');
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
    }

    /**
     * Ensure that before validation invalid fields list is empty
     */
    public function testInvalidFieldsAreEmpty()
    {
        $report = new Report();
        $this->assertEmpty($report->get_invalid_fields());
    }

    /**
     * Ensure that valid report definition considered valid.
     */
    public function testValidationPassed()
    {
        $json = getJSONobj();
        $report_def = array(
            'display_columns' => array(
                array(
                    'name' => 'id',
                    'table_key' => 'self',
                ),
            ),
            'filters_def' => array(),
        );

        $report = new Report($json->encode($report_def));
        $this->assertTrue($report->is_definition_valid());
    }

    /**
     * Ensure that invalid report definition considered invalid.
     */
    public function testValidationFailed()
    {
        $field_name = 'some_non_existing_field';

        $json = getJSONobj();
        $report_def = array(
            'display_columns' => array(
                array(
                    'name' => $field_name,
                    'table_key' => 'self',
                ),

                // specify one field twice to insure that there will be no
                // duplicates in invalid fields array
                array(
                    'name' => $field_name,
                    'table_key' => 'self',
                ),
            ),
            'filters_def' => array(),
        );

        $report = new Report($json->encode($report_def));
        $this->assertFalse($report->is_definition_valid());

        $this->assertEquals(array($field_name), $report->get_invalid_fields());
    }
}
