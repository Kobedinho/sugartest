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

require_once('tests/{old}/service/SOAPTestCase.php');

/**
 * Bug #41392
 * Wildcard % searching does not return email addresses when searching with outlook plugin
 *
 * @author mgusev@sugarcrm.com
 * @ticket 41392
 */
class Bug41392Test extends SOAPTestCase
{
    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        parent::setUp();
    }

    /**
     * Test creates new account and tries to find the account by wildcard of its email
     *
     * @group 41392
     */
    public function testSearchByModule()
    {
        $user = new User();
        $user->retrieve(1);

        $account = new Account();
        $account->name = 'Bug4192Test';
        $account->email1 = 'Bug4192Test@example.com';
        $account->save();
        $GLOBALS['db']->commit();

        $params = array(
            'user_name' => $user->user_name,
            'password' => $user->user_hash,
            'search_string' => '%@example.com',
            'modules' => array(
                'Accounts'
            ),
            'offset' => 0,
            'max_results' => 30
        );

        $actual = $this->_soapClient->call('search_by_module', $params);
        $account->mark_deleted($account->id);

        $this->assertGreaterThan(0, $actual['result_count'], 'Call must return one bean minimum');
        $this->assertEquals('Accounts', $actual['entry_list'][0]['module_name'], 'Bean must be account');
        $this->assertEquals($account->id, $actual['entry_list'][0]['id'], 'Bean id must be same as id of created account');
    }
}
