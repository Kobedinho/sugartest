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

if(!defined('sugarEntry')) define('sugarEntry', true);

set_include_path(
    dirname(__FILE__) . PATH_SEPARATOR .
    dirname(__FILE__) . '/..' . PATH_SEPARATOR .
    dirname(__FILE__) . '/../..' . PATH_SEPARATOR .
    get_include_path()
);

// constant to indicate that we are running tests
if (!defined('SUGAR_PHPUNIT_RUNNER'))
    define('SUGAR_PHPUNIT_RUNNER', true);

// prevent ext/session from trying to send headers, since it doesn't make sense in CLI mode
// and will conflict with PHPUnit output
ini_set('session.use_cookies', false);
session_cache_limiter(false);

// initialize the various globals we use
global $sugar_config, $db, $fileName, $current_user, $locale, $current_language;
if ( !isset($_SERVER['HTTP_USER_AGENT']) )
    // we are probably running tests from the command line
    $_SERVER['HTTP_USER_AGENT'] = 'cli';

// move current working directory
if (basename(getcwd()) == '{old}' || !is_file('include/entryPoint.php')) {
  chdir(dirname(__FILE__) . '/../..');
}

// this is needed so modules.php properly registers the modules globals, otherwise they
// end up defined in wrong scope
global $beanFiles, $beanList, $objectList, $moduleList, $modInvisList, $bwcModules, $sugar_version, $sugar_flavor;
require_once 'include/entryPoint.php';
require_once 'include/utils/layout_utils.php';

chdir(sugar_root_dir());

$GLOBALS['db'] = DBManagerFactory::getInstance();

$current_language = $sugar_config['default_language'];
// disable the SugarLogger
$sugar_config['logger']['level'] = 'fatal';

$GLOBALS['sugar_config']['default_permissions'] = array (
        'dir_mode' => 02770,
        'file_mode' => 0777,
        'chown' => '',
        'chgrp' => '',
    );

$GLOBALS['js_version_key'] = 'testrunner';

if ( !isset($_SERVER['SERVER_SOFTWARE']) )
    $_SERVER["SERVER_SOFTWARE"] = 'PHPUnit';

// helps silence the license checking when running unit tests.
$_SESSION['VALIDATION_EXPIRES_IN'] = 'valid';

$GLOBALS['startTime'] = microtime(true);

// clean out the cache directory
$repair = new RepairAndClear();
$repair->module_list = array();
$repair->show_output = false;
$repair->clearJsLangFiles();
$repair->clearJsFiles();

// make sure the client license has been validated
$license = new Administration();
$license = $license->retrieveSettings('license', true);
if ( !isset($license->settings['license_vk_end_date']))
    $license->saveSetting('license', 'vk_end_date', date('Y-m-d',strtotime('+1 year')));
// mark that we got by the admin wizard already
$focus = new Administration();
$focus->retrieveSettings();
$focus->saveSetting('system','adminwizard',1);

// include the other test tools
require_once 'SugarTestObjectUtilities.php';
require_once 'SugarTestProjectUtilities.php';
require_once 'SugarTestProjectTaskUtilities.php';
require_once 'SugarTestUserUtilities.php';
require_once 'SugarTestEmailAddressUtilities.php';
require_once 'SugarTestLangPackCreator.php';
require_once 'SugarTestThemeUtilities.php';
require_once 'SugarTestContactUtilities.php';
require_once 'SugarTestEmailUtilities.php';
require_once 'SugarTestCampaignUtilities.php';
require_once 'SugarTestLeadUtilities.php';
require_once 'SugarTestStudioUtilities.php';
require_once 'SugarTestMeetingUtilities.php';
require_once 'SugarTestCallUtilities.php';
require_once 'SugarTestAccountUtilities.php';
require_once 'SugarTestTrackerUtility.php';
require_once 'SugarTestImportUtilities.php';
require_once 'SugarTestMergeUtilities.php';
require_once 'SugarTestTaskUtilities.php';
require_once 'SugarTestCurrencyUtilities.php';
require_once 'SugarTestOpportunityUtilities.php';
require_once 'SugarTestJobQueueUtilities.php';
require_once 'SugarTestFilterUtilities.php';
require_once 'SugarTestRestUtilities.php';
require_once 'SugarTestActivityUtilities.php';
require_once 'SugarTestCommentUtilities.php';
require_once 'SugarTestReflection.php';
require_once 'SugarTestConfigUtilities.php';
require_once 'SugarTestDashboardUtilities.php';
require_once 'SugarTestNoteUtilities.php';

require_once 'SugarTestForecastUtilities.php';
require_once 'SugarTestProductUtilities.php';
require_once 'SugarTestRevenueLineItemUtilities.php';
require_once 'SugarTestProductCategoryUtilities.php';
require_once 'SugarTestProductTypeUtilities.php';
require_once 'SugarTestProductTemplatesUtilities.php';
require_once 'SugarTestProductBundleUtilities.php';
require_once 'SugarTestQuotaUtilities.php';
require_once 'SugarTestQuoteUtilities.php';
require_once 'SugarTestTeamUtilities.php';
require_once 'SugarTestTimePeriodUtilities.php';
require_once 'SugarTestWorksheetUtilities.php';
require_once 'SugarTestManagerWorksheetUtilities.php';
require_once 'SugarTestProspectUtilities.php';
require_once 'SugarTestProspectListsUtilities.php';
require_once 'SugarTestNotificationUtilities.php';
require_once 'SugarTestWebLogicHookUtilities.php';
require_once 'SugarTestRelationshipUtilities.php';
require_once 'SugarTestSugarEmailAddressUtilities.php';
require_once 'SugarTestCaseUtilities.php';
require_once 'SugarTestTagUtilities.php';
require_once 'SugarTestDatabaseMock.php';
require_once 'SugarTestKBContentUtilities.php';
require_once 'SugarTestSchedulersJobUtilities.php';
require_once 'SugarTestCategoryUtilities.php';

require_once 'SugarTestProcessAuthorUtilities.php';

// custom helper support
$customHelperIncludeFile = 'custom/tests/SugarTestHelperInclude.php';
if (file_exists($customHelperIncludeFile)) {
    require_once $customHelperIncludeFile;
}

$GLOBALS['db']->commit();

define('CHECK_FILE_MAPS', false);
// define our testcase subclass
if (function_exists("shadow_get_config") && ($sc = shadow_get_config()) != false && !empty($sc['template'])) {
    // shadow is enabled
    define('SHADOW_ENABLED', true);
    define('SHADOW_CHECK', false); // disable for faster tests
} else {
    define('SHADOW_ENABLED', false);
    define('SHADOW_CHECK', false);
}
class Sugar_PHPUnit_Framework_TestCase extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected $useOutputBuffering = true;

    protected $file_map;

    public static function getFiles()
    {
        $dir = realpath(dirname(__FILE__)."/../..");
        $files = `find $dir -name cache -prune -o -name custom -prune -o -name upload -prune -o -name tests -prune -o -name sugarcrm\\*.log -prune -o -type f -print | sort`;
        $flist = explode("\n", $files);
        sort($flist);

        return join("\n", $flist);
    }

    protected function assertPreConditions()
    {
        $GLOBALS['runningTest'] = $this->getName(false);
        $GLOBALS['runningTestClass'] = get_class($this);
        if (isset($GLOBALS['log'])) {
            $GLOBALS['log']->info("START TEST: {$this->getName(false)}");
        }
    }

    protected function assertPostConditions()
    {
        if (!empty($_REQUEST)) {
            foreach (array_keys($_REQUEST) as $k) {
                unset($_REQUEST[$k]);
            }
        }

        if (!empty($_POST)) {
            foreach (array_keys($_POST) as $k) {
                unset($_POST[$k]);
            }
        }

        if (!empty($_GET)) {
            foreach (array_keys($_GET) as $k) {
                unset($_GET[$k]);
            }
        }
        if (isset($GLOBALS['log'])) {
            $GLOBALS['log']->info("DONE TEST: {$this->getName(false)}");
        }
        // reset error handler in case somebody set it
        restore_error_handler();
    }

    public static function setUpBeforeClass()
   {
        SugarTestHelper::init();
   }

    public static function tearDownAfterClass()
    {
        unset($GLOBALS['disable_date_format']);
        SugarBean::resetOperations();
        $GLOBALS['timedate']->clearCache();
        if (constant('CHECK_FILE_MAPS')) {
            if (!self::compareArray(SugarAutoLoader::scanDir(""), SugarAutoLoader::$filemap)) {
                SugarAutoLoader::buildCache();
            }
        }
        SugarTestHelper::tearDown();
    }

    public static function compareArray($arr1, $arr2, $path = "")
    {
        if (!is_array($arr2)) {
            echo ("\nERROR[{$GLOBALS['runningTestClass']}:{$GLOBALS['runningTest']}]: Difference in ./{$path} - is file in map but should be directory!\n");
        }
        foreach ($arr2 as $key => $value) {
            $keypath = "{$path}$key/";
            if (in_array($keypath, SugarAutoLoader::$exclude)) {
                unset($arr1[$key]);
                continue;
            }
            if (!isset($arr1[$key])) {
                echo ("\nERROR[{$GLOBALS['runningTestClass']}:{$GLOBALS['runningTest']}]: Difference in {$path}$key - in map but not on disk\n");

                return false;
            }
            if (is_array($arr1[$key])) {
                if (!self::compareArray($arr1[$key], $arr2[$key], $keypath)) {
                    return false;
                }
            }
            unset($arr1[$key]);
        }
        foreach ($arr1 as $key => $value) {
            echo ("\nERROR[{$GLOBALS['runningTestClass']}:{$GLOBALS['runningTest']}]: Difference in {$path}$key - on disk but not in map!\n");

            return false;
        }

        return true;
    }

    protected function notRegexCallback($output)
    {
        $this->assertNotRegExp($this->_notRegex, $output);
    }

    public function expectOutputNotRegex($expectedRegex)
    {
        if (is_string($expectedRegex) || is_null($expectedRegex)) {
            $this->_notRegex = $expectedRegex;
        }

        $this->setOutputCallback(array($this, "notRegexCallback"));
    }

    public function runBare()
    {

        // Prevent the activity stream from creating messages.
        Activity::disable();
        if (SHADOW_CHECK && empty($this->file_map)) {
            $this->file_map = static::getFiles();
        }
        //track the original max execution time limit
        $originalMaxTime = ini_get('max_execution_time');

        parent::runBare();

        //sometimes individual tests change the max time execution limit, reset back to original
        set_time_limit($originalMaxTime);

        // clean up prepared statements
        $connection = DBManagerFactory::getConnection()->getWrappedConnection();
        SugarTestReflection::setProtectedValue($connection, 'statements', array());

        if (SHADOW_CHECK) {
            $oldfiles = $this->file_map;
            $this->file_map = static::getFiles();
            $this->assertEquals($oldfiles, $this->file_map);
        }
    }
}

/**
 * @deprecated Do not use this class, it will be removed soon
 * Use Sugar_PHPUnit_Framework_TestCase instead
 */
class Sugar_PHPUnit_Framework_OutputTestCase extends Sugar_PHPUnit_Framework_TestCase
{
}

// define a mock logger interface; used for capturing logging messages emited
// the test suite
class SugarMockLogger
{
    private $_messages = array();

    public function __call($method, $message)
    {
        $this->_messages[] = strtoupper($method) . ': ' . $message[0];
    }

    public function getMessages()
    {
        return $this->_messages;
    }

    public function getLastMessage()
    {
        return end($this->_messages);
    }

    public function getMessageCount()
    {
        return count($this->_messages);
    }
}

require_once 'ModuleInstall/ModuleInstaller.php';

/**
 * Own exception for SugarTestHelper class
 *
 * @author mgusev@sugarcrm.com
 */
class SugarTestHelperException extends PHPUnit_Framework_Exception
{

}

/**
 * Helper for initialization of global variables of SugarCRM
 *
 * @author mgusev@sugarcrm.com
 */
class SugarTestHelper
{
    /**
     * @var array array of registered vars. It allows helper to unregister them on tearDown
     */
    protected static $registeredVars = array();

    /**
     * @var array array of global vars. They are storing on init one time and restoring in global scope each tearDown
     */
    protected static $initVars = array(
        'GLOBALS' => array()
    );

    /**
     * @var array of system preference of SugarCRM as theme etc. They are storing on init one time and restoring each tearDown
     */
    protected static $systemVars = array();

    /**
     * @var array of modules which we should refresh on tearDown.
     */
    protected static $cleanModules = array();

    /**
     * @var array of modules and their custom fields created during setup.
     */
    protected static $customFields = array();

    /**
     * @var bool is SugarTestHelper inited or not. Just to skip initialization on the second and others call of init method
     */
    protected static $isInited = false;

    /**
     * All methods are static because of it we disable constructor
     */
    private function __construct()
    {
    }

    /**
     * All methods are static because of it we disable clone
     */
    private function __clone()
    {
    }

    /**
     * Initialization of main variables of SugarCRM in global scope
     *
     * @static
     */
    public static function init()
    {
        if (self::$isInited == true) {
            return true;
        }

        SugarCache::instance()->flush();
        SugarConfig::getInstance()->clearCache();

        // initialization & backup of sugar_config
        self::$initVars['GLOBALS']['sugar_config'] = null;
        if ($GLOBALS['sugar_config']) {
            self::$initVars['GLOBALS']['sugar_config'] = $GLOBALS['sugar_config'];
        }
        if (self::$initVars['GLOBALS']['sugar_config'] == false) {
            global $sugar_config;
            if (is_file('config.php')) {
                require_once 'config.php';
            }
            if (is_file('config_override.php')) {
                require_once 'config_override.php';
            }
            self::$initVars['GLOBALS']['sugar_config'] = $GLOBALS['sugar_config'];
        }

        // backup of current_language
        self::$initVars['GLOBALS']['current_language'] = 'en_us';
        if (isset($sugar_config['current_language'])) {
            self::$initVars['GLOBALS']['current_language'] = $sugar_config['current_language'];
        }
        if (isset($GLOBALS['current_language'])) {
            self::$initVars['GLOBALS']['current_language'] = $GLOBALS['current_language'];
        }
        $GLOBALS['current_language'] = self::$initVars['GLOBALS']['current_language'];

        // backup of reload_vardefs
        self::$initVars['GLOBALS']['reload_vardefs'] = null;
        if (isset($GLOBALS['reload_vardefs'])) {
            self::$initVars['GLOBALS']['reload_vardefs'] = $GLOBALS['reload_vardefs'];
        }

        // backup of locale
        self::$initVars['GLOBALS']['locale'] = null;
        if (isset($GLOBALS['locale'])) {
            self::$initVars['GLOBALS']['locale'] = $GLOBALS['locale'];
        }
        if (empty(self::$initVars['GLOBALS']['locale'])) {
            self::$initVars['GLOBALS']['locale'] = Localization::getObject();
        }

        // backup of service_object

        if (isset($GLOBALS['service_object'])) {
            self::$initVars['GLOBALS']['service_object'] = $GLOBALS['service_object'];
        }

        //Backup everything that could have been loaded in modules.php
        include 'include/modules.php';
        foreach(array('moduleList', 'beanList', 'beanFiles', 'bwcModules', 'modInvisList',
                      'objectList', 'modules_exempt_from_availability_check', 'adminOnlyList'
                     ) as $globVar)
        {
            $GLOBALS[$globVar] = $$globVar;
            self::$initVars['GLOBALS'][$globVar] = $GLOBALS[$globVar];
        }

        if (isset($GLOBALS['current_user'])) {
            self::$initVars['GLOBALS']['current_user'] = $GLOBALS['current_user'];
        }

        // backup of SugarThemeRegistry
        self::$systemVars['SugarThemeRegistry'] = SugarThemeRegistry::current();

        self::$isInited = true;
    }

    /**
     * Checking is there helper for variable or not
     *
     * @static
     * @param  string                   $varName name of global variable of SugarCRM
     * @return bool                     is there helper for a variable or not
     * @throws SugarTestHelperException fired when there is no implementation of helper for a variable
     */
    protected static function checkHelper($varName)
    {
        if (method_exists(__CLASS__, 'setUp_' . $varName) == false) {
            throw new SugarTestHelperException('setUp for $' . $varName . ' is not implemented. ' . __CLASS__ . '::setUp_' . $varName);
        }
    }

    /**
     * Entry point for setup of global variable
     *
     * @static
     * @param  string $varName name of global variable of SugarCRM
     * @param  array  $params  some parameters for helper. For example for $mod_strings or $current_user
     * @return bool   is variable setuped or not
     */
    public static function setUp($varName, $params = array())
    {
        self::init();
        self::checkHelper($varName);

        return call_user_func(__CLASS__ . '::setUp_' . $varName, $params);
    }

    /**
     * Clean up all registered variables and restore $initVars and $systemVars
     * @static
     * @return bool status of tearDown
     */
    public static function tearDown()
    {
        self::init();

        // Handle current_user placing on the end since there are some things
        // that need current user for the clean up
        if (isset(self::$registeredVars['current_user'])) {
            $cu = self::$registeredVars['current_user'];
            unset(self::$registeredVars['current_user']);
            self::$registeredVars['current_user'] = $cu;
        }

        // unregister variables in reverse order in order to have dependencies unregistered after dependants
        $unregisterVars = array_reverse(self::$registeredVars);
        foreach ($unregisterVars as $varName => $isCalled) {
            if ($isCalled) {
                unset(self::$registeredVars[$varName]);
                if (method_exists(__CLASS__, 'tearDown_' . $varName)) {
                    call_user_func(__CLASS__ . '::tearDown_' . $varName, array());
                } elseif (isset($GLOBALS[$varName])) {
                    unset($GLOBALS[$varName]);
                }
            }
        }

        // Restoring of system variables
        foreach (self::$initVars as $scope => $vars) {
            foreach ($vars as $name => $value) {
                $GLOBALS[$name] = $value;
            }
        }

        // Restore the activity stream.
        Activity::enable();

        // Restoring of theme
        SugarThemeRegistry::set(self::$systemVars['SugarThemeRegistry']->dirName);
        SugarCache::$isCacheReset = false;

        SugarConfig::getInstance()->clearCache();
        \TimeDate::getInstance()->allow_cache = true;

        return true;
    }

    /**
     * Registration of $current_user in global scope
     *
     * @static
     * @param  array $params parameters for SugarTestUserUtilities::createAnonymousUser method
     * @return bool  is variable setuped or not
     */
    protected static function setUp_current_user(array $params, $register = true)
    {
        if ($register) {
            self::$registeredVars['current_user'] = true;
        }
        $GLOBALS['current_user'] = call_user_func_array('SugarTestUserUtilities::createAnonymousUser', $params);

        return $GLOBALS['current_user'];
    }

    /**
     * Removal of $current_user from global scope
     *
     * @static
     * @return bool is variable removed or not
     */
    protected static function tearDown_current_user()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        return true;
    }

    /**
     * Registration of $beanList in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_beanList($params = array(), $register = true)
    {
        if ($register) {
            self::$registeredVars['beanList'] = true;
        }
        global $beanList;
        require 'include/modules.php';

        return true;
    }

    /**
     * Registration of $beanFiles in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_beanFiles($params = array(), $register = true)
    {
        if ($register) {
            self::$registeredVars['beanFiles'] = true;
        }
        global $beanFiles;
        require 'include/modules.php';

        return true;
    }

    /**
     * Registration of $bwcModules in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_bwcModules($params = array(), $register = true)
    {
        if ($register) {
            self::$registeredVars['bwcModules'] = true;
        }
        global $bwcModules;
        require 'include/modules.php';

        return true;
    }

    /**
     * Registration of $moduleList in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_moduleList($params = array(), $register = true)
    {
        if ($register) {
            self::$registeredVars['moduleList'] = true;
        }
        global $moduleList;
        require 'include/modules.php';

        return true;
    }

    /**
     * Reinitialization of $moduleList in global scope because we can't unset that variable
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function tearDown_moduleList()
    {
        return self::setUp_moduleList();
    }

    /**
     * Registration of $modListHeader in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_modListHeader()
    {
        self::$registeredVars['modListHeader'] = true;
        if (isset($GLOBALS['current_user']) == false) {
            self::setUp_current_user(array(
                true,
                1
            ));
        }
        $GLOBALS['modListHeader'] = query_module_access_list($GLOBALS['current_user']);

        return true;
    }

    /**
     * Registration of $app_strings in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_app_strings()
    {
        self::$registeredVars['app_strings'] = true;
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);

        return true;
    }

    /**
     * Setup the mock db helper,
     *
     * @param DBManager $mock
     * @return SugarTestDatabaseMock
     *
     * @deprecated
     */
    protected static function setUp_mock_db($mock = null)
    {
        if (!$mock) {
            $mock = new SugarTestDatabaseMock();
        }

        // as far as we mock the global object but don't know how to mock Doctrine connection,
        // leave it unmocked
        $doctrineConnection = DBManagerFactory::getConnection();
        SugarTestReflection::setProtectedValue($mock, 'conn', $doctrineConnection);

        self::$systemVars['db'] = DBManagerFactory::$instances;
        self::$registeredVars['mock_db'] = $mock;
        DBManagerFactory::$instances = array('' => $mock);

        return $mock;
    }

    /**
     * @deprecated
     */
    protected static function tearDown_mock_db()
    {
        DBManagerFactory::$instances = self::$systemVars['db'];
        unset(self::$systemVars['db'], self::$registeredVars['mock_db']);
    }

    /**
     * Registration of $app_list_strings in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_app_list_strings()
    {
        self::$registeredVars['app_list_strings'] = true;
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        return true;
    }

    /**
     * Registration of $timedate in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_timedate($params = array(), $register = true)
    {
        if ($register) {
            self::$registeredVars['timedate'] = true;
        }
        $GLOBALS['timedate'] = TimeDate::getInstance();

        return true;
    }

    /**
     * Removal of $timedate from global scope
     *
     * @static
     * @return bool is variable removed or not
     */
    protected static function tearDown_timedate()
    {
        $GLOBALS['timedate']->clearCache();
        $GLOBALS['timedate']->allow_cache = true;

        return true;
    }

    /**
     * Registration of $mod_strings in global scope
     *
     * @static
     * @param  array $params parameters for return_module_language function
     * @return bool  is variable setuped or not
     */
    protected static function setUp_mod_strings(array $params)
    {
        self::$registeredVars['mod_strings'] = true;
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], $params[0]);

        return true;
    }

    /**
     * Registration of $dictionary in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_dictionary()
    {
        self::setUp('beanFiles');
        self::setUp('beanList');
        self::setUp('bwcModules');
        self::$registeredVars['dictionary'] = true;

        global $dictionary;
        $dictionary = array();
        $moduleInstaller = new ModuleInstaller();
        $moduleInstaller->silent = true;
        $moduleInstaller->rebuild_tabledictionary();
        require 'modules/TableDictionary.php';

        foreach ($GLOBALS['beanList'] as $k => $v) {
            VardefManager::loadVardef($k, BeanFactory::getObjectName($k));
        }

        return true;
    }

    /**
     * Create custom field
     *
     * @static
     * @param array $params Array containing module name and field vardefs
     *
     * @return TemplateField
     * @throws SugarTestHelperException
     */
    protected static function setUp_custom_field(array $params)
    {
        self::$registeredVars['custom_field'] = true;

        if (count($params) < 2) {
            throw new SugarTestHelperException(sprintf(
                '%s requires 2 parameters, %d given',
                __METHOD__,
                count($params)
            ));
        }

        list($module, $vardefs) = $params;
        if (!isset($vardefs['type'])) {
            throw new SugarTestHelperException('Field type is not specified');
        }

        $field = get_widget($vardefs['type']);
        foreach ($vardefs as $param => $value) {
            $field->{$param} = $value;
        }

        $bean = BeanFactory::newBean($module);
        if (!$bean) {
            throw new SugarTestHelperException(sprintf(
                '%s is not a valid module name',
                $module
            ));
        }

        $dynamicField = new DynamicField($module);
        $dynamicField->setup($bean);
        $field->save($dynamicField);

        $mi = new ModuleInstaller();
        $mi->silent = true;
        $mi->rebuild_vardefs();

        self::$customFields[] = array($dynamicField, $field);

        $objectName = BeanFactory::getObjectName($module);
        VardefManager::loadVardef($module, $objectName, true);

        if (!empty($vardefs['formula'])) {
            foreach (VardefManager::getLinkedModulesFromFormula($bean, $vardefs['formula']) as $m => $_) {
                if ($objectName = BeanFactory::getObjectName($m)) {
                    $mi->rebuild_dependencies();
                    VardefManager::loadVardef($m, $objectName, true);
                };
            }
        }

        return $field;
    }

    /**
     * Removal of custom fields
     *
     * @static
     */
    protected static function tearDown_custom_field()
    {
        $mi = new ModuleInstaller();
        $mi->silent = true;

        foreach (self::$customFields as $data) {
            list($dynamicField, $field) = $data;
            $vardefs = $field->get_field_def();
            $field->delete($dynamicField);
            $mi->rebuild_vardefs();
            if (!empty($vardefs['formula'])) {
                $bean = $dynamicField->bean;
                foreach (VardefManager::getLinkedModulesFromFormula($bean, $vardefs['formula']) as $m => $_) {
                    if ($objectName = BeanFactory::getObjectName($m)) {
                        $mi->rebuild_dependencies();
                        VardefManager::loadVardef($m, $objectName, true);
                    };
                }
            }
            $module = $dynamicField->module;
            $objectName = BeanFactory::getObjectName($module);
            VardefManager::loadVardef($module, $objectName, true);
        }

        self::$customFields = array();
    }

    const NOFILE_DATA = '__NO_FILE__';
    public static $oldFiles;
    public static $oldDirs;
    /**
     * Setup file preserving hooks
     */
    protected static function setUp_files()
    {
        self::$oldFiles = array();
        self::$oldDirs = array();
        self::$registeredVars['files'] = true;
    }

    /**
     * Preserve a file
     */
    public static function saveFile($filename)
    {
        if (is_array($filename)) {
            foreach ($filename as $file) {
                self::saveFile($file);
            }

            return;
        }
        if ( file_exists($filename) ) {
            self::$oldFiles[$filename] = file_get_contents($filename);
        } else {
            self::$oldFiles[$filename] = self::NOFILE_DATA;
        }
    }

    public static function ensureDir($dirname)
    {
        if (is_array($dirname)) {
            foreach ($dirname as $dir) {
                self::ensureDir($dir);
            }

            return;
        }
        $parts = explode("/", $dirname);
        while (!empty($parts)) {
            $path = implode("/", $parts);
            if (!is_dir($path)) {
                self::$oldDirs[] = $path;
            }
            array_pop($parts);
        }
        if (!is_dir($dirname)) {
            SugarAutoLoader::ensureDir($dirname);
        }
    }

    /**
     * Restore files to previous state
     */
    protected static function tearDown_files()
    {
        foreach (self::$oldFiles as $filename => $filecontents) {
            if (SHADOW_ENABLED) {
                if (substr($filename, 0, 7) != 'custom/' && substr($filename, 0, 6) != 'cache/' && $filename != 'config_override.php' && file_exists($filename)) {
                    // Delete shadow files always
                    @SugarAutoLoader::unlink($filename, false);
                    if(file_exists($filename)) {
                        // still have it in map if it exists in template
                        SugarAutoLoader::addToMap($filename, false);
                    }
                    continue;
                }
            }
            if ($filecontents == self::NOFILE_DATA) {
                if ( file_exists($filename) ) {
                    SugarAutoLoader::unlink($filename, false);
                }
            } else {
                file_put_contents($filename,$filecontents);
                SugarAutoLoader::addToMap($filename, false);
            }
        }
        rsort(self::$oldDirs);
        foreach (self::$oldDirs as $dirname) {
            if (file_exists($dirname)) {
                rmdir($dirname);
                SugarAutoLoader::delFromMap($dirname, false);
            }
        }
        SugarAutoLoader::saveMap();
    }

    /**
     *
     * @var ACLAction
     */
    static public $aclAction;

    protected static function setUp_ACLStatic()
    {
        self::$aclAction = BeanFactory::newBean('ACLActions');
    }

    protected static function tearDown_ACLStatic()
    {
        self::$aclAction->clearACLCache();
    }

    public static function clearACLCache()
    {
        self::$aclAction->clearACLCache();
    }


    /**
     * Reinitialization of $dictionary in global scope because we can't unset that variable
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function tearDown_dictionary()
    {
        return self::setUp_dictionary();
    }

    /**
     * Cleaning caches and refreshing vardefs
     *
     * @static
     * @param  string $lhs_module left module from relation
     * @param  string $rhs_module right module from relation
     * @return bool   are caches refreshed or not
     */
    protected static function setUp_relation(array $params)
    {
        if (empty($params[0]) || empty($params[1])) {
            throw new SugarTestHelperException('setUp("relation") requires two parameters');
        }
        list($lhs_module, $rhs_module) = $params;
        self::$registeredVars['relation'] = true;
        self::$cleanModules[] = $lhs_module;

        LanguageManager::clearLanguageCache($lhs_module);
        if ($lhs_module != $rhs_module) {
            self::$cleanModules[] = $rhs_module;
            LanguageManager::clearLanguageCache($rhs_module);
        }

        self::setUp('dictionary');

        VardefManager::$linkFields = array();
        VardefManager::clearVardef();
        VardefManager::refreshVardefs($lhs_module, BeanFactory::getObjectName($lhs_module));
        if ($lhs_module != $rhs_module) {
            VardefManager::refreshVardefs($rhs_module, BeanFactory::getObjectName($rhs_module));
        }
        SugarRelationshipFactory::rebuildCache();

        return true;
    }

    /**
     * Doing the same things like setUp but for initialized list of modules
     *
     * @static
     * @return bool are caches refreshed or not
     */
    protected static function tearDown_relation()
    {
        SugarRelationshipFactory::deleteCache();

        $modules = array_unique(self::$cleanModules);
        foreach ($modules as $module) {
            LanguageManager::clearLanguageCache($module);
        }

        self::tearDown('dictionary');

        VardefManager::$linkFields = array();
        VardefManager::clearVardef();
        foreach ($modules as $module) {
            VardefManager::refreshVardefs($module, BeanFactory::getBeanClass($module));
        }
        SugarRelationshipFactory::rebuildCache();

        self::$cleanModules = array();

        return true;
    }

    protected static function setUp_theme()
    {
        self::$registeredVars['theme'] = true;
    }

    protected static function tearDown_theme()
    {
        SugarTestThemeUtilities::removeAllCreatedAnonymousThemes();
    }

    /**
     * Registration of $modInvisList in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_modInvisList($params = array(), $register = true)
    {
        if ($register) {
            self::$registeredVars['modInvisList'] = true;
        }
        global $modInvisList;
        require 'include/modules.php';

        return true;
    }

    /**
     * Reinitialization of $modInvisList in global scope because we can't unset that variable
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function tearDown_modInvisList()
    {
        return self::setUp_modInvisList();
    }
}
