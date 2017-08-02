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
 * @ticket 53942
 */
class Bug53942Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function testImportTZWithQuotes()
	{
        $this->markTestIncomplete('File ics not found. Needs to be fixed by the FRM team.');
	    $ic = new iCalendar();
	    $ic->parse(file_get_contents(dirname(__FILE__).'/Bug53942Test.ics'));
	    $event = null;
	    foreach ($ic->data['calendar'] as $calendar_key=>$calendar_val) {
	    	foreach ($calendar_val->stack as $key=>$val) {
	    		if($val instanceof vEvent) {
	    		    $event = $val;
	    		    break;
	    		}
	    	}
	    }
	    $this->assertNotEmpty($event, "Event not found!");
        $this->assertEquals("2012-06-21 16:00:00", $event->event->date_start);
        $this->assertEquals("2012-06-21 16:30:00", $event->event->date_end);
	}
}
