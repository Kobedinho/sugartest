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
 * Bug #48135
 * Testing that reassigning EAPM modules from one user to another will produce working query
 * @ticket 48135
 */
class Bug48135Test extends Sugar_PHPUnit_Framework_TestCase
{
    public $user1;
    public $user2;
    public $eapm;

    public function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        if(!isset($GLOBALS['current_language']))
        {
                $GLOBALS['current_language'] = 'en_us';
        }
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Users');

        //create 2 users and make one of them an admin and current user
        $this->user1 = SugarTestUserUtilities::createAnonymousUser();
        $this->user2 = SugarTestUserUtilities::createAnonymousUser();
        $this->user2->is_admin = 1;
        $this->user2->save();
        $GLOBALS['current_user'] = $this->user2;


        //create an eapm record that is assigned to user 1
        require_once('modules/EAPM/EAPM.php');
        $this->eapm = new EAPM();
        $this->eapm->name = 'testUnit48135EAPM';
        $this->eapm->description = 'simulate an inbound email box to Gmail for unit test';
        $this->eapm->deleted = 0;
        $this->eapm->assigned_user_id = $this->user1->id;
        $this->eapm->password = md5('KL8998ccD');
        $this->eapm->save();
    }


    public function tearDown()
    {
        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['mod_strings']);

        $GLOBALS['db']->query("DELETE FROM eapm WHERE name = 'testUnit48135EAPM'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }


    /**
     * @group user_reassignment
     */
    public function testReassignedEAPM()
    {
        global $current_user,$app_list_strings,$app_strings,$beanFiles,$mod_strings, $beanFiles;
        //first of all lets make sure the assigned id's match
        $this->eapm->retrieve($this->eapm->id);
        $this->assertSame($this->user1->id, $this->eapm->assigned_user_id);

        //reassign users is a 3 step wizard, with each page building up queries and other values based on the previous page.
        //We just want to make sure that the eapm reassignment query will not fail, so lets simulate
        //the request and post arrays built from step 1 in order to run step 2 and have the query created
        $this->simulateStep2Session();

        //lets call reassignUserRecords to create the query.  The file is full of echo's, so let's catch the buffer.
        ob_start();
        include('modules/Users/reassignUserRecords.php');
        ob_end_clean();

        //asssert that expected session variable structure exists
        $this->assertArrayHasKey('modules', $_SESSION['reassignRecords'],'Session[reassignRecords] does not contain a modules element, reassignuserrecords.php did not process as expected. ');
        $this->assertArrayHasKey('EAPM', $_SESSION['reassignRecords']['modules'], 'Session[reassignRecords][modules] does not contain an EAPM element, reassignuserrecords.php did not process as expected. ');
        $this->assertArrayHasKey('update', $_SESSION['reassignRecords']['modules']['EAPM'], 'Session[reassignRecords][modules][EAPM] does not contain an update element, reassignuserrecords.php did not process as expected. ');

        //run the query
        $GLOBALS['db']->query($_SESSION['reassignRecords']['modules']['EAPM']['update']);

        //assert that file got reassigned
        $this->eapm->retrieve($this->eapm->id);
        $this->assertSame($this->user2->id, $this->eapm->assigned_user_id, 'reassignUserrecords.php is not creating a proper query to reassign eapm records.  The query is: '.$_SESSION['reassignRecords']['modules']['EAPM']['update']);
    }


    public function simulateStep2Session(){

        //simulate having only selected eapm for reassignment
        $_SESSION['reassignRecords']['assignedModuleListCache'] = array('EAPM' => 'EAPM');
        $_SESSION['reassignRecords']['assignedModuleListCacheDisp'] = array ('EAPM' => 'EAPM');

        $_REQUEST['module'] = 'Users';
        $_REQUEST['action'] = 'reassignUserRecords';
        $_REQUEST['fromuser'] = $this->user1->id;
        $_REQUEST['touser'] = $this->user2->id;
        $_REQUEST['modules'] = array('EAPM');
        $_REQUEST['steponesubmit'] = 'Next';
    }
}
