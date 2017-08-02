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
 
require_once 'modules/DynamicFields/FieldCases.php';
require_once 'service/v4/SugarWebServiceImplv4.php';

class Bug41985Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_contact;
    protected $_account;

    public function setUp()
    {
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("current_user");
        $GLOBALS['current_user']->status = 'Active';
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['current_user']->save();

        $this->field = get_widget('varchar');
        $this->field->id = 'Accountstest_custom_c';
        $this->field->name = 'test_custom_c';
        $this->field->vanme = 'LBL_TEST_CUSTOM_C';
        $this->field->comments = NULL;
        $this->field->help = NULL;
        $this->field->custom_module = 'Accounts';
        $this->field->type = 'varchar';
        $this->field->label = 'LBL_TEST_CUSTOM_C';
        $this->field->len = 255;
        $this->field->required = 0;
        $this->field->default_value = NULL;
        $this->field->date_modified = '2009-09-14 02:23:23';
        $this->field->deleted = 0;
        $this->field->audited = 0;
        $this->field->massupdate = 0;
        $this->field->duplicate_merge = 0;
        $this->field->reportable = 1;
        $this->field->importable = 'true';
        $this->field->ext1 = NULL;
        $this->field->ext2 = NULL;
        $this->field->ext3 = NULL;
        $this->field->ext4 = NULL;

        $this->df = new DynamicField('Accounts');
        $this->mod = new Account();
        $this->df->setup($this->mod);
        $this->df->addFieldObject($this->field);
        $this->df->buildCache('Accounts');
        VardefManager::clearVardef();
        VardefManager::refreshVardefs('Accounts', 'Account');
        $this->mod->field_defs = $GLOBALS['dictionary']['Account']['fields'];

        $this->_contact = SugarTestContactUtilities::createContact();
        $this->_account = SugarTestAccountUtilities::createAccount();

        $this->_contact->load_relationship('accounts');
        $this->_contact->accounts->add($this->_account->id);

        $this->_account->test_custom_c = 'Custom Field';
        $this->_account->save();

        $GLOBALS['db']->commit(); // Making sure we commit any changes
    }

    public function tearDown()
    {
        $this->df->deleteField($this->field);

        $account_ids = SugarTestAccountUtilities::getCreatedAccountIds();
        $contact_ids = SugarTestContactUtilities::getCreatedContactIds();
        $GLOBALS['db']->query('DELETE FROM accounts_contacts WHERE contact_id IN (\'' . implode("', '", $contact_ids) . '\') OR  account_id IN (\'' . implode("', '", $account_ids) . '\')');

        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        SugarTestHelper::tearDown();
    }

    public function testGetRelationshipsWithCustomFields()
    {
        $web_service_util = new SugarWebServiceUtilv4();

        $result = $web_service_util->getRelationshipResults($this->_contact, 'accounts', array('id', 'name', 'test_custom_c'));

        $this->assertTrue(isset($result['rows'][0]));
        $this->assertTrue(isset($result['rows'][0]['test_custom_c']));
        $this->assertEquals($result['rows'][0]['test_custom_c'], 'Custom Field');
    }
}
