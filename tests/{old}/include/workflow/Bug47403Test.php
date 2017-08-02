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

require_once('include/workflow/action_utils.php');

class Bug47403Test extends Sugar_PHPUnit_Framework_TestCase
{

    protected $_focus;
    protected $_actionArray;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->_focus = SugarTestAccountUtilities::createAccount();

        $this->_actionArray = array (
            'action_module' => '',
            'action_type' => 'update',
            'rel_module' => '',
            'rel_module_type' => 'all',
            'basic_ext' => array (),
            'advanced' => array (),
        );
    }

    public function tearDown()
    {
        unset($this->_actionArray);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    public function testWorkflowCanSetNonRequiredFieldToEmpty() {
        $this->_focus->assigned_user_id = $GLOBALS['current_user']->id;
        $this->_actionArray['basic'] = array('assigned_user_id' => '');

        $this->assertSame($GLOBALS['current_user']->id, $this->_focus->assigned_user_id);
        process_action_update($this->_focus, $this->_actionArray);
        $this->assertSame('', $this->_focus->assigned_user_id);
    }

    public function testWorkflowCanNotSetRequiredFieldToEmpty() {
        $this->_focus->user_name = $GLOBALS['current_user']->user_name;
        $this->_actionArray['basic'] = array('name' => '');

        $this->assertSame($GLOBALS['current_user']->user_name, $this->_focus->user_name);
        process_action_update($this->_focus, $this->_actionArray);
        $this->assertNotSame('', $this->_focus->user_name);
    }
}

?>
