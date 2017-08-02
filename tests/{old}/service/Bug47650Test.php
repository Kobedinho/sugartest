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
class Bug47650Test extends SOAPTestCase
{
    public $_contactId = '';

    /**
     * Create test user
     *
     */
	public function setUp()
    {
    	$this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v2/soap.php';
        SugarTestAccountUtilities::createAccount();
        SugarTestAccountUtilities::createAccount();
		parent::setUp();
    }

    /**
     * Remove anything that was used during this test
     *
     */
    public function tearDown()
    {
		parent::tearDown();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    	global $soap_version_test_accountId, $soap_version_test_opportunityId, $soap_version_test_contactId;
        unset($soap_version_test_accountId);
        unset($soap_version_test_opportunityId);
        unset($soap_version_test_contactId);
    }

    public function testGetEntryListWithFourFieldsFields()
    {
    	$this->_login();
		$result = $this->_soapClient->call('get_entry_list',
            array(
                 'session'=>$this->_sessionId,
                 "module_name" => 'Accounts',
                 '',
                 '',
                 0,
                 "select_fields" => array('id', 'name', 'account_type', 'industry'),
                 null, 
                 'max_results' => 1
            )
        );

        $this->assertEquals(4, count($result['entry_list'][0]['name_value_list']), 'More than four fields were returned');
    } // fn
}
