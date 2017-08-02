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


class SugarViewTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_backup = array();

    /**
     * @var SugarViewTestMock
     */
    private $_view;

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('mod_strings', array('Users'));
        $this->_view = new SugarViewTestMock();
        parent::setUp();
        $this->dir = getcwd();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
        chdir($this->dir);
    }

    public function testGetModuleTab()
    {
        $_REQUEST['module_tab'] = 'ADMIN';
        $moduleTab = $this->_view->getModuleTab();
        $this->assertEquals('ADMIN', $moduleTab, 'Module Tab names are not equal from request');
    }

    public function testGetMetaDataFile()
    {
        // backup custom file if it already exists
        if(file_exists('custom/modules/Contacts/metadata/listviewdefs.php')){
            copy('custom/modules/Contacts/metadata/listviewdefs.php', 'custom/modules/Contacts/metadata/listviewdefs.php.bak');
            SugarAutoLoader::unlink('custom/modules/Contacts/metadata/listviewdefs.php', false);
        }
        $this->_view->module = 'Contacts';
        $this->_view->type = 'list';
        $metaDataFile = $this->_view->getMetaDataFile();
        $this->assertEquals('modules/Contacts/metadata/listviewdefs.php', $metaDataFile, 'Did not load the correct metadata file');

        //test custom file
        if(!file_exists('custom/modules/Contacts/metadata/')){
            sugar_mkdir('custom/modules/Contacts/metadata/', null, true);
        }
        $customFile = 'custom/modules/Contacts/metadata/listviewdefs.php';
        if(!file_exists($customFile))
        {
            SugarAutoLoader::touch($customFile, false);
            $customMetaDataFile = $this->_view->getMetaDataFile();
            $this->assertEquals($customFile, $customMetaDataFile, 'Did not load the correct custom metadata file');
            SugarAutoLoader::unlink($customFile, false);
        }
        // Restore custom file if we backed it up
        if(file_exists('custom/modules/Contacts/metadata/listviewdefs.php.bak')){
            rename('custom/modules/Contacts/metadata/listviewdefs.php.bak', 'custom/modules/Contacts/metadata/listviewdefs.php');
        }
    }

    public function testInit()
    {
        $bean = new SugarBean;
        $view_object_map = array('foo'=>'bar');
        $GLOBALS['action'] = 'barbar';
        $GLOBALS['module'] = 'foofoo';

        $this->_view->init($bean,$view_object_map);

        $this->assertInstanceOf('SugarBean',$this->_view->bean);
        $this->assertEquals($view_object_map,$this->_view->view_object_map);
        $this->assertEquals($GLOBALS['action'],$this->_view->action);
        $this->assertEquals($GLOBALS['module'],$this->_view->module);
        $this->assertInstanceOf('Sugar_Smarty',$this->_view->ss);
    }

    public function testInitNoParameters()
    {
        $GLOBALS['action'] = 'barbar';
        $GLOBALS['module'] = 'foofoo';

        $this->_view->init();

        $this->assertNull($this->_view->bean);
        $this->assertEquals(array(),$this->_view->view_object_map);
        $this->assertEquals($GLOBALS['action'],$this->_view->action);
        $this->assertEquals($GLOBALS['module'],$this->_view->module);
        $this->assertInstanceOf('Sugar_Smarty',$this->_view->ss);
    }

    public function testInitSmarty()
    {
        $this->_view->initSmarty();

        $this->assertInstanceOf('Sugar_Smarty',$this->_view->ss);
        $this->assertEquals($this->_view->ss->get_template_vars('MOD'),$GLOBALS['mod_strings']);
        $this->assertEquals($this->_view->ss->get_template_vars('APP'),$GLOBALS['app_strings']);
    }

    /**
     * @outputBuffering enabled
     */
    public function testDisplayErrors()
    {
        $this->_view->errors = array('error1','error2');
        $this->_view->suppressDisplayErrors = true;

        $this->assertEquals(
            '<span class="error">error1</span><br><span class="error">error2</span><br>',
            $this->_view->displayErrors()
            );
    }

    /**
     * @outputBuffering enabled
     */
    public function testDisplayErrorsDoNotSupressOutput()
    {
        $this->_view->errors = array('error1','error2');
        $this->_view->suppressDisplayErrors = false;

        $this->expectOutputString('<span class="error">error1</span><br><span class="error">error2</span><br>');
        $this->_view->displayErrors();
    }

    public function testGetBrowserTitle()
    {
        $viewMock = $this->createPartialMock('SugarViewTestMock', array('_getModuleTitleParams'));
        $viewMock->expects($this->any())
                 ->method('_getModuleTitleParams')
                 ->will($this->returnValue(array('foo','bar')));

        $this->assertEquals(
            "bar &raquo; foo &raquo; {$GLOBALS['app_strings']['LBL_BROWSER_TITLE']}",
            $viewMock->getBrowserTitle()
            );
    }

    public function testGetBrowserTitleUserLogin()
    {
        $this->_view->module = 'Users';
        $this->_view->action = 'Login';

        $this->assertEquals(
            "{$GLOBALS['app_strings']['LBL_BROWSER_TITLE']}",
            $this->_view->getBrowserTitle()
            );
    }

    public function testGetBreadCrumbSymbolForLTRTheme()
    {
        SugarTestHelper::setUp('theme');
        $theme = SugarTestThemeUtilities::createAnonymousTheme();
        SugarThemeRegistry::set($theme);

        $this->assertEquals(
            "<span class='breadCrumbSymbol'>&raquo;</span>",
            $this->_view->getBreadCrumbSymbol()
            );
    }

    public function testGetBreadCrumbSymbolForRTLTheme()
    {
        SugarTestHelper::setUp('theme');
        $theme = SugarTestThemeUtilities::createAnonymousRTLTheme();
        SugarThemeRegistry::set($theme);

        $this->assertEquals(
            "<span class='breadCrumbSymbol'>&laquo;</span>",
            $this->_view->getBreadCrumbSymbol()
            );
    }

    public function testGetSugarConfigJS()
    {
        global $sugar_config;

        $sugar_config['js_available'] = array('default_action');

        $js_array = $this->_view->getSugarConfigJS();
        $this->assertContains('SUGAR.config.default_action = "index";', $js_array);
    }
}

class SugarViewTestMock extends SugarView
{
    public function getModuleTab()
    {
        return parent::_getModuleTab();
    }

    public function initSmarty()
    {
        return parent::_initSmarty();
    }

    public function getSugarConfigJS()
    {
        return parent::getSugarConfigJS();
    }
}
