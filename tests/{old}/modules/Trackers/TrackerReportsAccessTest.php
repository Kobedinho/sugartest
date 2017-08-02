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
 
class TrackerReportsAccessTest extends Sugar_PHPUnit_Framework_TestCase {

	var $non_admin_user;
    var $current_user;

    function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        global $sugar_version, $sugar_flavor, $beanFiles, $beanList, $moduleList, $modListHeader, $sugar_config;
        require('config.php');
        require('include/modules.php');
        $modListHeader = $moduleList;
        require_once('modules/Reports/config.php');
        $GLOBALS['report_modules'] = getAllowedReportModules($modListHeader);
        $this->current_user = $GLOBALS['current_user'];
        $this->non_admin_user = SugarTestUserUtilities::createAnonymousUser();
    }

    function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->non_admin_user = null;
        $GLOBALS['current_user'] = $this->current_user;
        unset($GLOBALS['mod_strings']);
        SugarTestHelper::tearDown();
    }

    /**
     * Test whereby an Admin user attempts to access the TrackerSessions reports
     * @outputBuffering enabled
     */
    public function test_Admin_Tracker_Session_Report_access ()
    {
        $this->markTestIncomplete('Failing. Need to be fixed by FRM team');
    	$admin = new User();
    	$admin->retrieve('1');
    	$GLOBALS['current_user'] = $admin;
    	global $theme, $mod_strings;
    	$theme = 'Sugar';
    	$mod_strings = return_module_language($GLOBALS['current_language'], 'Reports');
    	$GLOBALS['_REQUEST']['action'] = 'ReportCriteriaResults';
    	$GLOBALS['_REQUEST']['module'] = 'Reports';
    	$GLOBALS['request_string'] = '';

    	$saved_report_seed = new SavedReport();
	    $saved_report_seed->disable_row_level_security = true;
	    $query = "SELECT id FROM saved_reports WHERE module = 'TrackerSessions' AND deleted=0";
	    $results = $GLOBALS['db']->query($query);

    	while($row = $GLOBALS['db']->fetchByAssoc($results)) {
        	    $id = $row['id'];
                $_REQUEST['id'] = $id;
		    	require('modules/Reports/ReportCriteriaResults.php');
		    	$this->assertTrue(checkSavedReportACL($args['reporter'], $args));
		}
    }

    /**
     * Test whereby a non-admin user is given the Tracker Role and attempts to access both of the TrackerSessions reports
     *
     */
    /*
    public function test_NonAdmin_Tracker_Session_Report_access () {

    	$GLOBALS['current_user'] = $this->non_admin_user;
    	$queryTrackerRole = "SELECT id FROM acl_roles where name='Tracker'";
		$result = $GLOBALS['db']->query($queryTrackerRole);
		$trackerRoleId = $GLOBALS['db']->fetchByAssoc($result);
		if(!empty($trackerRoleId['id'])) {
		   $role1= new ACLRole();
		   $role1->retrieve($trackerRoleId['id']);
		   $role1->set_relationship('acl_roles_users', array('role_id'=>$role1->id ,'user_id'=>$this->non_admin_user->id), false);

		   global $theme, $mod_strings;
	       $theme = 'Sugar';
	       $mod_strings = return_module_language($GLOBALS['current_language'], 'Reports');
	       $GLOBALS['_REQUEST']['action'] = 'ReportCriteriaResults';
	       $GLOBALS['request_string'] = '';

	       $saved_report_seed = new SavedReport();
		   $saved_report_seed->disable_row_level_security = true;
		   $query = "SELECT id FROM saved_reports WHERE module = 'TrackerSessions'";
		   $results = $GLOBALS['db']->query($query);
	       while($row = $GLOBALS['db']->fetchByAssoc($results)) {
			      $id = $row['id'];
	              $GLOBALS['_REQUEST']['id'] = $id;
			      include('modules/Reports/ReportCriteriaResults.php');
			      $this->assertTrue(checkSavedReportACL($args['reporter'],$args));
	       }
		}
    }
    */

}



?>
