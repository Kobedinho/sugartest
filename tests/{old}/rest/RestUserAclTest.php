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

class RestUserAclTest extends RestTestBase {

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM users WHERE id = '{$this->new_user_id}'");
        $GLOBALS['db']->query("DELETE FROM users WHERE id = '{$this->admin_user_id}'");
        $GLOBALS['db']->query("DELETE FROM users WHERE id = '{$this->deleted_user_id}'");
    }

    /**
     * @group rest
     */
    public function testAcls() {
        $restReply = $this->_restCall("Users/",
            json_encode(array('first_name'=>'UNIT TEST', 'last_name' => '- AFTER')),
            'POST');

        $this->assertTrue(!isset($restReply['reply']['id']),
            "An user was created");

        $this->assertEquals($restReply['reply']['error'], 'not_authorized',
            "An user was created");

        $restReply = $this->_restCall("/me", json_encode(array('first_name' => 'Awesome')), 'PUT');

        $this->assertEquals($restReply['reply']['current_user']['full_name'], 'Awesome ' . $GLOBALS['current_user']->last_name, 'Did not change my first name');

        // test create user with admin
        $old_user = $GLOBALS['current_user'];
        $user = new User();
        $user->user_name = 'captain';
        $user->user_hash = $user->getPasswordHash('awesome');
        $user->is_admin = 1;
        $user->first_name = 'captain';
        $user->last_name = 'awesome';
        $user->save();

        $this->_restLogin($user->user_name, 'awesome');

        // make sure he can't delete himself..that would be silly
        $restReply = $this->_restCall("Users/{$user->id}", array(), "DELETE");

        $this->assertEquals($restReply['reply']['error'], 'not_authorized',
            "You just deleted yourself..");

        $restReply = $this->_restCall("Users/",
            json_encode(array('first_name'=>'UNIT TEST', 'last_name' => '- AFTER', 'is_admin' => true)),
            'POST');

        $this->assertTrue(isset($restReply['reply']['id']),
            "An user was not created");

        $this->assertEquals($restReply['reply']['is_admin'], true, "Is admin was not set");

        $this->new_user_id = $restReply['reply']['id'];

        $restReply = $this->_restCall("Users/{$this->new_user_id}", array(), "DELETE");

        $this->assertTrue(isset($restReply['reply']['id']),
            "An user was not deleted");

        $this->deleted_user_id = $this->new_user_id;

        $restReply = $this->_restCall("Users/",
            json_encode(array('first_name'=>'UNIT TEST', 'last_name' => '- AFTER', 'is_admin' => true)),
            'POST');

        $this->assertTrue(isset($restReply['reply']['id']),
            "An user was not created");

        $this->new_user_id = $restReply['reply']['id'];

        $this->admin_user_id = $user->id;
        // test is_admin set with original user
        $this->_restLogin();

        $restReply = $this->_restCall("Users/{$this->new_user_id}", json_encode(array("is_admin" => false)), "PUT");

        $this->assertEquals($restReply['reply']['error'], 'not_authorized',
            "An user was created");

        // test delete with original user
        $restReply = $this->_restCall("Users/{$this->new_user_id}", array(), "DELETE");

        $this->assertEquals($restReply['reply']['error'], 'not_authorized',
            "An user was deleted");

        //test getting picture file of another user
        $restReply = $this->_restCall("Users/1/file/picture", array());

        $this->assertEmpty($restReply['reply']['error'], "An error was thrown it was: " . print_r($restReply['reply']['error'], true));

        $this->assertNotEmpty($restReply['replyRaw'], "No reply");
    }

}
