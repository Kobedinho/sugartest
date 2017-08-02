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

class Pat756Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $task;

    function setUp()
    {
        $current_user = SugarTestUserUtilities::createAnonymousUser();

        $this->task = SugarTestTaskUtilities::createTask();
        $this->task->assigned_user_id = $current_user->id;
    }

    function tearDown()
    {

        SugarTestTaskUtilities::removeAllCreatedTasks();
        $GLOBALS['db']->query("DELETE FROM notes WHERE name = 'note756'", true);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        SugarTestHelper::tearDown();
    }

    function testProcessActionNewAssnUserId()
    {
        $action_array = array(
            'action_type' => 'new',
            'action_module' => 'notes',
            'rel_module' => '',
            'rel_module_type' => 'all',
            'basic' => array(
                'name' => 'note756',
            ),
            'basic_ext' => array(),
            'advanced' => array(),
        );

        process_action_new($this->task, $action_array);

        $assigned_user_id = $GLOBALS['db']->getOne("SELECT assigned_user_id FROM notes WHERE name = 'note756'", true);

        $this->assertEquals($this->task->assigned_user_id, $assigned_user_id, 'assigned_user_id does not match');
    }
}
