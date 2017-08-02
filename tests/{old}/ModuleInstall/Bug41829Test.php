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
require_once('ModuleInstall/ModuleInstaller.php');

class Bug41829Test extends Sugar_PHPUnit_Framework_TestCase 
{   	
    protected $module_installer;
    protected $log;

	public function setUp()
	{
        SugarTestHelper::setUp("current_user");
        $this->module_installer = new ModuleInstaller();
        $this->module_installer->silent = true;
        $this->module_installer->base_dir = '';
        $this->module_installer->id_name = 'Bug41829Test';
        $this->module_installer->installdefs['dcaction'] = array(
            array(
                'from' => '<basepath>/dcaction_file.php',
            ),
        );
	    $this->log = $GLOBALS['log'];
        $GLOBALS['log'] = new SugarMockLogger();
	}

	public function tearDown()
	{
        SugarTestHelper::tearDown();
        $GLOBALS['log'] = $this->log;
	}

    public function testWarningOnUninstallDCActions()
    {
        $this->module_installer->uninstall_dcactions();

        $messages = $GLOBALS['log']->getMessages();
        $this->assertTrue(in_array('DEBUG: Uninstalling DCActions ...'  . str_replace('<basepath>', $this->module_installer->base_dir,  $this->module_installer->installdefs['dcaction'][0]['from']), $messages));
    }


}
