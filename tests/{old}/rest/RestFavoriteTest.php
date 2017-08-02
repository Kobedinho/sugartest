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

class RestFavoriteTest extends RestTestBase {
    public function setUp()
    {
        parent::setUp();
    }
    
    public function tearDown()
    {
        if ( isset($this->account_id) ) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->account_id}'");
            $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id = '{$this->account_id}'");
        }
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE created_by = '".$GLOBALS['current_user']->id."'");
        parent::tearDown();
    }

    public function testSetFavorite() {
        $restReply = $this->_restCall("Accounts/",
                                      json_encode(array('name'=>'UNIT TEST - AFTER', 'my_favorite' => true)),
                                      'POST');

        $this->assertTrue(isset($restReply['reply']['id']),
                          "An account was not created (or if it was, the ID was not returned)");

        $this->assertTrue(isset($restReply['reply']['team_name'][0]['name']), "A team name was not set.");

        $this->account_id = $restReply['reply']['id'];
        
        $account = new Account();
        $account->retrieve($this->account_id);

        $this->assertEquals("UNIT TEST - AFTER",
                            $account->name,
                            "Did not set the account name.");


        $restReply = $this->_restCall("Accounts/{$account->id}/favorite", array(), 'PUT');
        
        $is_fav = SugarFavorites::isUserFavorite('Accounts', $account->id, $this->_user->id);
        
        $this->assertEquals($is_fav, (bool) $restReply['reply']['my_favorite'], "The returned favorite was not the same.");


        $restReply = $this->_restCall("Accounts/{$account->id}/unfavorite", array(), 'PUT');

        
        $is_fav = SugarFavorites::isUserFavorite('Accounts', $account->id, $this->_user->id);
        
        $this->assertEquals($is_fav, (bool) $restReply['reply']['my_favorite'], "The returned favorite was not the same.");

        $restReply = $this->_restCall("Accounts/{$account->id}/favorite", array(), 'PUT');
        
        $is_fav = SugarFavorites::isUserFavorite('Accounts', $account->id, $this->_user->id);
        
        $this->assertEquals($is_fav, (bool) $restReply['reply']['my_favorite'], "The returned favorite was not the same.");


        $restReply = $this->_restCall("Accounts/{$account->id}/favorite", array(), 'DELETE');

        
        $is_fav = SugarFavorites::isUserFavorite('Accounts', $account->id, $this->_user->id);
        
        $this->assertEquals($is_fav, (bool) $restReply['reply']['my_favorite'], "The returned favorite was not the same.");



    }

}
