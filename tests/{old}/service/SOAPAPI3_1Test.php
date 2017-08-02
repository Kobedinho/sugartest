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

require_once 'tests/{old}/service/SOAPTestCase.php';
/**
 * This class is meant to test everything SOAP
 *
 */
class SOAPAPI3_1Test extends SOAPTestCase
{
    static protected $_contactId = '';
    static protected $_opportunities = array();

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        parent::setUpBeforeClass();
        $contact = SugarTestContactUtilities::createContact();
        self::$_contactId = $contact->id;
    }

    /**
     * Create test user
     *
     */
	public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v3_1/soap.php';
		parent::setUp();
        $this->_login();
    }

    /**
     * Remove anything that was used during this test
     *
     */
    public function tearDown() {
        $GLOBALS['db']->query("DELETE FROM accounts WHERE name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name like 'UNIT TEST%' ");
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        if(!empty(self::$_opportunities)) {
            $GLOBALS['db']->query('DELETE FROM opportunities WHERE id IN (\'' . implode("', '", self::$_opportunities) . '\')');
        }
        parent::tearDownAfterClass();
        SugarTestHelper::tearDown();
    }

    /**
	 * Ensure we can create a session on the server.
	 *
	 */
    public function testCanLogin(){
		$result = $this->_login();
    	$this->assertTrue(!empty($result['id']) && $result['id'] != -1,
            'SOAP Session not created. Error ('.$this->_soapClient->faultcode.'): '.$this->_soapClient->faultstring.': '.$this->_soapClient->faultdetail);
    }

   public function testGetEntryListWithLinkFields()
   {
       $c1_id = uniqid();
       $c1 = new Contact();
       $c1->id = $c1_id;
       $c1->email1 = "fee@bar.com";

       $c1->new_with_id = TRUE;
       $c1->first_name = "UNIT TEST";
       $c1->last_name = "UNIT_TEST";
       $c1->assigned_user_id = $GLOBALS['current_user']->id;
       $c1->save();
       $GLOBALS['db']->commit();

       $soap_data = array('session' => $this->_sessionId,
                          'module_name' => 'Contacts',
                          'query' => "contacts.id = '$c1_id'",
                          'order_by' => 'name',
                          'offset' => '',
                          'select_fields' => array('first_name','last_name'),
                          'link_name_to_fields_array' => array(array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address'))),
                          'max_results' => 20,
                          'deleted' => 0,
                          'favorites' => 0
                          );

       $result = $this->_soapClient->call('get_entry_list', $soap_data);
       $actual = $result['relationship_list'][0]['link_list'][0]['records'][0]['link_value'][1]['value'];
       $this->assertEquals($c1->email1, $actual);
    }
    /**
     * Test get avaiable modules call
     *
     */
    function testGetAvailableModulesForMobile()
    {
        $soap_data = array('session' => $this->_sessionId,'filter' => 'mobile');

        $result = $this->_soapClient->call('get_available_modules', $soap_data);

        $actual = $result['modules'][0];
        $this->assertEquals('Accounts', $actual['module_key'] );
        $this->assertArrayHasKey('acls', $actual);
    }
    /**
     * Test get avaiable modules call
     *
     */
    function testGetAllAvailableModules()
    {
        $soap_data = array('session' => $this->_sessionId);

        $result = $this->_soapClient->call('get_available_modules', $soap_data);
        $actual = $result['modules'][0];
        $this->assertArrayHasKey("module_key", $actual);
        $this->assertArrayHasKey("module_label", $actual);
        $this->assertArrayHasKey("acls", $actual);

        $soap_data = array('session' => $this->_sessionId, 'filter' => 'all');

        $result = $this->_soapClient->call('get_available_modules', $soap_data);
        $actual = $result['modules'][0];
        $this->assertArrayHasKey("module_key", $actual);
        $this->assertArrayHasKey("module_label", $actual);
        $this->assertArrayHasKey("acls", $actual);
    }

    public function testGetEntryList()
    {

       $account_name = 'UNIT_TEST ' . uniqid();
       $account_id = uniqid();
       $a1 = new Account();
       $a1->id = $account_id;
       $a1->new_with_id = TRUE;
       $a1->name = $account_name;
       $a1->save();
       $GLOBALS['db']->commit();

       $soap_data = array('session' => $this->_sessionId,
                          'module_name' => 'Accounts',
                          'query' => "accounts.name = '$account_name'",
                          'order_by' => 'name',
                          'offset' => '',
                          'select_fields' => array('name'),
                          'link_name_to_fields_array' => array(),
                          'max_results' => 20,
                          'deleted' => 0,
                          'favorites' => 0
                          );

       $result = $this->_soapClient->call('get_entry_list', $soap_data);

       $GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$a1->id}'");

       $actual = $result['entry_list'][0]['name_value_list'][0]['value'];
       $this->assertEquals($account_name, $actual);

   }


    public function testSetEntryForContact()
    {
    	$result = $this->_setEntryForContact();
		$soap_version_test_contactId = $result['id'];
    	$this->assertTrue(!empty($result['id']) && $result['id'] != -1,
            'Can not create new contact. Error ('.$this->_soapClient->faultcode.'): '.$this->_soapClient->faultstring.': '.$this->_soapClient->faultdetail);
    } // fn

    public function testGetEntryForContact()
    {
    	$setresult = $this->_setEntryForContact();
        $result = $this->_getEntryForContact($setresult['id']);
    	if (empty($this->_soapClient->faultcode)) {
    		if (($result['entry_list'][0]['name_value_list'][2]['value'] == 1) &&
    			($result['entry_list'][0]['name_value_list'][3]['value'] == "Cold Call")) {

    			$this->assertEquals($result['entry_list'][0]['name_value_list'][2]['value'],1,"testGetEntryForContact method - Get Entry For contact is not same as Set Entry");
    		} // else
    	} else {
    		$this->fail('Can not retrieve newly created contact. Error ('.$this->_soapClient->faultcode.'): '.$this->_soapClient->faultstring.': '.$this->_soapClient->faultdetail);
    	}
    } // fn

    /**
     * @ticket 38986
    */
    public function testGetEntryForContactNoSelectFields()
    {
        $result = $this->_soapClient->call('get_entry',array('session'=>$this->_sessionId,'module_name'=>'Contacts','id'=>self::$_contactId,'select_fields'=>array(), 'link_name_to_fields_array' => array()));
		$this->assertNotEmpty($result['entry_list'][0]['name_value_list'], "testGetEntryForContactNoSelectFields returned no field data");
    }

    public function testSetEntriesForAccount()
    {
    	$result = $this->_setEntriesForAccount();
    	$this->assertTrue(!empty($result['ids']) && $result['ids'][0] != -1,
            'Can not create new account using testSetEntriesForAccount. Error ('.$this->_soapClient->faultcode.'): '.$this->_soapClient->faultstring.': '.$this->_soapClient->faultdetail);
    } // fn

    public function testSetEntryForOpportunity()
    {
    	$result = $this->_setEntryForOpportunity();
    	$this->assertTrue(!empty($result['id']) && $result['id'] != -1,
            'Can not create new account using testSetEntryForOpportunity. Error ('.$this->_soapClient->faultcode.'): '.$this->_soapClient->faultstring.': '.$this->_soapClient->faultdetail);
    } // fn

    public function testSetRelationshipForOpportunity()
    {
    	$setresult = $this->_setEntryForOpportunity();
        $result = $this->_setRelationshipForOpportunity($setresult['id']);
    	$this->assertTrue(($result['created'] > 0), 'testSetRelationshipForOpportunity method - Relationship for opportunity to Contact could not be created');

    } // fn


    public function testGetRelationshipForOpportunity()
    {
    	$setresult = $this->_setEntryForOpportunity();
        $this->_setRelationshipForOpportunity($setresult['id']);
        $result = $this->_getRelationshipForOpportunity($setresult['id']);
    	$this->assertEquals(self::$_contactId,
    	    $result['entry_list'][0]['id'],
    	    "testGetRelationshipForOpportunity - Get Relationship of Opportunity to Contact failed"
            );
    } // fn

    public function testSearchByModule()
    {
    	$result = $this->_searchByModule();
    	$this->assertTrue(($result['entry_list'][0]['records'] > 0 && $result['entry_list'][1]['records'] && $result['entry_list'][2]['records']), "testSearchByModule - could not retrieve any data by search");
    } // fn

   public function testGetRelationshipWithCondition()
   {
       $this->markTestIncomplete("Test is failing on non-mysql db, working with David W. to fix");
       $account_name = 'UNIT_TEST ' . uniqid();
       $account_id = uniqid();
       $a1 = new Account();
       $a1->id = $account_id;
       $a1->new_with_id = TRUE;
       $a1->name = $account_name;
       $a1->save();

       $contact1 = SugarTestContactUtilities::createContact();
       $contact1->last_name = "New Contact 1";
       $contact1->save();

       $contact2 = SugarTestContactUtilities::createContact();
       $contact2->last_name = "New Contact 2";
       $contact2->save();

       $a1->load_relationship("contacts");
       $a1->contacts->add($contact1);
       $a1->contacts->add($contact2);

       $result = $this->_soapClient->call('get_relationships',
           array(
               'session' => $this->_sessionId,
               'module' => 'Accounts',
               'module_id' => $account_id,
               'link_field_name' => 'contacts',
               'related_module_query' => 'contacts.last_name = "New Contact 2"',
               'related_fields' => array('last_name','description'),
               'related_module_link_name_to_fields_array' => array(),
               'deleted' => false,
           )
       );
       $contact1->mark_deleted($contact1->id);
       $contact2->mark_deleted($contact2->id);

       $this->assertNotEmpty($result['entry_list']);
       $this->assertEquals(1, sizeof($result['entry_list']));
   } // fn

    /**********************************
     * HELPER PUBLIC FUNCTIONS
     **********************************/

    public function _setEntryForContact()
    {
		global $timedate;
		$current_date = $timedate->nowDb();
        $time = mt_rand();
    	$first_name = 'SugarContactFirst' . $time;
    	$last_name = 'SugarContactLast';
    	$email1 = 'contact@sugar.com';
		$result = $this->_soapClient->call('set_entry',array('session'=>$this->_sessionId,'module_name'=>'Contacts', 'name_value_list'=>array(array('name'=>'last_name' , 'value'=>"$last_name"), array('name'=>'first_name' , 'value'=>"$first_name"), array('name'=>'do_not_call' , 'value'=>"1"), array('name'=>'birthdate' , 'value'=>"$current_date"), array('name'=>'lead_source' , 'value'=>"Cold Call"), array('name'=>'email1' , 'value'=>"$email1"))));
		SugarTestContactUtilities::setCreatedContact(array($result['id']));
		return $result;
    } // fn

    public function _getEntryForContact($id)
    {
		$result = $this->_soapClient->call('get_entry',array('session'=>$this->_sessionId,'module_name'=>'Contacts','id'=>$id,'select_fields'=>array('last_name', 'first_name', 'do_not_call', 'lead_source', 'email1'), 'link_name_to_fields_array' => array(array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address')))));
		return $result;
    }

    public function _setEntriesForAccount()
    {
		global $timedate;
		$current_date = $timedate->nowDb();
        $time = mt_rand();
    	$name = 'SugarAccount' . $time;
        $email1 = 'account@'. $time. 'sugar.com';
		$result = $this->_soapClient->call('set_entries',array('session'=>$this->_sessionId,'module_name'=>'Accounts', 'name_value_lists'=>array(array(array('name'=>'name' , 'value'=>"$name"), array('name'=>'email1' , 'value'=>"$email1")))));
		$soap_version_test_accountId = $result['ids'][0];
		SugarTestAccountUtilities::setCreatedAccount(array($soap_version_test_accountId));
		return $result;
    } // fn

    public function _setEntryForOpportunity()
    {
		global $timedate;
		$date_closed = $timedate->getNow()->get("+1 week")->asDb();
        $time = mt_rand();
    	$name = 'SugarOpportunity' . $time;
    	$account = SugarTestAccountUtilities::createAccount();
    	$sales_stage = 'Prospecting';
    	$probability = 10;
    	$amount = 1000;
		$result = $this->_soapClient->call('set_entry',array('session'=>$this->_sessionId,'module_name'=>'Opportunities', 'name_value_lists'=>array(array('name'=>'name' , 'value'=>"$name"), array('name'=>'amount' , 'value'=>"$amount"), array('name'=>'probability' , 'value'=>"$probability"), array('name'=>'sales_stage' , 'value'=>"$sales_stage"), array('name'=>'account_id' , 'value'=>$account->id))));
		self::$_opportunities[] = $result['id'];
		return $result;
    } // fn

    public function _setRelationshipForOpportunity($id)
    {
		$result = $this->_soapClient->call('set_relationship',array('session'=>$this->_sessionId,'module_name' => 'Opportunities','module_id' => $id, 'link_field_name' => 'contacts','related_ids' =>array(self::$_contactId), 'name_value_list' => array(array('name' => 'contact_role', 'value' => 'testrole')), 'delete'=>0));
		return $result;
    } // fn

    public function _getRelationshipForOpportunity($id)
    {
		$result = $this->_soapClient->call('get_relationships',
				array(
                'session' => $this->_sessionId,
                'module_name' => 'Opportunities',
                'module_id' => $id,
                'link_field_name' => 'contacts',
                'related_module_query' => '',
                'related_fields' => array('id'),
                'related_module_link_name_to_fields_array' => array(array('name' =>  'contacts', 'value' => array('id', 'first_name', 'last_name'))),
            	'deleted'=>0,
				)
			);
		return $result;
    } // fn

    public function _searchByModule() {
		$result = $this->_soapClient->call('search_by_module',
				array(
                'session' => $this->_sessionId,
                'search_string' => 'Sugar',
				'modules' => array('Accounts', 'Contacts', 'Opportunities'),
                'offset' => '0',
                'max_results' => '10')
            );

		return $result;
    } // fn
}
