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


class Bug39756Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $_account = null;

    public function setUp() 
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->_account = new Account();
        $this->_account->name = 'Account_'.create_guid();
        $this->_account->save();

    }
    
    public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        $sql = "DELETE FROM accounts where id = '{$this->_account->id}'";
        $GLOBALS['db']->query($sql);
    }
    
    public function testUpdateDateEnteredWithValue()
    {
        global $disable_date_format;
        $disable_date_format = true;

       $newDateEntered = '2011-01-28 11:05:10';
       $oldDateEntered = $this->_account->date_entered;

       $this->_account->update_date_entered = true;
       $this->_account->date_entered = $newDateEntered;
       $this->_account->save();

       $acct = new Account();
       $acct->retrieve($this->_account->id);
       
       $this->assertNotEquals($acct->date_entered, $oldDateEntered, "Account date_entered should not be equal to old date_entered");
       $this->assertEquals($acct->date_entered, $newDateEntered, "Account date_entered should be equal to old date_entered");
    }

    public function testNoUpdateDateEnteredWithValue()
    {
        global $disable_date_format;
        $disable_date_format = true;

       $newDateEntered = '2011-01-28 11:05:10';
       $oldDateEntered = $this->_account->date_entered;

       $this->_account->date_entered = $newDateEntered;
       $this->_account->save();

       $acct = new Account();
       $acct->retrieve($this->_account->id);
       
       $this->assertEquals($acct->date_entered, $oldDateEntered, "Account date_entered should be equal to old date_entered");
       $this->assertNotEquals($acct->date_entered, $newDateEntered, "Account date_entered should not be equal to old date_entered");
    }
}
