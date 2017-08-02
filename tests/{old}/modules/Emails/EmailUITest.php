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


class EmailUITest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_folders = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('mod_strings', array('Emails'));
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
        $this->_user = $GLOBALS['current_user'];
        $this->_user->is_admin = 1;
        $GLOBALS['current_user'] = $this->_user;
        $this->eui = new EmailUIMock();
        $this->_folders = array();
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE assigned_user_id='{$GLOBALS['current_user']->id}'");
        foreach ($this->_folders as $f) {
            $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE folder_id='{$f}'");
            $GLOBALS['db']->query("DELETE FROM folders WHERE id='{$f}'");
        }

        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE assigned_user_id='{$this->_user->id}'");

        foreach ($this->_folders as $f) {
            $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE folder_id='{$f}'");
            $GLOBALS['db']->query("DELETE FROM folders WHERE id='{$f}'");
        }

        SugarTestHelper::tearDown();
    }

    /**
     * Save a SugarFolder
     */
    public function testSaveNewFolder()
    {
        $newFolderName = "UNIT_TEST";
        $rs = $this->eui->saveNewFolder($newFolderName,'Home',0);
        $newFolderID = $rs['id'];
        $this->_folders[] = $newFolderID;

        $sf = new SugarFolder();
        $sf->retrieve($newFolderID);
        $this->assertEquals($newFolderName, $sf->name);

    }

    /**
     * Save the user preference for list view order per IE account.
     *
     */
    public function testSaveListViewSortOrder()
    {
        $tmpId = create_guid();
        $folderName = "UNIT_TEST";
        $sortBy = 'last_name';
        $dir = "DESC";
        $rs = $this->eui->saveListViewSortOrder($tmpId,$folderName,$sortBy,$dir);

        //Check against the saved preferences.
        $prefs = unserialize($GLOBALS['current_user']->getPreference('folderSortOrder', 'Emails'));
        $this->assertEquals($sortBy, $prefs[$tmpId][$folderName]['current']['sort']);
        $this->assertEquals($dir, $prefs[$tmpId][$folderName]['current']['direction']);


    }
    public function testGetRelatedEmail()
    {

    	$account = new Account();
    	$account->name = "emailTestAccount";
    	$account->save(false);

    	$relatedBeanInfo = array('related_bean_id' => $account->id,  "related_bean_type" => "Accounts");

    	//First pass should return a blank query as are no related items
    	$qArray = $this->eui->getRelatedEmail("LBL_DROPDOWN_LIST_ALL", array(), $relatedBeanInfo);
    	$this->assertEquals("", $qArray['query']);

    	//Now create a related Contact
    	$contact = new Contact();
    	$contact->name = "emailTestContact";
    	$contact->account_id = $account->id;
    	$contact->account_name = $account->name;
    	$contact->email1 = "test@test.com";
    	$contact->save(false);

    	//Now we should get a result
        $qArray = $this->eui->getRelatedEmail("LBL_DROPDOWN_LIST_ALL", array(), $relatedBeanInfo);
        $r = $account->db->limitQuery($qArray['query'], 0, 25, true);
        $person = array();
        $a = $account->db->fetchByAssoc($r);
        $person['bean_id'] = $a['id'];
        $person['bean_module'] = $a['module'];
        $person['email'] = $a['email_address'];

        //Cleanup
    	$GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$account->id}'");
    	$GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$contact->id}'");

        $this->assertEquals("test@test.com", $person['email']);
    }

    /**
     * @ticket 29521
     */
    public function testLoadQuickCreateModules()
    {
        $qArray = $this->eui->_loadQuickCreateModules();

        $this->assertEquals(array('Bugs','Cases','Contacts', 'Opportunities', 'Leads', 'Tasks'), $qArray);
    }

    /**
     * @ticket 29521
     */
    public function testLoadCustomQuickCreateModulesCanMergeModules()
    {
        if (file_exists('custom/modules/Emails/metadata/qcmodulesdefs.php')) {
            copy('custom/modules/Emails/metadata/qcmodulesdefs.php','custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak');
        }
        sugar_mkdir("custom/modules/Emails/metadata/",null,true);
        SugarAutoLoader::put(
            'custom/modules/Emails/metadata/qcmodulesdefs.php',
            '<?php $QCModules[] = "Users"; ?>'
            );

        $qArray = $this->eui->_loadQuickCreateModules();

        if (file_exists('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak')) {
            copy('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak','custom/modules/Emails/metadata/qcmodulesdefs.php');
            unlink('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak');
        }
        else {
            SugarAutoLoader::unlink('custom/modules/Emails/metadata/qcmodulesdefs.php');
        }

        $this->assertEquals(array('Bugs','Cases','Contacts', 'Opportunities','Leads', 'Tasks', 'Users'), $qArray);
    }

    /**
     * @ticket 29521
     */
    public function testLoadQuickCreateModulesInvalidModule()
    {
        if (file_exists('custom/modules/Emails/metadata/qcmodulesdefs.php')) {
            copy('custom/modules/Emails/metadata/qcmodulesdefs.php','custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak');
        }
        sugar_mkdir("custom/modules/Emails/metadata/",null,true);
        SugarAutoLoader::put(
            'custom/modules/Emails/metadata/qcmodulesdefs.php',
            '<?php $QCModules[] = "EmailUIUnitTest"; ?>'
            );

        $qArray = $this->eui->_loadQuickCreateModules();

        if (file_exists('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak')) {
            copy('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak','custom/modules/Emails/metadata/qcmodulesdefs.php');
            unlink('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak');
        }
        else {
            SugarAutoLoader::unlink('custom/modules/Emails/metadata/qcmodulesdefs.php');
        }

        $this->assertEquals(array('Bugs','Cases','Contacts', 'Opportunities', 'Leads', 'Tasks'), $qArray);
    }

    /**
     * @ticket 29521
     */
    public function testLoadQuickCreateModulesCanOverrideDefaultModules()
    {
        if (file_exists('custom/modules/Emails/metadata/qcmodulesdefs.php')) {
            copy('custom/modules/Emails/metadata/qcmodulesdefs.php','custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak');
        }
        sugar_mkdir("custom/modules/Emails/metadata/",null,true);
        SugarAutoLoader::put(
            'custom/modules/Emails/metadata/qcmodulesdefs.php',
            '<?php $QCModules = array("Users"); ?>'
            );

        $qArray = $this->eui->_loadQuickCreateModules();

        if (file_exists('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak')) {
            copy('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak','custom/modules/Emails/metadata/qcmodulesdefs.php');
            unlink('custom/modules/Emails/metadata/qcmodulesdefs.php.test.bak');
        }
        else {
            SugarAutoLoader::unlink('custom/modules/Emails/metadata/qcmodulesdefs.php');
        }

        $this->assertEquals(array("Users"), $qArray);
    }

    /**
     * This is the data provider function for testLoadQuickCreateForm
     *
     * @return array
     */
    public function loadQuickCreateFormDataProvider()
    {
        return array(
            array('Bugs', 'modules/Bugs/metadata/editviewdefs.php', false),
            array('Cases', 'modules/Cases/metadata/editviewdefs.php', false),
            array('Contacts', 'modules/Contacts/metadata/editviewdefs.php', true),
            array('Opportunities', 'modules/Opportunities/metadata/editviewdefs.php', false),
            array('Leads', 'modules/Leads/metadata/editviewdefs.php', true),
            array('Tasks', 'modules/Tasks/metadata/editviewdefs.php', false)
        );
    }

    /**
     * @ticket 56711
     * @dataProvider loadQuickCreateFormDataProvider
     *
     * @param $module String value of module to test
     * @param $file String value of the path to editviewdefs.php file for module
     * @param $hasEmail boolean value indicating whether or not the quick create form form module has an email field
     */
    public function testLoadQuickCreateForm($module, $file, $hasEmail)
    {
        $email = new Bug56711Mock();
        $email->name = 'test';
        $email->from_name = 'Bug56711';
        $email->from_addr = 'Bug56711@sugarcrm.com';
        $email->to_addrs_names = 'test@sugarcrm.com';
        $email->description = 'This is a mock object!';

        //Stuff $_REQUEST parameter
        $_REQUEST['qc_module'] = $module;
        $output = $this->eui->getQuickCreateForm(array(), $email);
        $this->assertNotEmpty($output['html']);
        if($hasEmail)
        {
            $this->assertNotEmpty($output['emailAddress']);
        }

        $createdCustomFile = false;

        if(!file_exists("custom/{$file}"))
        {
           $moduleDir = dirname("custom/{$file}");
           if(!file_exists($moduleDir))
           {
               mkdir_recursive($moduleDir);
           }
           file_put_contents("custom/{$file}", file_get_contents($file));
           $createdCustomFile = true;
        }

        $output = $this->eui->getQuickCreateForm(array(), $email);
        $this->assertNotEmpty($output['html']);
        if($hasEmail)
        {
            $this->assertNotEmpty($output['emailAddress']);
        }

        if($createdCustomFile)
        {
            //Delete the custom file created for testing
            unlink("custom/{$file}");
        }

        unset($_REQUEST['qc_module']);
    }
}

/**
 * This is a mock object to simulate the email object
 */
class Bug56711Mock {
    public $name;
    public $from_name;
    public $from_addr;
    public $to_addrs_names;
    public $description;
}

class EmailUIMock extends EmailUI
{
    public function _loadQuickCreateModules()
    {
        return parent::_loadQuickCreateModules();
    }
}
