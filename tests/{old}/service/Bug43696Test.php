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

require_once('vendor/nusoap//nusoap.php');
require_once('tests/{old}/service/SOAPTestCase.php');

/**
 * @group bug43696
 */
class Bug43696Test extends SOAPTestCase
{
    private $_tsk = null;

	public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        parent::setUp();
        $this->_tsk = new Task();
        $this->_tsk->name = "Unit Test";
        $this->_tsk->assigned_user_id = $GLOBALS['current_user']->id;
        $this->_tsk->save();
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM tasks WHERE id = '{$this->_tsk->id}'");
        parent::tearDown();
    }

    /**
     * We want to make sure that a user can sync their own tasks.
     * only sync their own tasks
     * @return void
     */
    public function testSyncMyTasks()
    {
        $timedate = TimeDate::getInstance();
        $this->_login();

        $result = $this->_soapClient->call('sync_get_modified_relationships',
            array(
                'session' => $this->_sessionId,
                'module' => 'Users',
                'related_module' => 'Tasks',
                'from_date' => $timedate->getNow()->modify("- 2 minutes")->asDb(),
                'to_date' => $timedate->getNow()->asDb(),
                'offset' => 0,
                'max_results' => 100,
                'deleted' => 0,
                'module_id' => $GLOBALS['current_user']->id,
                'select_fields' => array('id', 'date_modified', 'deleted', 'name'),
                'id' => array(),
                'relationship_name' => 'tasks_assigned_user',
                'deletion_date' => $timedate->getNow()->modify("- 2 minutes")->asDb(),
                'php_serialize' => 0
            )
        );
        $this->assertContains($this->_tsk->id, base64_decode($result['entry_list']), 'The Result does not contain the Task Id');

    }

    /**
     * We want to make sure that even though the user is an admin they should not sync all tasks and should
     * only sync their own tasks.
     * @return void
     */
    public function testDontSyncOtherTasks()
    {
        $timedate = TimeDate::getInstance();

        //change the user to an admin
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['current_user']->save();

        //change the assigned user to another user
        $this->_tsk->assigned_user_id = 1;
        $this->_tsk->save();

        $this->_login();
        $result = $this->_soapClient->call('sync_get_modified_relationships',
            array(
                'session' => $this->_sessionId,
                'module' => 'Users',
                'related_module' => 'Tasks',
                'from_date' => $timedate->getNow()->modify("- 2 minutes")->asDb(),
                'to_date' => $timedate->getNow()->asDb(),
                'offset' => 0,
                'max_results' => 100,
                'deleted' => 0,
                'module_id' => $GLOBALS['current_user']->id,
                'select_fields' => array('id', 'date_modified', 'deleted', 'name'),
                'id' => array(),
                'relationship_name' => 'tasks_assigned_user',
                'deletion_date' => $timedate->getNow()->modify("- 2 minutes")->asDb(),
                'php_serialize' => 0
            )
        );
        $this->assertNotContains($this->_tsk->id, base64_decode($result['entry_list']), 'The Result should not contain the Task Id');

    }
}
