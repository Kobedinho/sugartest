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

require_once('modules/UpgradeWizard/uw_utils.php');

/**
 * Bug #57162
 * Upgrader needs to handle 3-dots releases and double digit values
 *
 * @author mgusev@sugarcrm.com
 * @ticked 57162
 */
class Bug57162Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function dataProvider()
    {
        return array(
            array('656', array('6.5.6')),
            array('660', array('6.6.0beta1')),
            array('640', array('6.4.0rc2')),
            array('600', array('6', 3)),
            array('6601', array('6.6.0.1')),
            array('6601', array('6.6.0.1', 0)),
            array('660', array('6.6.0.1', 3)),
            array('660', array('6.6.0.1', 3, '')),
            array('66x', array('6.6.0.1', 3, 'x')),
            array('660x', array('6.6.0.1', 0, 'x')),
            array('6.6.x', array('6.6.0.1', 3, 'x', '.')),
            array('6-6-0-beta2', array('6.6.0.1', 0, 'beta2', '-')),
            array('6601', array('6.6.0.1', 0, '', '')),
            array('', array('test342lk')),
            array('650', array('6.5.6' ,0, '0')),
            array('60', array('6.5.6', 2, 0)),
        );
    }

    /**
     * Test asserts result of implodeVersion function
     *
     * @group 57162
     * @dataProvider dataProvider
     * @param string $expect version
     * @param array $params for implodeVersion function
     */
    public function testImplodeVersion($expected, $params)
    {
        $actual = call_user_func_array('implodeVersion', $params);
        $this->assertEquals($expected, $actual, 'Result is incorrect');
    }
}
