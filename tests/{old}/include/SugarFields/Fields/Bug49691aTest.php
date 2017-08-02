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
 * @group Bug49691
 */
class Bug49691aTest extends Sugar_PHPUnit_Framework_TestCase {

    var $bean;
    var $sugarField;

    var $oldDate;
    var $oldTime;

    public function setUp() {
        global $sugar_config;
        $this->bean = new Bug49691aMockBean();
        $this->sugarField = new SugarFieldDatetimecombo("Accounts");
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->oldDate = $sugar_config['default_date_format'];
        $sugar_config['default_date_format'] = 'm/d/Y';
        $this->oldTime = $sugar_config['default_time_format'];
        $sugar_config['default_time_format'] = 'H:i';
    }

    public function tearDown() {
        global $sugar_config;
        unset($GLOBALS['current_user']);
        $sugar_config['default_date_format'] = $this->oldDate;
        $sugar_config['default_time_format'] = $this->oldTime;
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->sugarField);
    }

    /**
     * @dataProvider providerFunction
     */
    public function testDBDateConversion($dateValue, $expected) {
        global $current_user;

        $this->bean->test_c = $dateValue;

        $inputData = array('test_c'=>$dateValue);
        $field = 'test_c';
        $def = '';
        $prefix = '';

        $this->sugarField->save($this->bean, $inputData, $field, $def, $prefix);
        $this->assertNotEmpty($this->bean->test_c);
        $this->assertSame($expected, $this->bean->test_c);
    }

    public function providerFunction() {
        return array(
            array('01/01/2012 12:00', '2012-01-01 12:00:00'),
            array('2012-01-01 12:00:00', '2012-01-01 12:00:00'),
            array('01/01/2012', '2012-01-01'),
            array('2012-01-01', '2012-01-01'),
        );
    }
}

class Bug49691aMockBean {
    var $test_c;
}
