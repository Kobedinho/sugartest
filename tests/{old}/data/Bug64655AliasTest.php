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

/**
 * @ticket 64655
 */
class Bug64655AliasTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Account
     */
    private $account;

    /**
     * 28 characters is the maximum allowed custom field name length
     *
     * @var string
     */
    private static $fieldName = 'bug64655_abcdefghijklmnopqrs';

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // this test will fail in developer mode because VarDefManager will
        // rebuild User's vardefs
        unset($_SESSION['developerMode']);

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, true));

        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('custom_field', array(
            'Users',
            array(
                'name' => self::$fieldName,
                'type' => 'varchar',
            ),
        ));

        // add custom field to the name format map under "x" alias
        $GLOBALS['dictionary']['User']['name_format_map'] = array(
            'x' => self::$fieldName . '_c',
        );
    }

    protected function setUp()
    {
        parent::setUp();

        // create regular user with custom field populated
        $user = $this->user = SugarTestUserUtilities::createAnonymousUser(false, 0);
        $user->{self::$fieldName . '_c'} = 'Custom Value';
        $user->save();

        // create account assigned to the user
        $account = $this->account = SugarTestAccountUtilities::createAccount();
        $account->assigned_user_id = $user->id;
        $account->save();
    }

    protected function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();

        parent::tearDownAfterClass();
    }

    public function testLongRelateAlias()
    {
        /** @var User */
        global $current_user;
        $current_user->setPreference('default_locale_name_format', 'x');

        $account = BeanFactory::retrieveBean('Accounts', $this->account->id, array(
            'use_cache' => false,
        ));
        $this->assertNotEmpty($account);

        // formatted assigned user name must contain the value of custom field
        $this->assertContains('Custom Value', $account->assigned_user_name);
    }
}
