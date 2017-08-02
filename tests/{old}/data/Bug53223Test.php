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
 * Bug #53223
 * wrong relationship from subpanel create button
 *
 * @ticket 53223
 */
class Bug53223Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DeployedRelationships
     */
    protected $relationships = null;

    /**
     * @var OneToOneRelationship
     */
    protected $relationship = null;

    private $parentAccount;

    private function createRelationship($lhs_module, $rhs_module = null, $relationship_type = 'one-to-many')
    {
        $rhs_module = $rhs_module == null ? $lhs_module : $rhs_module;

        // Adding relation between products and users
        $this->relationships = new DeployedRelationships($lhs_module);
        $definition = array(
            'lhs_module' => $lhs_module,
            'relationship_type' => $relationship_type,
            'rhs_module' => $rhs_module,
            'lhs_label' => $lhs_module,
            'rhs_label' => $rhs_module,
            'rhs_subpanel' => 'default'
        );
        $this->relationship = RelationshipFactory::newRelationship($definition);
        $this->relationships->add($this->relationship);
        $this->relationships->save();
        $this->relationships->build();
        LanguageManager::clearLanguageCache($lhs_module);

        // Updating $dictionary by created relation
        global $dictionary;
        $moduleInstaller = new ModuleInstaller();
        $moduleInstaller->silent = true;
        $moduleInstaller->rebuild_tabledictionary();
        require 'modules/TableDictionary.php';

        // Updating vardefs
        VardefManager::$linkFields = array();
        VardefManager::clearVardef();
        VardefManager::refreshVardefs($lhs_module, BeanFactory::getObjectName($lhs_module));
        if ( $lhs_module != $rhs_module )
        {
            VardefManager::refreshVardefs($rhs_module, BeanFactory::getObjectName($rhs_module));
        }
        SugarRelationshipFactory::rebuildCache();
    }

    private function deleteRelationship($lhs_module, $rhs_module = null, $rel_name = null)
    {
        $rhs_module = $rhs_module == null ? $lhs_module : $rhs_module;

        $this->relationships = new DeployedRelationships($lhs_module);
        $this->relationships->delete($rel_name !== null ? $rel_name : $this->relationship->getName());
        $this->relationships->save();
        SugarRelationshipFactory::deleteCache();
        LanguageManager::clearLanguageCache($lhs_module);
        if ( $lhs_module != $rhs_module )
        {
            LanguageManager::clearLanguageCache($rhs_module);
        }
    }

    public function setUp()
    {
        $this->markTestIncomplete("This test is not yet complete. Artem is working on it");
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = 1;

        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        $this->createRelationship('Accounts');
        $this->parentAccount = SugarTestAccountUtilities::createAccount();
    }

    public function tearDown()
    {
        $this->markTestIncomplete("This test is not yet complete. Artem is working on it");
        $this->deleteRelationship('Accounts');

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        unset($GLOBALS['beanFiles'], $GLOBALS['beanList']);
        unset($GLOBALS['app_strings'], $GLOBALS['app_list_strings'], $GLOBALS['mod_strings']);

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        unset($this->parentAccount);
    }

    /**
     * @group 53223
     */
    public function testOneToManyRelationshipModule2Modult()
    {
        $_REQUEST['relate_id'] = $this->parentAccount->id;
        $_REQUEST['relate_to'] = $this->relationship->getName();

        // create new account
        $objAccount = new Account();
        $objAccount->name = "AccountBug53223".$_REQUEST['relate_to'].time();
        $objAccount->save();
        SugarTestAccountUtilities::setCreatedAccount(array($objAccount->id));

        // Retrieve new data
        $this->parentAccount->retrieve($this->parentAccount->id);
        $objAccount->retrieve($objAccount->id);
        $this->parentAccount->load_relationship($this->relationship->getName());
        $objAccount->load_relationship($this->relationship->getName());

        // Getting data of subpanel of parent bean
        $_REQUEST['module'] = 'Accounts';
        $_REQUEST['action'] = 'DetailView';
        $_REQUEST['record'] = $this->parentAccount->id;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        unset($GLOBALS['focus']);

        $subpanels = new SubPanelDefinitions($this->parentAccount, 'Accounts');
        $subpanelDef = $subpanels->load_subpanel($this->relationship->getName().'accounts_ida');
        $subpanel = new SubPanel('Accounts', $this->parentAccount->id, 'default', $subpanelDef);
        $subpanel->setTemplateFile('include/SubPanel/SubPanelDynamic.html');
        $subpanel->display();
        $actual = $this->getActualOutput();
        $this->assertContains($objAccount->name, $actual, 'Account name is not displayed in subpanel of parent account');

        ob_clean();

        // Getting data of subpanel of child bean
        $_REQUEST['module'] = 'Accounts';
        $_REQUEST['action'] = 'DetailView';
        $_REQUEST['record'] = $objAccount->id;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        unset($GLOBALS['focus']);

        $subpanels = new SubPanelDefinitions($objAccount, 'Accounts');
        $subpanelDef = $subpanels->load_subpanel($this->relationship->getName().'accounts_ida');
        $subpanel = new SubPanel('Accounts', $objAccount->id, 'default', $subpanelDef);
        $subpanel->setTemplateFile('include/SubPanel/SubPanelDynamic.html');
        $subpanel->display();
        $actual = $this->getActualOutput();
        $this->assertNotContains($this->parentAccount->name, $actual, 'Parent account name is displayed in subpanel of child aaccount');
    }
}
