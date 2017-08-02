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


class SugarApplicationTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_app;
    // Run in isolation so that it doesn't mess up other tests
    public $inIsolation = true;

    public function setUp()
    {
        $startTime = microtime();
        $system_config = new Administration();
        $system_config->retrieveSettings();
        $GLOBALS['system_config'] = $system_config;
        $this->_app = $this->getMockBuilder('SugarApplication')
            ->setMethods(null)
            ->getMock();
        $this->_app->controller = new stdClass();
        if ( isset($_SESSION['authenticated_user_theme']) )
            unset($_SESSION['authenticated_user_theme']);

        if ( isset($GLOBALS['sugar_config']['http_referer']) ) {
            $this->prevRefererList = $GLOBALS['sugar_config']['http_referer'];
        }

        $GLOBALS['sugar_config']['http_referer'] = array('list' => array(), 'actions' => array());
    }

    private function _loadUser()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $_SESSION[$GLOBALS['current_user']->user_name.'_PREFERENCES']['global']['gridline'] = 'on';
    }

    private function _removeUser()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }


    public function tearDown()
    {
        /*
        unset($GLOBALS['current_user']);
        unset($GLOBALS['moduleList']);
        unset($GLOBALS['request_string']);
        unset($GLOBALS['adminOnlyList']);
        unset($GLOBALS['modListHeader']);
        unset($GLOBALS['modInvisList']);
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['system_config']);
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['theme']);
        unset($GLOBALS['image_path']);
        unset($GLOBALS['starttTime']);
        unset($GLOBALS['sugar_version']);
        unset($GLOBALS['sugar_flavor']);
        */
        $GLOBALS['current_language'] = $GLOBALS['sugar_config']['default_language'];

        if ( isset($this->prevRefererList)) {
            $GLOBALS['sugar_config']['http_referer'] = $this->prevRefererList;
        } else {
            unset ($GLOBALS['sugar_config']['http_referer']);
        }

        global $sugar_version, $sugar_db_version, $sugar_flavor, $sugar_build, $sugar_timestamp;
        require('sugar_version.php');
    }

    public function testSetupPrint()
    {
        $_GET['foo'] = 'bar';
        $_POST['dog'] = 'cat';
        $this->_app->setupPrint();
        $this->assertEquals($GLOBALS['request_string'],
            'foo=bar&dog=cat&print=true'
        );
    }

    /*
     * @ticket 40277
     */
    public function testSetupPrintWithMultidimensionalArray()
    {
        $_GET['foo'] = array(
                            '0' => array(
                                   '0'=>'bar',
                                   'a' => 'hej'),
                            '1' => 'notMultidemensional',
                            '2' => 'notMultidemensional',
                           );
        $_POST['dog'] = 'cat';
        $this->_app->setupPrint();
        $this->assertEquals('foo[1]=notMultidemensional&foo[2]=notMultidemensional&dog=cat&print=true', $GLOBALS['request_string']
        );
    }

    public function testLoadDisplaySettingsDefault()
    {
        $this->_loadUser();

        $this->_app->loadDisplaySettings();

        $this->assertEquals($GLOBALS['theme'],
            $GLOBALS['sugar_config']['default_theme']);

        $this->_removeUser();
    }

    public function testLoadDisplaySettingsAuthUserTheme()
    {
        $this->_loadUser();

        $_SESSION['authenticated_user_theme'] = 'Sugar';

        $this->_app->loadDisplaySettings();

        $this->assertEquals(
            $GLOBALS['theme'],
            'RacerX',
            'Multiple themes are no longer supported. It should always load RacerX'
        );

        $this->_removeUser();
    }

    public function testLoadDisplaySettingsUserTheme()
    {
        $this->_loadUser();
        $_REQUEST['usertheme'] = (string) SugarThemeRegistry::getDefault();

        $this->_app->loadDisplaySettings();

        global $sugar_config;
        $disabledThemes = !empty($sugar_config['disabled_themes']) ? $sugar_config['disabled_themes'] : array();
        if(is_string($disabledThemes)) {
            $disabledThemes = array($disabledThemes);
        }
        $expectedTheme = !in_array($GLOBALS['theme'], $disabledThemes) ? $GLOBALS['theme'] : 'RacerX';

        $this->assertEquals($expectedTheme,
            $_REQUEST['usertheme']);

        $this->_removeUser();
    }

    public function testLoadGlobals()
    {
        $this->_app->controller =
            ControllerFactory::getController($this->_app->default_module);
        $this->_app->loadGlobals();

        $this->assertEquals($GLOBALS['currentModule'],$this->_app->default_module);
        $this->assertEquals($_REQUEST['module'],$this->_app->default_module);
        $this->assertEquals($_REQUEST['action'],$this->_app->default_action);
    }

    /**
     * @ticket 33283
     */
    public function testCheckDatabaseVersion()
    {
        if ( isset($GLOBALS['sugar_db_version']) )
            $old_sugar_db_version = $GLOBALS['sugar_db_version'];
        if ( isset($GLOBALS['sugar_version']) )
            $old_sugar_version = $GLOBALS['sugar_version'];
        include 'sugar_version.php';
        $GLOBALS['sugar_version'] = $sugar_version;

        // first test a valid value
        $GLOBALS['sugar_db_version'] = $sugar_db_version;
        $this->assertTrue($this->_app->checkDatabaseVersion(false));

        $GLOBALS['sugar_db_version'] = '1.1.1';
        // then test to see if we pull against the cache the valid value
        $this->assertTrue($this->_app->checkDatabaseVersion(false));

        // now retest to be sure we actually do the check again
        sugar_cache_put('checkDatabaseVersion_row_count', 0);
        $this->assertFalse($this->_app->checkDatabaseVersion(false));

        if ( isset($old_sugar_db_version) )
            $GLOBALS['sugar_db_version'] = $old_sugar_db_version;
        if ( isset($old_sugar_version) )
            $GLOBALS['sugar_version'] = $old_sugar_version;
    }

    public function testLoadLanguages()
    {
    	$this->_app->controller->module = 'Contacts';
    	$this->_app->loadLanguages();
    	//since there is a logged in user, the welcome screen should not be empty
    	$this->assertEmpty($GLOBALS['app_strings']['NTC_WELCOME'], 'Testing that Welcome message is not empty');
    	$this->assertNotEmpty($GLOBALS['app_strings'], "App Strings is not empty.");
    	$this->assertNotEmpty($GLOBALS['app_list_strings'], "App List Strings is not empty.");
    	$this->assertNotEmpty($GLOBALS['mod_strings'], "Mod Strings is not empty.");
    }

    public function testCheckHTTPRefererReturnsTrueIfRefererNotSet()
    {
        $_SERVER['HTTP_REFERER'] = '';
        $_SERVER['SERVER_NAME'] = 'dog';
        $this->_app->controller->action = 'index';

        $this->assertTrue($this->_app->checkHTTPReferer(false));
    }

    /**
     * @ticket 39691
     */
    public function testCheckHTTPRefererReturnsTrueIfRefererIsLocalhost()
    {
        $_SERVER['HTTP_REFERER'] = 'http://localhost';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $this->_app->controller->action = 'poo';

        $this->assertTrue($this->_app->checkHTTPReferer(false));

        $_SERVER['HTTP_REFERER'] = 'http://127.0.0.1';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $this->_app->controller->action = 'poo';

        $this->assertTrue($this->_app->checkHTTPReferer(false));

        $_SERVER['HTTP_REFERER'] = 'http://localhost';
        $_SERVER['SERVER_NAME'] = '127.0.0.1';
        $this->_app->controller->action = 'poo';

        $this->assertTrue($this->_app->checkHTTPReferer(false));

        $_SERVER['HTTP_REFERER'] = 'http://127.0.0.1';
        $_SERVER['SERVER_NAME'] = '127.0.0.1';
        $this->_app->controller->action = 'poo';

        $this->assertTrue($this->_app->checkHTTPReferer(false));
    }

    public function testCheckHTTPRefererReturnsTrueIfRefererIsServerName()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'dog';
        $this->_app->controller->action = 'index';

        $this->assertTrue($this->_app->checkHTTPReferer(false));
    }

    public function testCheckHTTPRefererReturnsTrueIfRefererIsInWhitelist()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'cat';
        $this->_app->controller->action = 'index';

        $GLOBALS['sugar_config']['http_referer']['list'][] = 'http://dog';

        $this->assertTrue($this->_app->checkHTTPReferer(false));
    }

    public function testCheckHTTPRefererReturnsFalseIfRefererIsNotInWhitelist()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'cat';
        $this->_app->controller->action = 'poo';

        $GLOBALS['sugar_config']['http_referer']['list'] = array();

        $this->assertFalse($this->_app->checkHTTPReferer(false));
    }

    public function testCheckHTTPRefererReturnsTrueIfRefererIsNotInWhitelistButActionIs()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'cat';
        $this->_app->controller->action = 'index';

        $this->assertTrue($this->_app->checkHTTPReferer(false));
    }

    public function testCheckHTTPRefererReturnsTrueIfRefererIsNotInWhitelistButActionIsInConfig()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'cat';
        $this->_app->controller->action = 'poo';

        $GLOBALS['sugar_config']['http_referer']['actions'][] = 'poo';
        $this->assertTrue($this->_app->checkHTTPReferer(false));
    }

    /**
     * @bug 50302
     */
    public function testWhitelistDefaults()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'cat';
        $GLOBALS['sugar_config']['http_referer']['actions'] = array('poo');
        $this->_app->controller->action = 'oauth';
        $this->assertTrue($this->_app->checkHTTPReferer(false));
        $this->_app->controller->action = 'index';
        $this->assertTrue($this->_app->checkHTTPReferer(false));
        $this->_app->controller->action = 'save';
        $this->assertFalse($this->_app->checkHTTPReferer(false));
    }

    /**
     * @group Login
     */
    public function testGetAuthenticatedUrl_DefaultShouldBeSidecar()
    {
        $appReflection = new ReflectionClass("SugarApplication");
        $method = $appReflection->getMethod('getAuthenticatedHomeUrl');
        $method->setAccessible(true);

        $url = $method->invoke($this->_app);

        $this->assertContains("index.php?action=sidecar#Home", $url);
    }

    /**
     * @group Login
     */
    public function testGetAuthenticatedUrl_AllowsDisablingOfSidecarWithUrlParameter()
    {
        $appReflection = new ReflectionClass("SugarApplication");
        $method = $appReflection->getMethod('getAuthenticatedHomeUrl');
        $method->setAccessible(true);
        
        $_GET['sidecar'] = '0';
        
        $url = $method->invoke($this->_app);

        $this->assertContains("index.php?module=Home&action=index", $url);
    }

    /**
     * @dataProvider providerGetLoginRedirect
     */
    public function testGetLoginRedirect($add_empty, $post_data, $result_query)
    {
        $appReflection = new ReflectionClass("SugarApplication");
        $method = $appReflection->getMethod('getLoginRedirect');
        $method->setAccessible(true);

        $_POST = $post_data;
        $url = $method->invoke($this->_app, $add_empty);

        $this->assertContains($result_query, $url);
    }

    function providerGetLoginRedirect() {
        return array(
            array(
                'add_empty' => true,
                'post_data' => array(
                    'login_module' => 'foo',
                    'login_action' => 'bar',
                ),
                'result_query' => 'index.php?module=foo&action=bar',
            ),
            array(
                'add_empty' => true,
                'post_data' => array(
                    'login_module' => 'foo',
                    'login_action' => '',
                ),
                'result_query' => 'index.php?module=foo&action=',
            ),
            array(
                'add_empty' => false,
                'post_data' => array(
                    'login_module' => 'foo',
                    'login_empty_value' => '',
                    'login_zero_value' => '0',
                ),
                'result_query' => 'index.php?module=foo&zero_value=0',
            ),
        );
    }

    /**
     * @dataProvider providerTestCreateLoginVars
     */
    public function testCreateLoginVars(array $request, $url, SugarController $controller = null)
    {
        $app = $this->getMockBuilder('SugarApplication')
            ->disableOriginalConstructor()
            ->setMethods(array('getRequestVars'))
            ->getMock();

        $app->expects($this->once())
            ->method('getRequestVars')
            ->will($this->returnValue($request));

        if ($controller) {
            SugarTestReflection::setProtectedValue($app, 'controller', $controller);
        }

        $this->assertSame($url, $app->createLoginVars());
    }

    public function providerTestCreateLoginVars()
    {
        return array(
            array(
                array(),
                '',
            ),
            array(
                array(
                    'csrf_token' => '123456',
                ),
                '',
            ),
            array(
                array(
                    'foo' => 'bar',
                ),
                '&login_foo=bar',
            ),
            array(
                array(
                    'foo' => 'bar',
                    'more' => 'beer',
                ),
                '&login_foo=bar&login_more=beer',
            ),
            array(
                array(
                    'foo' => 'bar',
                    'mobile' => '1',
                    'more' => 'beer',
                ),
                '&login_foo=bar&login_mobile=1&login_more=beer&mobile=1',
            ),
            array(
                array(
                    'foo' => 'bar',
                    'no_saml' => '1',
                    'more' => 'beer',
                ),
                '&login_foo=bar&login_no_saml=1&login_more=beer&no_saml=1',
            ),
            array(
                array(
                    'foo' => 'bar',
                    'csrf_token' => '123456',
                    'more' => 'beer',
                ),
                '&login_foo=bar&login_more=beer',
            ),
            array(
                array(
                    'foo' => 'bar',
                    'csrf_token' => '123456',
                    'more' => 'beer',
                ),
                '&login_foo=bar&login_more=beer',
                $this->createControllerMock(),
            ),
            array(
                array(
                    'foo' => 'bar',
                    'csrf_token' => '123456',
                    'more' => 'beer',
                ),
                '&login_foo=override&login_more=beer',
                $this->createControllerMock(array(
                    'foo' => 'override',
                )),
            ),
            array(
                array(
                    'foo' => 'bar',
                    'csrf_token' => '123456',
                    'mobile' => '1',
                    'more' => 'beer',
                    'no_saml' => '1',
                ),
                '&login_foo=override&login_mobile=1&login_more=beer&login_no_saml=false&mobile=1&no_saml=1',
                $this->createControllerMock(array(
                    'foo' => 'override',
                    'no_saml' => 'false',
                )),
            ),
        );
    }

    /**
     * Create SugarController mock with given public property values
     * @param array $properties Key/value pairs to set
     * @return SugarController
     */
    protected function createControllerMock(array $properties = array())
    {
        $controller = $this->createMock('SugarController');
        foreach ($properties as $property => $value) {
            $controller->$property = $value;
        }
        return $controller;
    }
}
