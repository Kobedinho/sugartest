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

require_once('modules/Campaigns/ProcessBouncedEmails.php');

class Bug12755Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $emailAddress = 'unittest@example.com';
    protected $_user;

    public function setUp()
    {
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $this->_user->emailAddress->addAddress($this->emailAddress, false, false, 0);
        $this->_user->emailAddress->save($this->_user->id, $this->_user->module_dir);
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $query = "DELETE from email_addresses where email_address = '{$this->emailAddress}'";
        $GLOBALS['db']->query($query);
        $query = "DELETE from email_addr_bean_rel where bean_id = '{$this->_user->id}'";
        $GLOBALS['db']->query($query);
    }

    public function testMarkEmailAddressInvalid()
    {
        markEmailAddressInvalid($this->emailAddress);

        $sea = BeanFactory::newBean('EmailAddresses');
        $rs = $sea->retrieve_by_string_fields( array('email_address_caps' => trim(strtoupper($this->emailAddress))) );
        $this->assertTrue( (bool) $rs->invalid_email);
    }

}