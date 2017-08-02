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
 * @ticket 42683
 */
class Bug42683Test extends SOAPTestCase
{
    public function setUp()
    {
    	$this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v2/soap.php';
		parent::setUp();
    }

    public function tearDown()
    {
        SugarTestLeadUtilities::removeAllCreatedLeads();
        parent::tearDown();
    }

    public function testBadQuery()
    {
        $lead = SugarTestLeadUtilities::createLead();

        $this->_login();
        $result = $this->_soapClient->call(
            'get_entry_list',
            array(
                'session' => $this->_sessionId,
                "module_name" => 'Leads',
                "query" => "leads.id = '{$lead->id}'",
                'order_by' => '',
                'offset' => 0,
                'select_fields' => array(
                    'name'
                ),
                'link_name_to_fields_array' => array(
                    array(
                        'name' => 'email_addresses',
                        'value' => array(
                            'id',
                            'email_address',
                            'opt_out',
                            'primary_address'
                        )
                    )
                ),
                'max_results' => 1,
                'deleted' => 0
            )
        );

        $this->assertEquals('primary_address', $result['relationship_list'][0][0]['records'][0][3]['name']);

    }
}
