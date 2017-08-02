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

require_once('tests/{old}/rest/RestTestBase.php');

class RestBug54519Test extends RestTestBase {
    public function setUp()
    {
        parent::setUp();
    }
    
    public function tearDown()
    {
        if ( isset($this->account_id) ) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->account_id}'");
            if ($GLOBALS['db']->tableExists('accounts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c = '{$this->account_id}'");
            }
            $GLOBALS['db']->commit();
        }
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testCreate()
    {
        $restReply = $this->_restCall("Accounts/",
                                      json_encode(array('name'=>'UNIT TEST - AFTER &nbsp;')),
                                      'POST');

        $this->assertTrue(isset($restReply['reply']['id']),
                          "An account was not created (or if it was, the ID was not returned)");


        $this->account_id = $restReply['reply']['id'];
        
        $account = new Account();
        $account->retrieve($this->account_id);
        $restReply = $this->_restCall("Accounts/".$this->account_id,
                                      json_encode(array()),
                                      'GET');
        $this->assertEquals("UNIT TEST - AFTER  ",
            $restReply['reply']['name'],
                            "Did not return the account name.");
    }

}