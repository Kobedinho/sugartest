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

require_once 'include/export_utils.php';

/**
 * Test export_utils.php
 */
class ExportUtilsTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Ensure that get_field_order_mapping returns an array with lowercase keys
     * even if passed column names that are capitalized.
     */
    public function testGetFieldOrderMappingHasLowercaseKeys()
    {
        $fields = array(
            'Uppercase Field' => 'Uppercase Field',
            'BLOCK_CAPS_FIELD' => 'Block Capital Field',
            'all lowercase field' => 'Lowercase Field',
        );
        $result = get_field_order_mapping('contacts', $fields);
        $expectedResult = array_change_key_case($fields, CASE_LOWER);
        $this->assertEquals($expectedResult, $result, 'get_field_order_mapping did not convert keys to lowercase!');
    }
}
