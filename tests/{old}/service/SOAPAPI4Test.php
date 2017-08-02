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
require_once('tests/{old}/service/APIv3Helper.php');


class SOAPAPI4Test extends SOAPTestCase
{
    private static $helperObject;
    private $cleanup;

    /**
     * Create test user
     *
     */
	public function setUp()
    {
    	$this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v4/soap.php';
		parent::setUp();
		self::$helperObject = new APIv3Helper();
        $this->_login();
        $this->cleanup = false;
    }

    public function tearDown()
    {
        if(!empty($this->cleanup)) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE name like 'UNIT TEST%' ");
            $GLOBALS['db']->query("DELETE FROM opportunities WHERE name like 'UNIT TEST%' ");
            $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name like 'UNIT TEST%' ");
        }
        parent::tearDown();
    }

    public function testGetEntryList()
    {
        $contact = SugarTestContactUtilities::createContact();

        $result = $this->_soapClient->call(
            'get_entry_list',
            array(
                'session' => $this->_sessionId,
                'module_name' => 'Contacts',
                'query' => "contacts.id = '{$contact->id}'",
                'order_by' => '',
                'offset' => 0,
                'select_fields' => array('last_name', 'first_name', 'do_not_call', 'lead_source', 'email1'),
                'link_name_to_fields_array' => array(array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address'))),
                'max_results' => 1,
                'deleted' => 0,
                'favorites' => false,
                )
            );

        $this->assertEquals(
            $contact->email1,
            $result['relationship_list'][0]['link_list'][0]['records'][0]['link_value'][1]['value']
            );
    }


    public function testGetEntryListWithFavorites()
    {
        $contact = SugarTestContactUtilities::createContact();
        $sf = new SugarFavorites();
        $sf->id = SugarFavorites::generateGUID('Contacts',$contact->id);
        $sf->module = 'Contacts';
        $sf->record_id = $contact->id;
        $sf->save(FALSE);
        $GLOBALS['db']->commit();
        $this->assertTrue(SugarFavorites::isUserFavorite('Contacts',$contact->id),"The contact wasn't correctly marked as a favorite.");

        $result = $this->_soapClient->call(
            'get_entry_list',
            array(
                'session' => $this->_sessionId,
                'module_name' => 'Contacts',
                'query' => "contacts.id = '{$contact->id}'",
                'order_by' => '',
                'offset' => 0,
                'select_fields' => array('last_name', 'first_name', 'do_not_call', 'lead_source', 'email1'),
                'link_name_to_fields_array' => array(array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address'))),
                'max_results' => 1,
                'deleted' => 0,
                'favorites' => true,
                )
            );

        $this->assertEquals(
            $contact->email1,
            $result['relationship_list'][0]['link_list'][0]['records'][0]['link_value'][1]['value']
            );
    }


    public function testSearchByModule()
    {

        $seedData = self::$helperObject->populateSeedDataForSearchTest($GLOBALS['current_user']->id);
        $this->cleanup = true;
        $returnFields = array('name','id','deleted');
        $searchModules = array('Accounts','Contacts','Opportunities');
        $searchString = "UNIT TEST";
        $offSet = 0;
        $maxResults = 10;

        $results = $this->_soapClient->call('search_by_module',
                        array(
                            'session' => $this->_sessionId,
                            'search'  => $searchString,
                            'modules' => $searchModules,
                            'offset'  => $offSet,
                            'max'     => $maxResults,
                            'user'    => $GLOBALS['current_user']->id,
                            'fields'  => $returnFields,
                            'unified_only' => TRUE,
                            'favorites' => FALSE)
                        );
        $this->assertEquals($seedData[0]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[0]['id'],'Accounts', $seedData[0]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[1]['id'],'Accounts', $seedData[1]['fieldName']));
        $this->assertEquals($seedData[2]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[2]['id'],'Contacts', $seedData[2]['fieldName']));
        $this->assertEquals($seedData[3]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[3]['id'],'Opportunities', $seedData[3]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[4]['id'],'Opportunities', $seedData[4]['fieldName']));
    }

    public function testSearchByModuleWithFavorites()
    {

        $seedData = self::$helperObject->populateSeedDataForSearchTest($GLOBALS['current_user']->id);
        $this->cleanup = true;
        $sf = new SugarFavorites();
        $sf->module = 'Accounts';
        $sf->record_id = $seedData[0]['id'];
        $sf->save(FALSE);

        $sf = new SugarFavorites();
        $sf->module = 'Contacts';
        $sf->record_id = $seedData[2]['id'];
        $sf->save(FALSE);

        $GLOBALS['db']->commit();

        $returnFields = array('name','id','deleted');
        $searchModules = array('Accounts','Contacts','Opportunities');
        $searchString = "UNIT TEST";
        $offSet = 0;
        $maxResults = 10;

        $results = $this->_soapClient->call('search_by_module',
                        array(
                            'session' => $this->_sessionId,
                            'search'  => $searchString,
                            'modules' => $searchModules,
                            'offset'  => $offSet,
                            'max'     => $maxResults,
                            'user'    => $GLOBALS['current_user']->id,
                            'fields'  => $returnFields,
                            'unified_only' => TRUE,
                            'favorites' => TRUE)
                        );
        $this->assertEquals($seedData[0]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[0]['id'],'Accounts', $seedData[0]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[1]['id'],'Accounts', $seedData[1]['fieldName']));
        $this->assertEquals($seedData[2]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[2]['id'],'Contacts', $seedData[2]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[3]['id'],'Opportunities', $seedData[3]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[4]['id'],'Opportunities', $seedData[4]['fieldName']));
    }


    public function testGetEntries()
    {
        $contact = SugarTestContactUtilities::createContact();

        $this->_login();
        $result = $this->_soapClient->call(
            'get_entries',
            array(
                'session' => $this->_sessionId,
                'module_name' => 'Contacts',
                'ids' => array($contact->id),
                'select_fields' => array('last_name', 'first_name', 'do_not_call', 'lead_source', 'email1'),
                'link_name_to_fields_array' => array(array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address'))),
                )
            );

        $this->assertEquals(
            $contact->email1,
            $result['relationship_list'][0]['link_list'][0]['records'][0]['link_value'][1]['value']
            );
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
        $this->assertArrayHasKey("favorite_enabled", $actual);

        $soap_data = array('session' => $this->_sessionId, 'filter' => 'all');

        $result = $this->_soapClient->call('get_available_modules', $soap_data);
        $actual = $result['modules'][0];
        $this->assertArrayHasKey("module_key", $actual);
        $this->assertArrayHasKey("module_label", $actual);
        $this->assertArrayHasKey("acls", $actual);
        $this->assertArrayHasKey("favorite_enabled", $actual);
    }

    /**
     * Test get avaiable modules call
     *
     */
    function testGetAvailableModules()
    {
        $soap_data = array('session' => $this->_sessionId,'filter' => 'mobile');
        $result = $this->_soapClient->call('get_available_modules', $soap_data);

        foreach ( $result['modules'] as $tmpModEntry)
        {
            $tmpModEntry['module_key'];
            $this->assertTrue( isset($tmpModEntry['acls']) );
            $this->assertTrue( isset($tmpModEntry['module_key']) );


            $mod = BeanFactory::newBean($tmpModEntry['module_key']);
            $this->assertEquals( $mod->isFavoritesEnabled(), $tmpModEntry['favorite_enabled']);
        }
    }

}
