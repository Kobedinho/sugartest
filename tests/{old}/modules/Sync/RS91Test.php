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

require_once 'modules/Sync/SyncHelper.php';

/**
 * RS-91: Prepare Sync Module.
 */
class RS91Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
    }

    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testSyncHelper()
    {
        $time = TimeDate::getInstance()->nowDb();
        $result = clean_for_sync('Accounts');
        $this->assertEmpty($result);
        $result = clean_relationships_for_sync('Accounts', 'Contacts');
        $this->assertTrue($result);
        $result = get_altered('Accounts', $time, $time);
        $this->assertEmpty($result['entry_list']);
        $result = get_altered_relationships('Accounts', 'Contacts', $time, $time);
        $this->assertEmpty($result['entry_list']);
    }
}
