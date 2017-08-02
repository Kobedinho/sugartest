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
 * RS-29: Prepare OAuthKeys Module
 * Test covers that methods of module don't through any error
 */
class RS29Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testGetByKey()
    {
        $bean = new OAuthKey();
        $actual = $bean->getByKey('');
        $this->assertEmpty($actual);
    }

    public function testFetchKey()
    {
        $actual = OAuthKey::fetchKey('');
        $this->assertEmpty($actual);
    }

    public function testMarkDeleted()
    {
        $bean = new OAuthKey();
        $bean->name = create_guid();
        $bean->save();
        $bean->mark_deleted($bean->id);
        $actual = $bean->retrieve($bean->id);
        $this->assertEmpty($actual);
    }
}
