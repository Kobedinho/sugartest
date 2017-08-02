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

namespace Sugarcrm\SugarcrmTestsUnit\Console\Command\Api;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\Command\Api\SearchFieldsCommand
 *
 */
class SearchFieldsCommandTest extends AbstractApiCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->commandClass = 'Sugarcrm\Sugarcrm\Console\Command\Api\SearchFieldsCommand';
        $this->apiClass = 'AdministrationApi';
        $this->apiMethod = 'searchFields';
    }

    /**
     * {@inheritdoc}
     */
    public function providerTestExecuteCommand()
    {
        // TODO - cleanup different logic in a better way:
        //
        // - module_list
        // - search_only
        // - order_by_boost

        $test0 = array(
            'Accounts' => array(
                'name' => array(
                    'name' => 'name',
                    'type' => 'name',
                    'searchable' => true,
                    'boost' => 1.9099999999999999,
                ),
                'date_entered' => array(
                    'name' => 'date_entered',
                    'type' => 'datetime',
                    'searchable' => false,
                ),
                'description' => array(
                    'name' => 'description',
                    'type' => 'text',
                    'searchable' => true,
                    'boost' => 0.71999999999999997,
                ),
                'phone_fax' => array(
                    'name' => 'phone_fax',
                    'type' => 'phone',
                    'searchable' => true,
                    'boost' => 1.04,
                ),
            ),
            'Contracts' => array(
                'name' => array(
                    'name' => 'name',
                    'type' => 'name',
                    'searchable' => true,
                    'boost' => 1.5900000000000001,
                ),
                'date_entered' => array(
                    'name' => 'date_entered',
                    'type' => 'datetime',
                    'searchable' => false,
                ),
                'modified_user_id' => array(
                    'name' => 'modified_user_id',
                    'type' => 'id',
                    'searchable' => false,
                ),
                'description' => array(
                    'name' => 'description',
                    'type' => 'text',
                    'searchable' => true,
                    'boost' => 0.63,
                ),
                'reference_code' => array(
                    'name' => 'reference_code',
                    'type' => 'varchar',
                    'searchable' => true,
                    'boost' => 0.62,
                ),
            ),
        );

        $test1 = array (
            'Contacts.first_name' => 1.99,
            'Contacts.last_name' => 1.97,
            'Contacts.email' => 1.95,
            'Contacts.portal_name' => 1.9299999999999999,
            'Accounts.name' => 1.9099999999999999,
            'Accounts.email' => 1.8899999999999999,
            'Leads.first_name' => 1.8700000000000001,
            'Leads.last_name' => 1.8500000000000001,
            'Leads.email' => 1.8300000000000001,
        );

        return array(
            array(
                $test0,
                array(),
                'SearchFieldsCommand_0.txt',
                0,
            ),
            array(
                $test1,
                array('--byBoost' => true),
                'SearchFieldsCommand_1.txt',
                0,
            ),
        );
    }
}
