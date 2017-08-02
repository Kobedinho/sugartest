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
 * @ticket 64166
 */
class Bug64166Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Contact
     */
    private $contact;

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        $this->contact = SugarTestContactUtilities::createContact();
    }

    protected function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testEmptyRelateFieldIsRegistered()
    {
        $contact = BeanFactory::newBean('Contacts');
        $contact->retrieve($this->contact->id);
        $this->assertEmpty($contact->account_name);
        $this->assertArrayHasKey('account_name', $contact->fetched_rel_row);
    }
}
