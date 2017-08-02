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

require_once('modules/ACLFields/actiondefs.php');

/**
 * Bug #61373
 * QuickSearch doesn't have field level ACL checks
 *
 * @ticket 61373
 */
class Bug61373Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var ACLRoles
     */
    protected $role = null;

    /**
     * @var SugarBean
     */
    protected $bean = null;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->role = BeanFactory::newBean('ACLRoles');
        $this->role->name = 'bug61373role';
        $this->role->description = 'Temp Role';
        $this->role->save();

        $this->role->load_relationship('users');
        $this->role->users->add($GLOBALS['current_user']);
    }

    public function tearDown()
    {
        $this->role->mark_deleted($this->role->id);

        // Remove all of the modules from data provider
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for testQuickSearchACLFields()
     */
    public function dataProvider() {
        return array(
            'contacts-email-owner' => array(
                'Contacts',
                'email1',
                ACL_OWNER_READ_WRITE,
                'test1@test.com',
                '',
                array(
                    'SugarTestContactUtilities',
                    'createContact'
                )
            ),
            'accounts-email-owner' => array(
                'Accounts',
                'email1',
                ACL_OWNER_READ_WRITE,
                'test1@test.com',
                '',
                array(
                    'SugarTestAccountUtilities',
                    'createAccount'
                )
            ),
            'leads-email-owner' => array(
                'Leads',
                'email1',
                ACL_OWNER_READ_WRITE,
                'test1@test.com',
                '',
                array(
                    'SugarTestLeadUtilities',
                    'createLead'
                )
            ),
            'contacts-email-default' => array(
                'Contacts',
                'email1',
                ACL_FIELD_DEFAULT,
                'test1@test.com',
                'test1@test.com',
                array(
                    'SugarTestContactUtilities',
                    'createContact'
                )
            ),
            'accounts-email-default' => array(
                'Accounts',
                'email1',
                ACL_FIELD_DEFAULT,
                'test1@test.com',
                'test1@test.com',
                array(
                    'SugarTestAccountUtilities',
                    'createAccount'
                )
            ),
            'leads-email-default' => array(
                'Leads',
                'email1',
                ACL_FIELD_DEFAULT,
                'test1@test.com',
                'test1@test.com',
                array(
                    'SugarTestLeadUtilities',
                    'createLead'
                )
            ),
            'contacts-description-owner' => array(
                'Contacts',
                'description',
                ACL_OWNER_READ_WRITE,
                'Test Desc',
                '',
                array(
                    'SugarTestContactUtilities',
                    'createContact'
                )
            ),
            'accounts-description-owner' => array(
                'Accounts',
                'description',
                ACL_OWNER_READ_WRITE,
                'Test Desc',
                '',
                array(
                    'SugarTestAccountUtilities',
                    'createAccount'
                )
            ),
            'leads-description-owner' => array(
                'Leads',
                'description',
                ACL_OWNER_READ_WRITE,
                'Test Desc',
                '',
                array(
                    'SugarTestLeadUtilities',
                    'createLead'
                )
            ),
            'contacts-description-default' => array(
                'Contacts',
                'description',
                ACL_FIELD_DEFAULT,
                'Test Desc',
                'Test Desc',
                array(
                    'SugarTestContactUtilities',
                    'createContact'
                )
            ),
            'accounts-description-default' => array(
                'Accounts',
                'description',
                ACL_FIELD_DEFAULT,
                'Test Desc',
                'Test Desc',
                array(
                    'SugarTestAccountUtilities',
                    'createAccount'
                )
            ),
            'leads-description-default' => array(
                'Leads',
                'description',
                ACL_FIELD_DEFAULT,
                'Test Desc',
                'Test Desc',
                array(
                    'SugarTestLeadUtilities',
                    'createLead'
                )
            ),
        );
    }

    /**
     * Check if QuickSearch returns fields disabled with ACL
     *
     * @dataProvider dataProvider
     * @group 61373
     */
    public function testQuickSearchACLFields($module, $field, $acl, $value, $expected, $factory)
    {

        // Create bean
        $this->bean = call_user_func($factory);
        // Set create by to some different than current user
        $this->bean->created_by = SugarTestUserUtilities::createAnonymousUser()->id;
        // Set the field we are checking to some value
        $this->bean->$field = $value;
        $this->bean->save();

        // Set ACL for the field
        ACLField::setAccessControl($this->bean->module_name, $this->role->id, $field, $acl);

        // Load the ACLs
        ACLField::loadUserFields(
            $this->bean->module_name,
            $this->bean->object_name,
            $GLOBALS['current_user']->id,
            true
        );

        $quickSearchQuery = new QuickSearchQuery();

        $args = array (
            'method' => 'query',
            'modules' =>
            array (
                0 => $module
            ),
            'group' => 'or',
            'field_list' =>
            array (
                0 => $field
            ),
            'conditions' =>
            array (
                0 =>
                array (
                    'name' => 'name',
                    'op' => 'like_custom',
                    'end' => '%',
                    'value' => $this->bean->name,
                ),
            ),
            'order' => 'name',
            'limit' => '30',
            'no_match_text' => 'No Match',
        );

        // Do a QuickSearch query
        $results = $quickSearchQuery->query($args);

        $json = getJSONobj();
        $results = $json->decode($results);

        $this->assertEquals($expected, $results['fields'][0][$field], "$module->$field should be equal to $value. ACL level $acl");

    }

}
