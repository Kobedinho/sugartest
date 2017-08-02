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


class UpdateRelatedCalcFieldTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $testAccount;
    protected $createdBeans = array();
    protected $createdFiles = array();

    public function setUp()
	{
	    $this->markTestIncomplete('Disabled by John Mertic');
	    require('include/modules.php');
	    $GLOBALS['beanList'] = $beanList;
	    $GLOBALS['beanFiles'] = $beanFiles;
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = 1;
	    $GLOBALS['current_user']->setPreference('timezone', "America/Los_Angeles");
	    $GLOBALS['current_user']->setPreference('datef', "m/d/Y");
		$GLOBALS['current_user']->setPreference('timef', "h.iA");

        //Create a CF on the description field.
        $extensionContent = <<<EOQ
<?php
\$dictionary['Account']['fields']['description']['calculated'] = true;
\$dictionary['Account']['fields']['description']['formula']    = 'count(\$contacts)';
\$dictionary['Account']['fields']['description']['enforced']   = true;

EOQ;
        create_custom_directory("Extension/modules/Accounts/Ext/Vardefs/description_calc_field.php");
        $fileLoc = "custom/Extension/modules/Accounts/Ext/Vardefs/description_calc_field.php";
        $this->createdFiles[] = $fileLoc;
        file_put_contents($fileLoc, $extensionContent);
        $_REQUEST['repair_silent']=1;
        $rc = new RepairAndClear();
        $rc->repairAndClearAll(array("rebuildExtensions", "clearVardefs"), array("Accounts", "Contacts"),  false, false);
	}

	/*public function tearDown()
	{
	    foreach($this->createdBeans as $bean)
        {
            $bean->retrieve($bean->id);
            $bean->mark_deleted($bean->id);
        }
        foreach($this->createdFiles as $file)
        {
            if (is_file($file))
                unlink($file);
        }
        $rc = new RepairAndClear();
        $rc->repairAndClearAll(array("rebuildExtensions", "clearVardefs"), array("Accounts", "Contacts"), false, false);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	    unset($GLOBALS['current_user']);
	    unset($GLOBALS['beanList']);
	    unset($GLOBALS['beanFiles']);
	}*/
	

	public function testUpdateAccountCFWhenContactSave()
	{
        $account = new Account();
        $account->name = "CalcFieldTestAccount";
        $account->save();
        $this->createdBeans[] = $account;
        $this->assertEmpty($account->description);

        //First try a simple new Contact
        $contact1 = new Contact();
        $contact1->name = "CalcFieldTestContact1";
        $contact1->account_id = $account->id;
        $contact1->save();
        $this->createdBeans[] = $contact1;

        //refresh the account
        $account->retrieve($account->id);
        $this->assertEquals("1", $account->description);

        //Try creating a contact and add it from the account side
        $contact2 = new Contact();
        $contact2->name = "CalcFieldTestContact2";
        $contact2->save();
        $this->createdBeans[] = $contact2;

        $account->load_relationship("contacts");
        $account->contacts->add($contact2->id);
        $account->save();

        $this->assertEquals("2", $account->description);

        //Try creating a contact and add it from the contact side
        $contact3 = new Contact();
        $contact3->name = "CalcFieldTestContact3";
        $contact3->save();
        $this->createdBeans[] = $contact3;

        $contact3->load_relationship("accounts");
        $contact3->accounts->add($account->id);

        $contact3->save();

        $account->retrieve($account->id);
        $this->assertEquals("3", $account->description);


        //Try removing a contact from the contact side
        $contact3->accounts->delete($contact3->id, $account->id);
        $contact3->save();

        $account->retrieve($account->id);
        $this->assertEquals("2", $account->description);

        //Try removing a contact from the account side
        $account->load_relationship("contacts");
        $account->contacts->delete($account->id, $contact2->id);
        $account->retrieve($account->id);
        $this->assertEquals("1", $account->description);
    }
}
