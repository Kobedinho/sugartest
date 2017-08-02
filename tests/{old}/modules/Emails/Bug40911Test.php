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
 

class Bug40911 extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $current_user;
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
    }
    
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * Save a SugarFolder 
     */
    public function testSaveNewFolder()
    {
        global $current_user, $app_strings;

        $email = new Email();
        $email->type = 'out';
        $email->status = 'sent';
        $email->from_addr_name = $email->cleanEmails("sender@domain.eu");
        $email->to_addrs_names = $email->cleanEmails("to@domain.eu");
        $email->cc_addrs_names = $email->cleanEmails("cc@domain.eu");
        $email->save();

        $_REQUEST["emailUIAction"] = "getSingleMessageFromSugar";
        $_REQUEST["uid"] = $email->id;
        $_REQUEST["mbox"] = "";
        $_REQUEST['ieId'] = "";
        ob_start();
        require "modules/Emails/EmailUIAjax.php";
        $jsonOutput = ob_get_contents();
        ob_end_clean();
        $meta = json_decode($jsonOutput);

        $this->assertRegExp("/.*cc@domain.eu.*/", $meta->meta->email->cc);
    }
    
}


