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
 * Bug #60688
 * Role that sets email to owner read/owner write still allows non-admin user to email the contact or see email address.
 *
 * @ticket 60688
 */
class Bug60688Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var ACLRoles
     */
    protected $role = null;

    /**
     * @var Email
     */
    protected $email = null;

    /**
     * @var Account
     */
    protected $contact = null;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true));
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->email = BeanFactory::newBean('Emails');

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->email2 = 'bug60688role@example.com';
        $this->contact->created_by = SugarTestUserUtilities::createAnonymousUser()->id;
        $this->contact->save();

        $this->role = BeanFactory::newBean('ACLRoles');
        $this->role->name = 'bug60688role';
        $this->role->description = 'Temp role.';
        $this->role->save();

        $this->role->load_relationship('users');
        $this->role->users->add($GLOBALS['current_user']);
    }

    public function tearDown()
    {
        $this->role->mark_deleted($this->role->id);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    /**
     * @group 60688
     */
    public function testEmptyEmailLinkForDisabledAccess()
    {
        $this->markTestIncomplete('Skipping as this is not needed for Sugar7. Should be removed on SC-1312');
        // Primary email address for Contact module
        ACLField::setAccessControl($this->contact->module_name, $this->role->id, 'email1', ACL_OWNER_READ_WRITE);
        // Alternative email address for Contact module
        ACLField::setAccessControl($this->contact->module_name, $this->role->id, 'email2', ACL_OWNER_READ_WRITE);

        ACLField::loadUserFields(
            $this->contact->module_name,
            $this->contact->object_name,
            $GLOBALS['current_user']->id,
            true
        );

        $actualEmailLink = $this->email->getNamePlusEmailAddressesForCompose(
            $this->contact->module_name,
            array($this->contact->id)
        );

        $this->assertEmpty($actualEmailLink, 'E-mail should be empty. We disabled both primary and secondary e-mails with ACL.');
    }

    /**
     * @group 60688
     */
    public function testAlternativeEmailLinkWhenPrimaryDisabled()
    {
        $this->markTestIncomplete('Skipping as this is not needed for Sugar7. Should be removed on SC-1312');
        // Primary email address for Contact module
        ACLField::setAccessControl($this->contact->module_name, $this->role->id, 'email1', ACL_OWNER_READ_WRITE);

        ACLField::loadUserFields(
            $this->contact->module_name,
            $this->contact->object_name,
            $GLOBALS['current_user']->id,
            true
        );

        $actualEmailLink = $this->email->getNamePlusEmailAddressesForCompose(
            $this->contact->module_name,
            array($this->contact->id)
        );

        $this->assertContains($this->contact->email2, $actualEmailLink, 'Should get secondary e-mail. Primary was disabled with ACL.');
    }
}
