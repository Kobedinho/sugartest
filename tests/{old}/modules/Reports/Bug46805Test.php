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


require_once('include/modules.php');


/**
 * Bug #46805
 * SQL error when Edit 'Opportunities By Lead Source' chart using MSSQL
 *
 * @author mgusev
 *
 */
class Bug46805Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Test emulate mssql connection and tries to assert number of left and right brackets from generated query.
     * @ticket 40433
     * @return void
     */
	public function setUp()
	{
		require('include/modules.php');
	    $GLOBALS['beanList'] = $beanList;
	    $GLOBALS['beanFiles'] = $beanFiles;
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}
	
	public function tearDown()
	{
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	    unset($GLOBALS['current_user']);
	   	unset($GLOBALS['beanList']);
	    unset($GLOBALS['beanFiles']);
	}
    function testOrderFields()
    {
        $db = new stdClass();
        $db->dbType = 'mssql';
        $report = new Report();
        $report->db = $db;
        $report->select_fields = array(
            'test1'
        );
        $report->from = ' FROM test';

        $report->order_by_arr = array(
            '(test.test1=\'\' OR test.test1 IS NULL)  DESC, test.test1=\'1\'  DESC'
        );
        $report->create_query();

        $report->order_by_arr = array(
            '([!@#$%^&*()_+ ].[!@#$%^&*()_+ ]=\'\' OR [!@#$%^&*()_+ ].[!@#$%^&*()_+ ] IS NULL)  DESC, [!@#$%^&*()_+ ].[!@#$%^&*()_+ ]=\'1\'  DESC'
        );
        $report->create_query();

        $report->order_by_arr = array(
            '(test1=\'\' OR test1 IS NULL)  DESC, test1=\'1\'  DESC'
        );
        $report->create_query();

        $report->order_by_arr = array(
            '([!@#$%^&*()_+ ]=\'\' OR [!@#$%^&*()_+ ] IS NULL)  DESC, [!@#$%^&*()_+ ]=\'1\'  DESC'
        );
        $report->create_query();

        foreach ($report->query_list as $query)
        {
            $query = preg_replace('/\[[^\]]+\]/', '', $query);
            $query = preg_replace('/[^\(\)]/', '', $query);
            // Compare number of left and right brackets. Query is not valid if their number is not equal.
            $this->assertEquals(substr_count($query, '('), substr_count($query, ')'), 'Number of left/right brackets should be equal');
        }
    }
}
