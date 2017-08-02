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
 

class Bug50171Test extends Sugar_PHPUnit_Framework_TestCase {

public function testGetJSMock() {
    global $mod_strings;
    $mod_strings = return_module_language($GLOBALS['current_language'], 'Import');
    $mock = new Bug50171ImportViewStep3Mock();
    $required = array("It's A Bug!");
    $output = $mock->_getJSMock($required);
    $this->assertRegExp('/required\[\'0\'\] = \'It\&\#039\;s A Bug\!/', $output, 'Required string not encoded');
}

}


class Bug50171ImportViewStep3Mock extends ImportViewStep3 {

public function _getJSMock($required) {
    return $this->_getJS($required);
}

}