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
 * @ticket 56938
 */
class Bug56938Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var User */
    private $user, $duplicate;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        SugarTestEmailAddressUtilities::addAddressToPerson(
            $this->user,
            'bug-56938-test@example.com'
        );
    }

    /**
     * Ensure that a new instance of EmailAddress is created during creating
     * User duplicate
     */
    public function testCreateDuplicate()
    {
        // retrieve created user from database in order to populate email addresses
        $original = new User();
        $original->retrieve($this->user->id);

        // simulate request parameters of "Duplicate" web form
        $address = $original->emailAddress->addresses[0];
        $_REQUEST = array(
            'Users_email_widget_id' => '1',
            'Users1emailAddress0'   => $address['email_address'],
            'Users1emailAddressId0' => $address['email_address_id'],
        );

        // create a duplicate and retrieve it from database as well
        $duplicate = $this->duplicate = new User();
        $duplicate->save();

        $retrieved = new User();
        $retrieved->retrieve($duplicate->id);

        // ensure that email address is created in duplicate
        $this->assertEquals(1, count($retrieved->emailAddress->addresses));

        // ensure that it's value is the same as original email address
        $this->assertEquals(
            $original->emailAddress->addresses[0]['email_address'],
            $retrieved->emailAddress->addresses[0]['email_address']
        );

        // ensure that new instance of EmailAddress is created instead of
        // sharing the same instance between users
        $this->assertNotEquals(
            $original->emailAddress->addresses[0]['email_address_id'],
            $retrieved->emailAddress->addresses[0]['email_address_id']
        );
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $_REQUEST = array();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        if ($this->duplicate)
        {
            $this->duplicate->mark_deleted($this->duplicate->id);
        }
    }
}
