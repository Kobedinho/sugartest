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

/**
 * Bug 51723
 *  SOAP::get_entries() call fails to export portal_name field
 * @ticket 51723
 * @author arymarchik@sugarcrm.com
 */
class Bug51723Test extends SOAPTestCase
{
    private $_contact;
    private $_opt = null;

    public function setUp()
    {
        $this->markTestIncomplete("Test breaking on CI, working with dev to fix");
        $administration = new Administration();
        $administration->retrieveSettings();
        if(isset($administration->settings['portal_on']))
        {
            $this->_opt = $administration->settings['portal_on'];
        }
        $administration->saveSetting('portal', 'on',  1);

        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php?wsdl';
        parent::setUp();
        $this->_login();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        $this->_contact = new Contact();
        $this->_contact->last_name = "Contact #bug51723";
        $this->_contact->id = create_guid();
        $this->_contact->new_with_id = true;
        $this->_contact->team_id = 1;
        $this->_contact->save();
    }

    public function tearDown()
    {
        //$this->_contact->mark_deleted($this->_contact->id);
        parent::tearDown();

        $administration = new Administration();
        $administration->retrieveSettings();
        if($this->_opt === null)
        {
            if(isset($administration->settings['portal_on']))
            {
                $administration->saveSetting('portal', 'on', 0);
            }
        }
        else
        {
            $administration->saveSetting('portal', 'on',  $this->_opt);
        }
    }

    /**
     * Testing SOAP method get_entries for existing "portal_name" field
     * @group 51723
     */
    public function testPortalNameInGetEntries()
    {
        $fields = array('portal_name', 'first_name', 'last_name');
        $result = $this->_soapClient->call(
            'get_entries',
            array('session' => $this->_sessionId,
                'module_name' => 'Contacts',
                'ids' => array($this->_contact->id),
                'select_fields' => $fields
            )
        );
        // replacement of $this->assertCount()
        if(count($result['entry_list']) != 1)
        {
            $this->fail('Can\'t get entry list');
        }

        foreach ($result['entry_list'][0]['name_value_list'] as $key => &$value)
        {
            if(($index = array_search($value['name'], $fields, true)) !== false)
            {
                unset($fields[$index]);
            }
            else
            {
                $this->fail('Wrong field in selected fields:' . $value['name']);
            }
        }
        if(count($fields) != 0)
        {
            $this->fail('Can\'t get expected values:' . implode(',', $fields));
        }
    }

}