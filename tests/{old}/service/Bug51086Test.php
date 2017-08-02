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


require_once('vendor/nusoap//nusoap.php');
require_once 'tests/{old}/service/SOAPTestCase.php';

/*
 * This test makes sure that field level acl's are honored in soap v4
 * @ticket 51086
 */

class Bug51086Test extends SOAPTestCase
{
    private $_aclRole;
    private $_contact;
    private $_blockedfield = 'description';
    private $_nonAdminUser;

    /**
     * Create test user
     *
     */
	public function setUp()
    {
        //set up non admin user, do not use default user created by class which is an admin
        $this->_nonAdminUser = SugarTestUserUtilities::createAnonymousUser();
        $this->_nonAdminUser->status = 'Active';
        $this->_nonAdminUser->is_admin = 0;
        $this->_nonAdminUser->save();
        $GLOBALS['db']->commit();
        $GLOBALS['current_user'] = $this->_nonAdminUser;

    	$this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v4/soap.php';

        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("app_strings");
        SugarTestHelper::setUp("app_list_strings");
        SugarTestHelper::setUp("mod_strings", array('Contacts'));

        //Create new ACL role
        $this->_aclRole = new ACLRole();
        $this->_aclRole->name = "Unit Test";
        $this->_aclRole->save();
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing

        //relate the acl role to the new user
        $this->_aclRole->set_relationship('acl_roles_users', array('role_id'=>$this->_aclRole->id ,'user_id'=> $GLOBALS['current_user']->id), false);
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing

        //Disable access to the blocked field on contacts bean.
        ACLField::setAccessControl('Contacts', $this->_aclRole->id, $this->_blockedfield, -99);
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing

        //create Contact
   	    $this->_contact = new Contact();
        $this->_contact->first_name = 'Joe ';
        $this->_contact->last_name = 'UT51086 ';
        $this->_contact->email1 = 'ut51086Contact@example.com';
        $this->_contact->save();
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing

        parent::setUp();
        $this->_login();
    }

    public function tearDown()
    {

        $GLOBALS['db']->query("DELETE FROM acl_roles WHERE id = '".$this->_aclRole->id."' ");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '".$this->_contact->id."' ");
        $GLOBALS['db']->commit();

        SugarTestHelper::tearDown();
        unset($this->_aclRole);
        unset($this->_contact);
        unset($this->_blockedfield);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        parent::tearDown();
    }

    //overwrite class login function because we need the user to be non admin for this test to work, otherwise acls will be ignored
    public function _login()
   {
       $GLOBALS['db']->commit();
       $result = $this->_soapClient->call('login',
           array('user_auth' =>
               array(
                   'user_name' => $this->_nonAdminUser->user_name,
                   'password' => $this->_nonAdminUser->user_hash,
                   'version' => '.01'),
               'application_name' => 'SoapTest', "name_value_list" => array())
           );
       $this->_sessionId = $result['id'];
       return $result;
   }


    //test that the soap call will honor field level acl with a passed in list of selected fields
    /**
     *
     */
    public function testFieldLevelACLWithDefinedSelect()
    {
        //make the soap call that will return the contact record with the selected fields.
        //Assert that blocked field is declared to be returned
        $selFields =  array('last_name'=>'last_name', 'first_name'=>'first_name', 'email1'=>'email1','id'=>'id',$this->_blockedfield=>$this->_blockedfield);
        $this->assertContains($this->_blockedfield, $selFields[$this->_blockedfield], 'array of selected fields does not contain blocked field ('.$this->_blockedfield.'), this test is no longer valid');

        //make soap call
        $result = $this->_soapClient->call(
            'get_entry_list',
            array(
                'session' => $this->_sessionId,
                'module_name' => 'Contacts',
                'query' => "contacts.id = '{$this->_contact->id}'",
                'order_by' => '',
                'offset' => 0,
                'select_fields' =>$selFields,
                'link_name_to_fields_array' => array(array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address'))),
                'max_results' => 20,
                'deleted' => 0,
                'favorites' => false,
                )
            );

        //assert that results were returned and grab the list of returned fields
        $this->assertNotEmpty($result['entry_list'], 'get_entry_list soap call failed, results were not returned as expected');
        $fields_returned = $result['entry_list'][0]['name_value_list'];

        //iterate through array and make sure description is not a returned field
        $foundDescription = false;
        foreach( $fields_returned as $name_val_array){
            if($this->_blockedfield == $name_val_array['name']){
                $foundDescription = true;
            }

        }



        //assert returned field is not description
        $this->assertFalse($foundDescription, 'the blocked field ('.$this->_blockedfield.') was returned with select fields specified, despite being off limits through ACLs and user not being admin.');
    }

    //Same as previous test, only without a passed in list of selected fields, meaning that all fields are returned
    public function testFieldLevelACLWithOutDefinedSelect()
    {

        //make the soap call that will return the contact record with all fields (no selected fields defined).
        $result = $this->_soapClient->call(
            'get_entry_list',
            array(
                'session' => $this->_sessionId,
                'module_name' => 'Contacts',
                'query' => "contacts.id = '{$this->_contact->id}'",
                'order_by' => '',
                'offset' => 0,
                'select_fields' =>array(),
                'link_name_to_fields_array' => array(array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address'))),
                'max_results' => 20,
                'deleted' => 0,
                'favorites' => false,
                )
            );

        //assert that results were returned and grab the list of returned fields
        $this->assertNotEmpty($result['entry_list'], 'get_entry_list soap call failed, results were not returned as expected');
        $fields_returned = $result['entry_list'][0]['name_value_list'];

        //iterate through array and make sure description is not a returned field
        $foundDescription = false;
        foreach( $fields_returned as $name_val_array){
            if($this->_blockedfield == $name_val_array['name']){
                $foundDescription = true;
            }

        }
        //assert returned field is not description
        $this->assertFalse($foundDescription, 'the blocked field ('.$this->_blockedfield.') was returned when no select fields were specified despite being off limits through ACLs and user not being admin.');
    }


}
