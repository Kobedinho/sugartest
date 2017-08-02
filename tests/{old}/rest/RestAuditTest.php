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

class RestAuditTest extends RestTestBase
{
    private $account_id;
    
    public function setUp() 
    {
        parent::setUp();
    }
    
    public function tearDown()
    {
        if ( isset($this->account_id) ) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->account_id}'");
            $GLOBALS['db']->query("DELETE FROM accounts_audit WHERE parent_id = '{$this->account_id}'");
        }
        parent::tearDown();
    }
           
    public function testViewChangeLog() {
        // FIXME TY-1311: investigate why this test fails
        // For some unknown reason, creating an account directly will cause a 'out of memory' error in SugarBean::retrieve()
        // when we call AuditApi below. This has something to do with how an account is created, not the api.
        // The api works fine when tested using Postman
        $restReply = $this->_restCall("Accounts/",
                json_encode(array('name'=>'UNIT TEST - BEFORE', 'my_favorite' => true)),
                'POST');
        $this->assertTrue(isset($restReply['reply']['id']),
                "An account was not created (or if it was, the ID was not returned)");       
        $this->account_id = $restReply['reply']['id'];
        $account = new Account();
        $account->retrieve($this->account_id);
        $account->name = "UNIT TEST - AFTER";
        $account->save();
        $GLOBALS['db']->commit();        
        $restReply = $this->_restCall('Audit?module=Accounts&record='.$this->account_id);
        $this->assertNotEmpty($restReply['reply']['records'],"There should be one record");
    }
}
