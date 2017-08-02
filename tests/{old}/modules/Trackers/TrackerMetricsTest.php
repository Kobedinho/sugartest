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
 
require_once('modules/Trackers/TrackerTestUtility.php');

class TrackerMetricsTest extends Sugar_PHPUnit_Framework_TestCase {

	var $trackerSettings;
	
	function setUp() {
		TrackerTestUtility::setUp(); 		
	}
	
    function tearDown() {
    	TrackerTestUtility::tearDown();
    }
    
    function testMetrics() {
        $trackerManager = TrackerManager::getInstance();
	    $monitor = $trackerManager->getMonitor('tracker');
        $metrics = $monitor->getMetrics();
        foreach($metrics as $metric) {
           if($metric->name() == 'monitor_id') {
           	  $this->assertFalse($metric->isMutable(), "Test that {$metric->name()} is not mutable");
           } else {
           	  $this->assertTrue($metric->isMutable(), "Test that {$metric->name()} is mutable");
           }
        }
    }
    
}  
?>
