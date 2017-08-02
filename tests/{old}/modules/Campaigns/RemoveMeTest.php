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

class RemoveMeTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var Campaign */
    private $campaign;

    protected function setUp()
    {
        SugarTestHelper::setUp('mod_strings', array('Campaigns'));
        $this->campaign = SugarTestCampaignUtilities::createCampaign();
    }

    protected function tearDown()
    {
        SugarTestCampaignUtilities::removeAllCreatedCampaignLogs();
        SugarTestCampaignUtilities::removeAllCreatedCampaigns();
        SugarTestContactUtilities::removeAllCreatedContacts();

        parent::tearDown();
    }

    public function testRemoveContact()
    {
        global $mod_strings;

        $contact = SugarTestContactUtilities::createContact();
        $log = SugarTestCampaignUtilities::createCampaignLog(
            $this->campaign->id,
            'targeted',
            $contact
        );

        $trackerKey = create_guid();
        $log->target_tracker_key = $trackerKey;
        $log->save();

        $this->expectOutputString('*' . $mod_strings['LBL_ELECTED_TO_OPTOUT']);

        $_REQUEST['identifier'] = $trackerKey;
        require 'modules/Campaigns/RemoveMe.php';

        $emails = $contact->emailAddress->getAddressesForBean($contact, true);
        $this->assertNotEmpty($emails);

        foreach ($emails as $email) {
            $this->assertEquals(1, $email['opt_out']);
        }
    }
}
