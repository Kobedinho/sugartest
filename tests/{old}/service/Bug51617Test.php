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
require_once 'modules/DynamicFields/FieldCases.php';

class Bug51617Test extends SOAPTestCase
{
    protected $_account;

    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v2/soap.php';

        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, 1));

        $this->field = get_widget('varchar');
        $this->field->id = 'Accountstest_custom_c';
        $this->field->name = 'test_custom_c';
        $this->field->vname = 'LBL_TEST_CUSTOM_C';
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
        $GLOBALS['db']->commit();
        VardefManager::clearVardef();
        VardefManager::refreshVardefs('Accounts', 'Account');
        $this->mod->field_defs = $GLOBALS['dictionary']['Account']['fields'];

        $this->_account = SugarTestAccountUtilities::createAccount();

        $this->_account->test_custom_c = 'Custom Field';
        $this->_account->team_set_id = '1';
        $this->_account->team_id = '1';
        $this->_account->save();

        $GLOBALS['db']->commit(); // Making sure we commit any changes

        parent::setUp();
    }

    public function tearDown()
    {
        $this->df->deleteField($this->field);
        if ($GLOBALS['db']->tableExists('accounts_cstm')) {
            $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c = '{$this->_account->id}'");
        }

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();

        parent::tearDown();
        $GLOBALS['db']->commit();

        global $soap_version_test_accountId, $soap_version_test_opportunityId, $soap_version_test_contactId;
        unset($soap_version_test_accountId);
        unset($soap_version_test_opportunityId);
        unset($soap_version_test_contactId);
    }

    /**
     * 
     */
    public function testGetEntryListWithCustomField()
    {
        $this->_login();
        $GLOBALS['db']->commit();
        $result = $this->_soapClient->call('get_entry_list',
            array(
                 'session'=>$this->_sessionId,
                 "module_name" => 'Accounts',
                 "accounts.id = '{$this->_account->id}'",
                 '',
                 0,
                 "select_fields" => array('id', 'name', 'test_custom_c'),
                 null,
                 'max_results' => 1
            )
        );

        $this->assertTrue($result['result_count'] > 0,
            'Get_entry_list failed: Fault code: '.$this->_soapClient->faultcode.', fault string:'.$this->_soapClient->faultstring.', fault detail: '.$this->_soapClient->faultdetail);

        $row = array();
        $row = $result['entry_list'][0]['name_value_list'];

        // find the custom field
        if (!empty($row))
        {
            foreach($row as $r) {
                // just make sure they are all not empty
                $this->assertNotEmpty($r['value'],"Value is empty, looks like: ".var_export($r,true));
                // make sure that the test field has our value in it
                if($r['name'] == "test_custom_c") {
                    $this->assertEquals("Custom Field", $r['value'],"Custom field does not have our value in it");
                }
            }
        } // if

    } // fn
}
