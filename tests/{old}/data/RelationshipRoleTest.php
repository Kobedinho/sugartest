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

class RelationshipRoleTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $createdBeans = array();
    protected $createdFiles = array();

    public static function setUpBeforeClass()
	{
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $GLOBALS['current_user']->setPreference('timezone', "America/Los_Angeles");
	    $GLOBALS['current_user']->setPreference('datef', "m/d/Y");
		$GLOBALS['current_user']->setPreference('timef', "h.iA");
	}

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

	public function tearDown()
	{
	    SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestTaskUtilities::removeAllCreatedTasks();

        foreach($this->createdFiles as $file)
        {
            if (is_file($file))
            {
                unlink($file);
            }
        }
	}
	

    /**
     * Create a new account and bug, then link them.
     * @return void
     */
	public function testQuoteAccountsRole()
	{
	    $account = SugarTestAccountUtilities::createAccount();
        $account->name = "RoleTestAccount";
        $account->save();

        $quote = SugarTestQuoteUtilities::createQuote();
        $quote->load_relationship("billing_accounts");
        $quote->billing_accounts->add($account);

        //Now check the row in the database
        $quote->set_account();
        $this->assertEquals($account->name, $quote->billing_account_name);
    }

    /**
     * Create a new account and bug, then link them.
     * @return void
     */
	public function testOne2MGetJoinWithRole()
	{
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        $db = DBManagerFactory::getInstance();
        $task = SugarTestTaskUtilities::createTask();
        $task->name = "RoleTestTask";
        $task->save();
        $this->createdBeans[] = $task;

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->name = "RoleTestOpp";
        $opp->save();
        $this->createdBeans[] = $opp;

        $task->load_relationship("opportunities");
        $task->opportunities->add($opp);
        $join = $task->opportunities->getJoin(array(
            'join_type' => "LEFT JOIN",
            'right_join_table_alias' => "jt1",
            'right_join_table_link_alias' => "jtl_1",
            'join_table_alias' => "jt2",
            'join_table_link_alias' => "jtl_2",
            'left_join_table_alias' => "jt2",
            'left_join_table_link_alias' => "jtl_2",
            'primary_table_name' => "jt2",
        ));
        $this->assertContains("jt1.parent_type = 'Opportunities'", $join);
        $this->assertContains("jt1.parent_id=jt2.id", $join);
        $result = $db->query("SELECT count(jt1.id) as count FROM tasks jt1 $join WHERE jt1.id='{$task->id}'");
        $this->assertTrue($result != false, "One2M getJoin returned invalid SQL");
        //sqlsrv_num_rows seems buggy
        //$this->assertEquals(1, $db->getRowCount($result));
        $row = $db->fetchByAssoc($result);
        $this->assertEquals(1, $row['count']);

        //Now check that it also works from the other side
        $opp->load_relationship("tasks");
        $join = $opp->tasks->getJoin(array(
            'join_type' => "LEFT JOIN",
            'right_join_table_alias' => "jt2",
            'right_join_table_link_alias' => "jt2_1",
            'join_table_alias' => "jt2",
            'join_table_link_alias' => "jt2_2",
            'left_join_table_alias' => "jt1",
            'left_join_table_link_alias' => "jtl_2",
            'primary_table_name' => "jt1",
        ));
        $this->assertContains("jt2.parent_type = 'Opportunities'", $join);
        $this->assertContains("jt1.id=jt2.parent_id", $join);
        $result = $db->query("SELECT count(jt1.id) as count FROM opportunities jt1 $join WHERE jt1.id='{$opp->id}'");
        $this->assertTrue($result != false, "One2M getJoin returned invalid SQL");

        //sqlsrv_num_rows seems buggy
        //$this->assertEquals(1, $db->getRowCount($result));
        $row = $db->fetchByAssoc($result);
        $this->assertEquals(1, $row['count']);
    }
}
