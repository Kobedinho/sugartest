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
 * @covers RelateRecordApi
 */
class RelateRecordApiUnrelatedRecordTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**#@+
     * @var Account
     */
    private static $account1;
    private static $account2;
    /**#@-*/

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('current_user');
        self::$account1 = SugarTestAccountUtilities::createAccount();
        self::$account2 = SugarTestAccountUtilities::createAccount();
    }

    public static function tearDownAfterClass()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        parent::tearDownAfterClass();
    }

    public function testCreateUnrelatedRecord()
    {
        $service = SugarTestRestUtilities::getRestServiceMock();
        $api = new RelateRecordApi();

        $result = $api->createRelatedRecord($service, array(
            'module' => 'Accounts',
            'record' => self::$account1->id,
            'link_name' => 'contacts',
            'account_id' => self::$account2->id,
        ));

        SugarTestContactUtilities::setCreatedContact(array($result['related_record']['id']));
        $this->assertEquals(self::$account2->id, $result['related_record']['account_id']);
    }
}
