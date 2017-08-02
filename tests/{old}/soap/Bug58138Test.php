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

require_once 'vendor/nusoap//nusoap.php';
require_once 'modules/DynamicFields/FieldCases.php';

/**
 * Bug #58138
 * Web Service get_relationships doesn't work with related_module_query parameter when using custom fields
 *
 * @author mgusev@sugarcrm.com
 * @ticked 58138
 */
class Bug58138Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var nusoapclient
     */
    protected $soap = null;

    /**
     * @var DynamicField
     */
    protected $dynamicField = null;

    /**
     * @var TemplateText
     */
    protected $field = null;

    /**
     * @var Contact
     */
    protected $module = null;

    /**
     * @var Account
     */
    protected $account = null;

    /**
     * @var Contact
     */
    protected $contact = null;

    /**
     * Creating new field, account, contact with filled custom field, relationship between them
     */
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->field = get_widget('varchar');
        $this->field->id = 'Contactstest_c';
        $this->field->name = 'test_c';
        $this->field->type = 'varchar';
        $this->field->len = 255;
        $this->field->importable = 'true';

        $this->field->label = '';

        $this->module = new Contact();

        $this->dynamicField = new DynamicField('Contacts');

        $this->dynamicField->setup($this->module);
        $this->dynamicField->addFieldObject($this->field);

        SugarTestHelper::setUp('dictionary');
        $GLOBALS['reload_vardefs'] = true;

        $this->account = SugarTestAccountUtilities::createAccount();

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->account_id = $this->account->id;
        $this->contact->test_c = 'test value';
        $this->contact->load_relationship('accounts');
        $this->contact->accounts->add($this->account->id);
        $this->contact->save();

        $GLOBALS['db']->commit();
    }

    /**
     * Removing field, account, contact
     */
    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        $this->dynamicField->deleteField($this->field);

        SugarTestHelper::tearDown();
        $GLOBALS['reload_vardefs'] = false;
    }

    /**
     * Test asserts that contact can be found by custom field
     *
     * @group 58138
     */
    public function testSoap()
    {
        $soap_url = $GLOBALS['sugar_config']['site_url'] . '/soap.php';
        $this->soap = new nusoapclient($soap_url);

        $result = $this->soap->call('login', array(
                'user_auth' => array(
                    'user_name' => $GLOBALS['current_user']->user_name,
                    'password' => $GLOBALS['current_user']->user_hash,
                    'version' => '.01'
                ),
                'application_name' => 'SoapTest'
            )
        );

        $actual = $this->soap->call('get_relationships', array(
            'session' => $result['id'],
            'module_name' => 'Accounts',
            'module_id' => $this->account->id,
            'link_field_name' => 'Contacts',
            'related_module_query' => "contacts_cstm.test_c = 'test value' ",
            'deleted' => '1',
        ));

        $this->assertInternalType('array', $actual, 'Incorrect response');

        if (empty($actual['ids']))
        {
            $this->fail('Data is not present');
        }

        $actual = reset($actual['ids']);
        $this->assertEquals($this->contact->id, $actual['id'], 'Contact is incorrect');
    }

    public static function dataProvider()
    {
        return array(
            array('/service/v2/soap.php'),
            array('/service/v2_1/soap.php'),
            array('/service/v3/soap.php'),
            array('/service/v3_1/soap.php'),
            array('/service/v4/soap.php'),
            array('/service/v4_1/soap.php')
        );
    }

    /**
     * Test asserts that contact can be found by custom field
     *
     * @group 58138
     * @dataProvider dataProvider
     */
    public function testSoapVersions($url)
    {
        $soap_url = $GLOBALS['sugar_config']['site_url'] . $url;
        $this->soap = new nusoapclient($soap_url);

        $result = $this->soap->call('login', array(
            'user_auth' => array(
                'user_name' => $GLOBALS['current_user']->user_name,
                'password' => $GLOBALS['current_user']->user_hash,
                'version' => '.01'
            ),
            'application_name' => 'SoapTest'
            )
        );

        $actual = $this->soap->call('get_relationships', array(
            'session' => $result['id'],
            'module_name' => 'Accounts',
            'module_id' => $this->account->id,
            'link_field_name' => 'contacts',
            'related_module_query' => "contacts_cstm.test_c = 'test value' ",
            'link_module_fields' => array('id'),
            'deleted' => '1',
        ));

        $this->assertInternalType('array', $actual, 'Incorrect response');

        if (empty($actual['entry_list']))
        {
            $this->fail('Data is not present');
        }

        $actual = reset($actual['entry_list']);
        $this->assertEquals($this->contact->id, $actual['id'], 'Contact is incorrect');
    }
}
