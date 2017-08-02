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

use Sugarcrm\Sugarcrm\Security\InputValidation\InputValidation;


/**
 * Bug 51423:
 *  Related data is not properly populated in Reports
 * @ticket 51423
 * @author arymarchik@sugarcrm.com
 */
class Bug51423Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var array Request for creating/deleting related field for Accounts module
     */
    private $_req = array (
        'to_pdf' => 'true',
        'sugar_body_only' => '1',
        'module' => 'ModuleBuilder',
        'new_dropdown' => '',
        'view_module' => 'Accounts',
        'is_update' => 'true',
        'type' => 'relate',
        'name' => 'relate_contacts',
        'labelValue' => 'relate contacts',
        'label' => 'LBL_RELATE_CONTACTS',
        'help' => '',
        'comments' => '',
        'ext2' => 'Contacts',
        'ext3' => '',
        'dependency' => '',
        'dependency_display' => '',
        'reportableCheckbox' => '1',
        'reportable' => '1',
        'importable' => 'true',
        'duplicate_merge' => '0',
    );

    private $_account_1;

    private $_account_2;

    private $_contact_1;

    private $_contact_2;

    private $_report;

    private $_user;

    /**
     * @var bool
     */
    protected $origin_isCacheReset;

    public function setUp()
    {
        $this->origin_isCacheReset = SugarCache::$isCacheReset;
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("app_strings");
        SugarTestHelper::setUp("app_list_strings");

        $this->_user = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $GLOBALS['current_user'] = $this->_user;

        $this->_req['action'] = 'saveField';
        $request = InputValidation::create($this->_req, array());
        $mb = new ModuleBuilderController($request);
        $mb->action_saveField();

        $this->_contact_1 = SugarTestContactUtilities::createContact();
        $this->_contact_1->last_name = 'Contact #1';
        $this->_contact_1->team_id = 1;
        $this->_contact_1->save();

        $this->_contact_2 = SugarTestContactUtilities::createContact();
        $this->_contact_2->last_name = 'Contact #2';
        $this->_contact_2->team_id = 1;
        $this->_contact_2->save();

        $this->_account_1 = SugarTestAccountUtilities::createAccount();
        $this->_account_1->name = 'Account #1';
        $this->_account_1->contact_id_c = $this->_contact_1->id;
        $this->_account_1->team_id = 1;
        $this->_account_1->relate_contacts_c = $this->_contact_1->last_name;
        $this->_account_1->save();

        $this->_account_2 = SugarTestAccountUtilities::createAccount();
        $this->_account_2->name = 'Account #2';
        $this->_account_2->contact_id_c = $this->_contact_2->id;
        $this->_account_2->relate_contacts_c = $this->_contact_2->last_name;
        $this->_account_2->parent_id = $this->_account_1->id;
        $this->_account_2->team_id = 1;
        $this->_account_2->save();
    }

    public function tearDown()
    {
        $this->_req['action'] = 'DeleteField';
        $this->_req['name'] = 'relate_contacts_c';
        $request = InputValidation::create($this->_req, array());
        $mb = new ModuleBuilderController($request);
        $mb->action_DeleteField();

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarCache::$isCacheReset = $this->origin_isCacheReset;
        SugarTestHelper::tearDown();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    /**
     * Testing related fields in the report
     * @group 51423
     */
    public function testReportsRelatedField()
    {
        /**
         * Report defs for generating the report
         */
        $rep_defs =array (
            'display_columns' =>
            array (
                0 =>
                array (
                    'name' => 'name',
                    'label' => 'Name',
                    'table_key' => 'self',
                ),
                1 =>
                array (
                    'name' => 'relate_contacts_c',
                    'label' => 'relate contacts',
                    'table_key' => 'self',
                ),
                2 =>
                array (
                    'name' => 'name',
                    'label' => 'Name1',
                    'table_key' => 'Accounts:member_of',
                ),
                3 =>
                array (
                    'name' => 'relate_contacts_c',
                    'label' => 'relate contacts1',
                    'table_key' => 'Accounts:member_of',
                ),
            ),
            'module' => 'Accounts',
            'group_defs' =>
            array (
            ),
            'summary_columns' =>
            array (
            ),
            'report_name' => 'report #1',
            'chart_type' => 'none',
            'do_round' => 1,
            'numerical_chart_column' => '',
            'numerical_chart_column_type' => '',
            'assigned_user_id' => '1',
            'report_type' => 'tabular',
            'full_table_list' =>
            array (
                'self' =>
                array (
                    'value' => 'Accounts',
                    'module' => 'Accounts',
                    'label' => 'Accounts',
                ),
                'Accounts:member_of' =>
                array (
                    'name' => 'Accounts  >  Member of',
                    'parent' => 'self',
                    'link_def' =>
                    array (
                        'name' => 'member_of',
                        'relationship_name' => 'member_accounts',
                        'bean_is_lhs' => false,
                        'link_type' => 'one',
                        'label' => 'Member of',
                        'module' => 'Accounts',
                        'table_key' => 'Accounts:member_of',
                    ),
                    'dependents' =>
                    array (
                        0 => 'display_cols_row_3',
                        1 => 'display_cols_row_4',
                        2 => 'display_cols_row_3',
                        3 => 'display_cols_row_4',
                    ),
                    'module' => 'Accounts',
                    'label' => 'Member of',
                    'optional' => true,
                ),
            ),
            'filters_def' =>
            array (
                'Filter_1' =>
                array (
                    'operator' => 'AND',
                    0 =>
                    array (
                        'name' => 'name',
                        'table_key' => 'self',
                        'qualifier_name' => 'is'
                    ),
                ),
            ),
        );
        $rep_defs['filters_def']['Filter_1']['0']['input_name0'] = $this->_account_2->id;
        $rep_defs['filters_def']['Filter_1']['0']['input_name1'] = $this->_account_2->name;
        $json = getJSONobj();
        $tmp = $json->encode($rep_defs);
        $this->_report = new Report($tmp);
        $this->_report->run_query();
        while (( $row = $this->_report->get_next_row() ) != 0 ) {
            $this->assertRegExp('/.*' . preg_quote($this->_account_2->name) . '.*/', $row['cells']['0']);
            $this->assertRegExp('/.*' . preg_quote($this->_contact_2->last_name) . '.*/', $row['cells']['1']);
            $this->assertRegExp('/.*' . preg_quote($this->_account_1->name) . '.*/', $row['cells']['2']);
            $this->assertRegExp('/.*' . preg_quote($this->_contact_1->last_name) . '.*/', $row['cells']['3']);
        }
    }
}
