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
 * @coversDefaultClass RelateRecordApi
 */
class RelateRecordApiUpdateTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $user1;
    private $user2;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('current_user');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->user1 = SugarTestUserUtilities::createAnonymousUser();
        $this->user2 = SugarTestUserUtilities::createAnonymousUser();
    }

    /**
     * @coversDefaultClass ::updateRelatedLink
     */
    public function testRelateFieldUpdated()
    {
        $contact = SugarTestContactUtilities::createContact();
        $contact->assigned_user_id = $this->user1->id;
        $contact->save();

        $contact->retrieve($contact->id);
        $this->assertEquals($contact->assigned_user_name, $this->user1->name);

        $account = SugarTestAccountUtilities::createAccount();
        $account->load_relationship('contacts');
        $account->contacts->add($contact);

        $api = new RelateRecordApi();
        $service = SugarTestRestUtilities::getRestServiceMock();
        $response = $api->updateRelatedLink($service, array(
            'module' => $account->module_name,
            'record' => $account->id,
            'link_name' => 'contacts',
            'remote_id' => $contact->id,
            'assigned_user_id' => $this->user2->id,
        ));

        $this->assertEquals($this->user2->name, $response['related_record']['assigned_user_name']);
    }
}
