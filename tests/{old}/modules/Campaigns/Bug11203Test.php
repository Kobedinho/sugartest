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
 * This test simulates a failure in creating an inbound email from the campaigns 'Email Setup' wizard
 * @ticket 11203
 */
class Bug11203Test extends Sugar_PHPUnit_Framework_TestCase
{
    public $_user = null;

	public function setUp()
    {
        //set the global user to an admin
        global $current_user;
        $this->_user = $current_user;

        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->is_admin = 1;
        $user->save();
        $current_user = $user;


        //build request the way WizardEmailSetupSave.php would
        //Make sure that the credentials will not pass!  We want to test a failed optimums result during inboundEmail save.
        $_REQUEST = array(
              'module' => 'Campaigns',
              'action' =>' WizardEmailSetupSave',
              'mailbox' => 'INBOX',
              'ssl' => '1',
              'email_password' => 'S8bllc',
              'notify_fromname' => 'SugarCRM',
              'mail_sendtype' => 'SMTP',
              'notify_fromaddress' => 'do_not_reply@example.com',
              'mail_smtpserver' => 'smtp.gmail.com',
              'mail_smtpport' => '587',
              'mail_smtpauth_req' => '1',
              'mail_smtpuser' => 'fail@gmail.com',
              'mail_smtppass' => 'fail847d',
              'massemailer_campaign_emails_per_run' => '500',
              'massemailer_tracking_entities_location_type' => '1',
              'name' => 'UnitTest_Mailbox11203',
              'server_url' => 'smtp.gmail.com',
              'email_user' => 'fail@gmail.com',
              'protocol' => 'imap',
              'port' => '993',
              'mark_read' => '1',
              'only_since' => '1',
              'mailbox_type' => 'bounce',
              'from_name' => 'SugarCRM',
              'group_id' => 'new',
              'from_name' =>'fail@gmail.com',
              'from_addr' =>'fail@gmail.com',
              'reply_to_name' =>'failed',
              'reply_to_addr' =>'fail@gmail.com',
              'filter_domain' =>'somedomain.com',
              'email_num_autoreplies_24_hours' =>'10',

          );

	}
    public function tearDown()
    {   global $current_user;
        $GLOBALS['db']->query("DELETE FROM user_preferences WHERE assigned_user_id='{$current_user->id}'");
        $current_user = $this->_user;
        $GLOBALS['db']->query("DELETE FROM inbound_email WHERE name='UnitTest_Mailbox11203'");
        unset($_REQUEST);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

    }

    function test_CampaignEmailSetupFailure()
    {

        //include the save file like WizardEmailSetupSave.php does
         require_once('modules/InboundEmail/Save.php');

        //Test that the failure was returned.
        $this->assertTrue($_REQUEST['error'], 'Request did not have the error flag set to true after failed Inbound Email Save, this means that the campaign wizard will not display an error as it should have.');
    }

}
?>
