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
 

class Bug56373Test extends Sugar_PHPUnit_Framework_TestCase
{


	// Currently, getBreadCrumbList in BreadCrumbStack.php limits you to 10
	// Also, the Constructor in BreadCrumbStack.php limits it to 10 too.
    /*
     * @group bug56373
     */
    public function testProcessRecentRecordsForHTML() {
        $view = new Bug56373TestSugarViewMock();

        $history = array(
                        array('item_summary' => '&lt;img src=x alert(true)', 'module_name'=>'Accounts'),
                        array('item_summary' => '&lt;script&gt;alert(hi)&lt;/script&gt;', 'module_name'=>'Accounts'),


        );
        $out = $view->processRecentRecords($history);
        foreach($out as $key => $row) {
            $this->assertEquals($row['item_summary'], $history[$key]['item_summary']);
           $this->assertNotRegExp('/[<>]/',$row['item_summary_short']);
           $this->assertContains($history[$key]['item_summary'], $row['image']);
        }

    }

}

class Bug56373TestSugarViewMock extends SugarView
{
    public function processRecentRecords($history)
    {
        return parent::processRecentRecords($history);
    }
}
