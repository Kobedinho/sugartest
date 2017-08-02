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
 * @group bug47572
 */
class Bug47572Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp() {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown(){
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testShowSubpanelsSettingForPrint()
    {
        $viewClass = 'ViewDetail';
        $type = 'detail';

        $view = new $viewClass();
        $view->module = 'Cases';
        ViewFactory::_loadConfig($view, $type);

        $_REQUEST['print'] = true;
        $view->preDisplay();

        $this->assertFalse($view->options['show_subpanels'], 'show_subpanels should be false for print');
    }
}