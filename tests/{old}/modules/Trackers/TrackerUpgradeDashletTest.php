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
 
require_once('tests/{old}/modules/Trackers/TrackerTestUtility.php');

class TrackerUpgradeDashletTest extends Sugar_PHPUnit_Framework_TestCase  {

	var $defaultTrackingDashlets = array('TrackerDashlet', 'MyModulesUsedChartDashlet', 'MyTeamModulesUsedChartDashlet');
       
    function setUp() {
    	 $this->markTestIncomplete("Skipping unless otherwise specified");
    	
    	TrackerTestUtility::setUp(); 
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Home');      	

        $cuser = new User();
		$cuser->retrieve('1');
    	$GLOBALS['current_user'] = $cuser;
    	
	    //Set the user theme to be 'Sugar' theme since this is run for CE flavor conversions
	    $cuser->setPreference('user_theme', 'Sugar5', 0, 'global');
    	
        if(ACLController::checkAccess('Trackers', 'view', false, 'Tracker')) {
		  $pages = $GLOBALS['current_user']->getPreference('pages', 'Home');
		  $pages = !empty($pages) ? $pages : array();
		  $dashlets = $GLOBALS['current_user']->getPreference('dashlets', 'Home');
		  $dashlets = !empty($dashlets) ? $dashlets : array();
		  $new_dashlets = array();
		  
              foreach($dashlets as $id=>$dashlet) {
                if(!in_array($dashlet['className'], $this->defaultTrackingDashlets)) {
                	 $new_dashlets[$id] = $dashlet;
                }
              }
              
              $GLOBALS['current_user']->setPreference('dashlets', $new_dashlets, 0, 'Home');
              
              $new_pages = array();
              foreach($pages as $page) {
                    if(!empty($page['pageTitle']) && $page['pageTitle'] != 'Tracker') {
                    	 $new_pages[] = $page;
                    }
              }
              
              $GLOBALS['current_user']->setPreference('pages', $new_pages, 0, 'Home');
              $GLOBALS['current_user']->save();
        } //if        
    }
    
    function tearDown() {
		TrackerTestUtility::tearDown(); 
		$user = new User();
		$user->retrieve('1');
		$GLOBALS['current_user'] = $user; 		
    }

    
    function testUpgradeTrackerDashlet() {
    	$this->upgradeUserPreferencesCopy();
    	$cuser = new User();
    	$cuser->retrieve('1');
		$dashlets = $cuser->getPreference('dashlets', 'Home');
		$countAdded = 0;
		
		foreach($dashlets as $id=>$dashlet) {
			    if(in_array($dashlet['className'], $this->defaultTrackingDashlets)) {
			       $countAdded++;
			    }
		}

		$this->assertEquals($countAdded, 3);
		
		$pages = $cuser->getPreference('pages', 'Home');
	    $countAdded = 0;
		foreach($pages as $id=>$page) {
			    if($page['pageTitle'] == 'Tracker') {
			       $countAdded++;
			    }
		}
		
		$theme = $cuser->getPreference('user_theme', 'global');
		$this->assertTrue($theme == 'Sugar');
		$this->assertTrue($countAdded == 1);
    }    
    
    
/**
 * upgradeUserPreferencesCopy
 *
 */
private function upgradeUserPreferencesCopy() {
	

	if(file_exists($GLOBALS['sugar_config']['cache_dir'].'dashlets/dashlets.php')) {
   	   require($GLOBALS['sugar_config']['cache_dir'].'dashlets/dashlets.php');
   	} else if(file_exists('modules/Dashboard/dashlets.php')) {
   	   require('modules/Dashboard/dashlets.php');
   	}

	$upgradeTrackingDashlets = array('TrackerDashlet'=>array(
									    'file' => 'modules/Trackers/Dashlets/TrackerDashlet/TrackerDashlet.php',
									    'class' => 'TrackerDashlet',
									    'meta' => 'modules/Trackers/Dashlets/TrackerDashlet/TrackerDashlet.meta.php',
									    'module' => 'Trackers',
									 ),
									 'MyModulesUsedChartDashlet'=>array(
									    'file' => 'modules/Charts/Dashlets/MyModulesUsedChartDashlet/MyModulesUsedChartDashlet.php',
									    'class' => 'MyModulesUsedChartDashlet',
									    'meta' => 'modules/Charts/Dashlets/MyModulesUsedChartDashlet/MyModulesUsedChartDashlet.meta.php',
									    'module' => 'Trackers',
									 ),
									 'MyTeamModulesUsedChartDashlet'=>array(
									    'file' => 'modules/Charts/Dashlets/MyTeamModulesUsedChartDashlet/MyTeamModulesUsedChartDashlet.php',
									    'class' => 'MyTeamModulesUsedChartDashlet',
									    'meta' => 'modules/Charts/Dashlets/MyTeamModulesUsedChartDashlet/MyTeamModulesUsedChartDashlet.meta.php',
									    'module' => 'Trackers',
									 )
							   );

	$GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Home');
	
    $db = DBManagerFactory::getInstance();
    $result = $db->query("SELECT id FROM users where deleted = '0'");
   	while($row = $db->fetchByAssoc($result)){
	      $current_user = new User();
	      $current_user->retrieve($row['id']);
	      
	      //Set the user theme to be 'Sugar' theme since this is run for CE flavor conversions
	      $current_user->setPreference('user_theme', 'Sugar', 0, 'global');	      
	      
		  $pages = $current_user->getPreference('pages', 'Home');

		  if(empty($pages)) {
                continue;
		  }

		  $empty_dashlets = array();
		  $dashlets = $current_user->getPreference('dashlets', 'Home');
		  $dashlets = !empty($dashlets) ? $dashlets : $empty_dashlets;
   		  $existingDashlets = array();
   		  foreach($dashlets as $id=>$dashlet) {
   		  	      if(!empty($dashlet['className']) && !is_array($dashlet['className'])) {
		  	         $existingDashlets[$dashlet['className']] = $dashlet['className'];
   		  	      }
		  }

		  if(ACLController::checkAccess('Trackers', 'view', false, 'Tracker')) {
				$trackingDashlets = array();
			    foreach($upgradeTrackingDashlets as $trackingDashletName=>$entry){
			    	if (empty($existingDashlets[$trackingDashletName])) {
			            $trackingDashlets[create_guid()] = array('className' => $trackingDashletName,
				                                                 'fileLocation' => $entry['file'],
			                                                     'options' => array());
			    	}
			    }

			    if(empty($trackingDashlets)) {
			       continue;
			    }

			    $trackingColumns = array();
			    $trackingColumns[0] = array();
			    $trackingColumns[0]['width'] = '50%';
			    $trackingColumns[0]['dashlets'] = array();

			    foreach($trackingDashlets as $guid=>$dashlet){
			            array_push($trackingColumns[0]['dashlets'], $guid);
			    }

			    //Set the tracker dashlets to user preferences table
		 		$dashlets = array_merge($dashlets, $trackingDashlets);
		 		$current_user->setPreference('dashlets', $dashlets, 0, 'Home');

		    	//Set the dashlets pages to user preferences table
		    	$pageIndex = count($pages);
				$pages[$pageIndex]['columns'] = $trackingColumns;
				$pages[$pageIndex]['numColumns'] = '1';
				$pages[$pageIndex]['pageTitle'] = $GLOBALS['mod_strings']['LBL_HOME_PAGE_4_NAME'];
				$current_user->setPreference('pages', $pages, 0, 'Home');
		  } //if
	} //while
	
    /*	
	 * This section checks to see if the Tracker settings for the corresponding versions have been 
	 * disabled and the regular tracker (for breadcrumbs) enabled.  If so, then it will also disable
	 * the tracking for the regular tracker.  Disabling the tracker (for breadcrumbs) will no longer prevent
	 * breadcrumb tracking.  It will instead only track visible entries (see trackView() method in SugarView.php).
	 * This has the benefit of reducing the tracking overhead and limiting it to only visible items.
	 * For the CE version, we are checking to see that there are no entries enabled for PRO/ENT versions 
	 * we are checking for Tracker sessions, performance and queries.	
	 */		
	if(isset($_SESSION['upgrade_from_flavor']) && preg_match('/^SugarCE.*?(Pro|Ent|Corp|Ult)$/', $_SESSION['upgrade_from_flavor'])) {
		//Set tracker settings. Disable tracker session, performance and queries
		$category = 'tracker';
		$value =1;
		$key = array('Tracker', 'tracker_sessions','tracker_perf','tracker_queries');
		$admin = new Administration();
		foreach($key as $k){
			$admin->saveSetting($category, $k, $value);
		}	
	} else {
		//If only Tracker for breadcrumbs is enabled then this should be 3	
		if($GLOBALS['sugar_flavor'] == 'PRO' || $GLOBALS['sugar_flavor'] == 'ENT') {
		   $query = "select count(name) as total from config where category = 'tracker' and name in ('tracker_sessions', 'tracker_perf', 'tracker_queries')";
		   $results = $db->query($query);
		   if(!empty($results)) {
		       $row = $db->fetchByAssoc($results);
		       //We are assuming if the 3 settings were already disabled, then also disable the 'Tracker' setting
		       if($row['total'] == 3) {
		       	  $db->query("INSERT INTO config (category, name, value) VALUES ('tracker', 'Tracker', '1')");
		       }
		   }
		} else {
		   $query = "select count(name) as total from config where category = 'tracker' and name = 'Tracker'";
		   $results = $db->query($query);
		   if(!empty($results)) {
		       $row = $db->fetchByAssoc($results);
		       //We are assuming if the 'Tracker' setting is not disabled then we will just disable it
		       if($row['total'] == 0) {
		       	  $db->query("INSERT INTO config (category, name, value) VALUES ('tracker', 'Tracker', '1')");
		       }
		   }
		}
	}
	
	//Write the entries to cache/dashlets/dashlets.php
	if(file_exists($GLOBALS['sugar_config']['cache_dir'].'dashlets/dashlets.php')) {
	   require($GLOBALS['sugar_config']['cache_dir'].'dashlets/dashlets.php');
	   foreach($upgradeTrackingDashlets as $id=>$entry) {
	   	   if(!isset($dashletsFiles[$id])) {
	   	   	  $dashletsFiles[$id] = $entry;
	   	   }
	   } 
	   write_array_to_file("dashletsFiles", $dashletsFiles, $GLOBALS['sugar_config']['cache_dir'].'dashlets/dashlets.php');
	} //if
	
	//If only Tracker for breadcrumbs is enabled then this should be 3	
	if($GLOBALS['sugar_flavor'] == 'PRO' || $GLOBALS['sugar_flavor'] == 'ENT') {
	   $query = "select count(name) as total from config where category = 'tracker' and name in ('tracker_sessions', 'tracker_perf', 'tracker_queries')";
	   $results = $db->query($query);
	   if(!empty($results)) {
	       $row = $db->fetchByAssoc($results);
	       //We are assuming if the 3 settings were already disabled, then also disable the 'Tracker' setting
	       if($row['total'] == 3) {
	       	  $db->query("INSERT INTO config (category, name, value) VALUES ('tracker', 'Tracker', '1')");
	       }
	   }
	} else {
	   $query = "select count(name) as total from config where category = 'tracker' and name = 'Tracker'";
	   $results = $db->query($query);
	   if(!empty($results)) {
	       $row = $db->fetchByAssoc($results);
	       //We are assuming if the 'Tracker' setting is not disabled then we will just disable it
	       if($row['total'] == 0) {
	       	  $db->query("INSERT INTO config (category, name, value) VALUES ('tracker', 'Tracker', '1')");
	       }
	   }
	}
}    
    
}  
?>
