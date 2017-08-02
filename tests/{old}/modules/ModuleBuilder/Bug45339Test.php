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
 * Bug #45339
 * Export Customizations Does Not Cleanly Handle Relationships.
 *
 * @ticket 45339
 */
class Bug45339Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $relationAccountContact = null;
    private $relationContactAccount = null;
    private $mbPackage = null;
    private $keys = array(
        'module' => "ModuleBuilder",
        'action' => "SaveRelationship",
        'remove_tables' => "true",
        'view_module' => "",
        'relationship_lang' => "en_us",
        'relationship_name' => "",
        'lhs_module' => "",
        'relationship_type' => "many-to-many",
        'rhs_module' => "",
        'lhs_label' => "",
        'rhs_label' => "",
        'lhs_subpanel' => "default",
        'rhs_subpanel' => "default",
    );
    private $packName = 'test_package';
    private $df = null;
    private $field = null;

    public function setUp()
    {
        $this->markTestIncomplete("Marking as incomplete as it can take long time to run");
        return;
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');

        $_REQUEST = $this->keys;

        $_REQUEST['view_module'] = "Accounts";
        $_REQUEST['lhs_module'] = "Accounts";
        $_REQUEST['rhs_module'] = "Contacts";
        $_REQUEST['lhs_label'] = "Accounts";
        $_REQUEST['rhs_label'] = "Contacts";

        $relationAccountContact = new DeployedRelationships($_REQUEST['view_module']);
        $this->relationAccountContact = $relationAccountContact->addFromPost();
        $relationAccountContact->save();
        $relationAccountContact->build();

        $_REQUEST['view_module'] = "Contacts";
        $_REQUEST['lhs_module'] = "Contacts";
        $_REQUEST['rhs_module'] = "Accounts";
        $_REQUEST['lhs_label'] = "Contacts";
        $_REQUEST['rhs_label'] = "Accounts";

        $relationContactAccount = new DeployedRelationships($_REQUEST['view_module']);
        $this->relationContactAccount = $relationContactAccount->addFromPost();
        $relationContactAccount->save();
        $relationContactAccount->build();
        SugarTestHelper::setUp('relation', array(
            'Contacts',
            'Accounts'
        ));

        //create a new field for accounts
        $this->field = get_widget('varchar');
        $this->field->id = 'Accountstest_45339333_c';
        $this->field->name = 'test_45339333_c';
        $this->field->vname = 'LBL_TEST_CUSTOM_C';
        $this->field->help = NULL;
        $this->field->custom_module = 'Accounts';
        $this->field->type = 'varchar';
        $this->field->label = 'LBL_TEST_CUSTOM_C';
        $this->field->len = 255;
        $this->field->required = 0;
        $this->field->default_value = NULL;
        $this->field->date_modified = '2012-10-31 02:23:23';
        $this->field->deleted = 0;
        $this->field->audited = 0;
        $this->field->massupdate = 0;
        $this->field->duplicate_merge = 0;
        $this->field->reportable = 1;
        $this->field->importable = 'true';
        $this->field->ext1 = NULL;
        $this->field->ext2 = NULL;
        $this->field->ext3 = NULL;
        $this->field->ext4 = NULL;

        //add field to metadata
        $this->df = new DynamicField('Accounts');
        $this->df->setup(new Account());
        $this->df->addFieldObject($this->field);
        $this->df->buildCache('Accounts');
        VardefManager::clearVardef();
        VardefManager::refreshVardefs('Accounts', 'Account');



           //create a new field for accounts
        $this->field = get_widget('varchar');
        $this->field->id = 'Accountstest_45339333_c';
        $this->field->name = 'test_45339333_c';
        $this->field->vname = 'LBL_TEST_CUSTOM_C';
        //$this->field->comments = NULL;
        $this->field->help = NULL;
        $this->field->custom_module = 'Accounts';
        $this->field->type = 'varchar';
        $this->field->label = 'LBL_TEST_CUSTOM_C';
        $this->field->len = 255;
        $this->field->required = 0;
        $this->field->default_value = NULL;
        $this->field->date_modified = '2012-10-31 02:23:23';
        $this->field->deleted = 0;
        $this->field->audited = 0;
        $this->field->massupdate = 0;
        $this->field->duplicate_merge = 0;
        $this->field->reportable = 1;
        $this->field->importable = 'true';
        $this->field->ext1 = NULL;
        $this->field->ext2 = NULL;
        $this->field->ext3 = NULL;
        $this->field->ext4 = NULL;

        //add field to metadata
        $this->df = new DynamicField('Accounts');
        $this->df->setup(new Account());
        $this->df->addFieldObject($this->field);
        $this->df->buildCache('Accounts');
        VardefManager::clearVardef();
        VardefManager::refreshVardefs('Accounts', 'Account');


        $this->mbPackage = new Bug45339MBPackageMock($this->packName);
    }

    public function tearDown()
    {
        $this->markTestIncomplete("Marking as incomplete as it can take long time to run");
        return;
        $this->df->deleteField($this->field);
        $relationshipAccountContact = new DeployedRelationships($this->relationAccountContact->getLhsModule());
        $relationshipAccountContact->delete($this->relationAccountContact->getName());
        $relationshipAccountContact->save();

        $relationshipContactAccount = new DeployedRelationships($this->relationContactAccount->getLhsModule());
        $relationshipContactAccount->delete($this->relationContactAccount->getName());
        $relationshipContactAccount->save();

        SugarRelationshipFactory::deleteCache();

        $_REQUEST = array();

        SugarTestHelper::tearDown();
    }

    /**
     * @group 45339
     */
    public function testGetCustomRelationshipsByModuleName()
    {
        $this->markTestIncomplete("Marking as incomplete as it can take long time to run");
        return;
        /* @var $this->mbPackage MBPackage */
        $accountsAllCustomRelationships = $this->mbPackage->getCustomRelationshipsByModuleNameTest('Accounts');
        // Created in the Account module.
        $accountsLhsCustomRelationships = $this->mbPackage->getCustomRelationshipsByModuleNameTest('Accounts', true);
        $wrongModuleName = $this->mbPackage->getCustomRelationshipsByModuleNameTest('Wrong_module_name');

        $this->assertArrayHasKey($this->relationAccountContact->getName(), $accountsAllCustomRelationships);
        $this->assertArrayHasKey($this->relationContactAccount->getName(), $accountsAllCustomRelationships);

        $this->assertArrayHasKey($this->relationAccountContact->getName(), $accountsLhsCustomRelationships);
        $this->assertArrayNotHasKey($this->relationContactAccount->getName(), $accountsLhsCustomRelationships);

        $this->assertFalse($wrongModuleName); // check
    }

    /**
     * @group 45339
     */
    public function testGetCustomRelationshipsMetaFilesByModuleName()
    {
        $this->markTestIncomplete("Marking as incomplete as it can take long time to run");
        return;
        $accountContactMetaPath = sprintf(
                'custom%1$smetadata%1$s' . $this->relationAccountContact->getName() . 'MetaData.php',
                DIRECTORY_SEPARATOR
        );
        $accountContactTablePath = sprintf(
                'custom%1$sExtension%1$sapplication%1$sExt%1$sTableDictionary%1$s' . $this->relationAccountContact->getName() . '.php',
                DIRECTORY_SEPARATOR
        );
        $contactAccountMetaPath = sprintf(
                'custom%1$smetadata%1$s' . $this->relationContactAccount->getName() . 'MetaData.php',
                DIRECTORY_SEPARATOR
        );

        /* @var $this->mbPackage MBPackage */
        $accountsAllFiles = $this->mbPackage->getCustomRelationshipsMetaFilesByModuleNameTest('Accounts',false,false, array('Accounts','Contacts'));
        $accountsOnlyMetaFile = $this->mbPackage->getCustomRelationshipsMetaFilesByModuleNameTest('Accounts', true, true, array('Accounts'));
        $wrongModuleName = $this->mbPackage->getCustomRelationshipsMetaFilesByModuleNameTest('Wrong_module_name');

        $this->assertContains($accountContactMetaPath, $accountsAllFiles);
        $this->assertContains($accountContactTablePath, $accountsAllFiles);
        $this->assertContains($contactAccountMetaPath, $accountsAllFiles);

        $this->assertNotContains($accountContactMetaPath, $accountsOnlyMetaFile);
        $this->assertNotContains($contactAccountMetaPath, $accountsOnlyMetaFile);

        $this->assertInternalType('array', $wrongModuleName);
        $this->assertEmpty($wrongModuleName);
    }

    /**
     * @group 45339
     */
   public function testGetExtensionsList()
    {
        $this->markTestIncomplete("Marking as incomplete as it can take long time to run");
        return;
        // Create new relationship between Leads and Accounts
        $_REQUEST['view_module'] = "Leads";
        $_REQUEST['lhs_module'] = "Leads";
        $_REQUEST['rhs_module'] = "Accounts";
        $_REQUEST['lhs_label'] = "Leads";
        $_REQUEST['rhs_label'] = "Accounts";

        $deployedRelation = new DeployedRelationships($_REQUEST['view_module']);
        $relationLeadAccount = $deployedRelation->addFromPost();
        $deployedRelation->save();
        $deployedRelation->build();

        //create expected file paths from custom extensions
        $accountContactRelInAccountVardefExtensions = sprintf(
                'custom%1$sExtension%1$smodules%1$sAccounts%1$sExt%1$sVardefs%1$s' . $this->relationAccountContact->getName() . '_Accounts.php',
                DIRECTORY_SEPARATOR
        );
        $contactAccountRelInAccountVardefExtensions = sprintf(
                'custom%1$sExtension%1$smodules%1$sAccounts%1$sExt%1$sVardefs%1$s' . $this->relationContactAccount->getName() . '_Accounts.php',
                DIRECTORY_SEPARATOR
        );
        $leadAccountRelInAccountVardefExtensions = sprintf(
                'custom%1$sExtension%1$smodules%1$sAccounts%1$sExt%1$sVardefs%1$s' . $relationLeadAccount->getName() . '_Accounts.php',
                DIRECTORY_SEPARATOR
        );
        $sugarfieldAccountVardefExtensions = sprintf(
                'custom%1$sExtension%1$smodules%1$sAccounts%1$sExt%1$sVardefs%1$s' .'sugarfield_'. $this->field->name . '.php',
                DIRECTORY_SEPARATOR
        );

        //call mbPackage to retrieve arrays of Files to be exported using different test parameters
        $accountAllExtensions = $this->mbPackage->getExtensionsListTest('Accounts',array('Accounts','Contacts','Leads'));
        $accountExtContacts = $this->mbPackage->getExtensionsListTest('Accounts',array('Accounts','Contacts'));
        $accountExtOnly = $this->mbPackage->getExtensionsListTest('Accounts', array('Accounts'));
        $contactExtWithWrongRelationship = $this->mbPackage->getExtensionsListTest('Contacts', array(''));
        $wrongModuleName = $this->mbPackage->getExtensionsListTest('Wrong_module_name');

        // Remove relationship
        $deployedRelation->delete($relationLeadAccount->getName());
        $deployedRelation->save();
        SugarRelationshipFactory::deleteCache();

        //assert that contact rels are exported when all rels were defined
        $this->assertContains($accountContactRelInAccountVardefExtensions, $accountAllExtensions,'Contact Relationship should have been exported when accounts and contacts modules are exported');

        //assert that contact rels are not exported when contact is not defined
        $this->assertNotContains($accountContactRelInAccountVardefExtensions, $accountExtOnly,'Contact Relationship should NOT have been exported when exporting accounts only');

        //assert that non relationship change is exported when no related module is defined
        $this->assertContains($sugarfieldAccountVardefExtensions, $accountExtOnly,'Sugarfield change should have been exported when exporting Accounts only');

        //assert only contact and Account modules are present when both contact and Accounts are defined
        $this->assertContains($accountContactRelInAccountVardefExtensions, $accountExtContacts,'Accounts rels should be present when exporting Contacts and Accounts');
        $this->assertContains($contactAccountRelInAccountVardefExtensions, $accountExtContacts,'Contacts rels should be present when exporting Contacts and Accounts');
        $this->assertNotContains($leadAccountRelInAccountVardefExtensions, $accountExtContacts,'Leads rels should NOT be present when exporting Contacts and Accounts');

        //assert that requesting a wrong relationship returns an empty array
        $this->assertInternalType('array', $contactExtWithWrongRelationship,'array type should be returned when no relationships are exported, and no other changes exist');
        $this->assertEmpty($contactExtWithWrongRelationship,'An empty array should be returned when no relationships are exported, and no other changes exist');

        //assert that requesting a wrong module name returns an empty array
        $this->assertInternalType('array', $wrongModuleName,'An array type should be returned when a bad module is requested for export');
        $this->assertEmpty($wrongModuleName,'An empty array should be returned when a bad module is requested for export');
    }

    /**
     * @group 45339
     */
    public function testGetExtensionsManifestForPackage()
    {
        $this->markTestIncomplete("Marking as incomplete as it can take long time to run");
        return;
        /* @var $this->mbPackage MBPackage */
        $this->mbPackage->exportCustom(array('Accounts'), false, false);
        $installDefs = array();
        $packExtentionsPath = $this->mbPackage->getBuildDir() . DIRECTORY_SEPARATOR . 'Extension' . DIRECTORY_SEPARATOR . 'modules';
        $expected = 0;

        $this->mbPackage->getExtensionsManifestForPackageTest($this->mbPackage->getBuildDir(), $installDefs);

        $recursiveIterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($packExtentionsPath),
                        RecursiveIteratorIterator::SELF_FIRST
        );

        /* @var $fInfo SplFileInfo */
        foreach (new RegexIterator($recursiveIterator, "/\.php$/i") as $fInfo)
        {
            if ($fInfo->isFile())
            {
                ++$expected;
            }
        }

        $this->mbPackage->delete();
        $this->mbPackage->deleteBuild();

        $this->assertEquals($expected, count($installDefs['copy']));
    }

    /**
     * @group 45339
     */
    public function testCustomBuildInstall()
    {
        $this->markTestIncomplete("Marking as incomplete as it can take long time to run");
        return;
        /* @var $this->mbPackage MBPackage */
        $this->mbPackage->exportCustom(array('Accounts'), false, false);
        $installDefString = $this->mbPackage->customBuildInstall(array('Accounts'), $this->mbPackage->getBuildDir());

        eval($installDefString);

        $this->mbPackage->delete();
        $this->mbPackage->deleteBuild();
        
        $this->assertArrayHasKey('relationships', $installdefs);
    }

}

class Bug45339MBPackageMock extends MBPackage
{

    public function getExtensionsManifestForPackageTest($path, &$installdefs)
    {
        return $this->getExtensionsManifestForPackage($path, $installdefs);
    }

    public function getExtensionsListTest($module, $includeRelationships = true)
    {
        return $this->getExtensionsList($module, $includeRelationships);
    }

    public function getCustomRelationshipsMetaFilesByModuleNameTest($moduleName, $lhs = false, $metadataOnly = false,$exportModules=array())
    {
        return $this->getCustomRelationshipsMetaFilesByModuleName($moduleName, $lhs, $metadataOnly,$exportModules);
    }

    public function getCustomRelationshipsByModuleNameTest($moduleName, $lhs = false)
    {
        return $this->getCustomRelationshipsByModuleName($moduleName, $lhs);
    }


}
