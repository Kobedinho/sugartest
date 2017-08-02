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
 
class Bug42994Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_smarty;
    private $_lang_manager;

    public function setUp()
    {
        $this->_smarty = new Sugar_Smarty();
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->_lang_manager = new SugarTestLangPackCreator();
        $GLOBALS['current_language'] = 'en_us';
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($this->_lang_manager);
    }

    public function testSetLanguageStringDependant() 
    {

        $this->_lang_manager->setModString('LBL_DEPENDENT','XXDependentXX','DynamicFields');
        $this->_lang_manager->save();
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'DynamicFields');
        $output = $this->_smarty->fetch('modules/DynamicFields/templates/Fields/Forms/coreDependent.tpl');

        $this->assertContains('XXDependentXX', $output);
    }
    
    public function testSetLanguageStringVisible() 
    {
        $this->_lang_manager->setModString('LBL_VISIBLE_IF','XXVisible ifXX','DynamicFields');
        $this->_lang_manager->save();
        $output = $this->_smarty->fetch('modules/DynamicFields/templates/Fields/Forms/coreDependent.tpl');

        $this->assertContains('XXVisible ifXX', $output);
    }
}
