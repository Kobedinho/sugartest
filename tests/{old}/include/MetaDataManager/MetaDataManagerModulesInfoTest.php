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


class MetaDataManagerModulesInfoTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var TabController Instance of TabController
     */
    static protected $tabs;

    /**
     * @var array Test set of tabs for base app
     */
    static protected $testSystemTabs = array(
        'Accounts' => 'Accounts',
        'Contacts' => 'Contacts',
        'Leads' => 'Leads',
        'Opportunities' => 'Opportunities',
        'Cases' => 'Cases',
        'Bugs' => 'Bugs'
    );

    /**
     * @var array Test set of tabs for portal
     */
    static protected $testPortalTabs = array(
        'Contacts' => 'Contacts',
        'Cases' => 'Cases',
        'Bugs' => 'Bugs'
    );

    /**
     * @var array Store current system tabs to backup later
     */
    static protected $savedSystemTabs;

    /**
     * @var array Store current portal tabs to backup later
     */
    static protected $savedPortalTabs;

    /**
     * @var string Location of the mobile tabs metadata file
     */
    static protected $mobileMetaFile = 'include/MVC/Controller/wireless_module_registry.php';

    /**
     * @var string Location of the custom mobile tabs metadata file
     */
    static protected $customMobileMetaFile = 'custom/include/MVC/Controller/wireless_module_registry.php';

    /**
     * @var bool Flag to indicate if the mobile custom file already exists
     */
    static protected $mobileBackedUp = false;

    /**
     * @var bool Flag to indicate if the mobile custom path exists
     */
    static protected $mobileCustomPathExists = true;

    /**
     * @var string Path that is created for test purpose.
     */
    static protected $mobileCreatedPath;

    /**
     * Set up once before all tests are run
     */
    static public function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user', array(true, 1));

        self::$tabs = new TabController();

        // Save current system tabs and set test system tabs
        self::$savedSystemTabs = self::$tabs->get_system_tabs();
        self::$tabs->set_system_tabs(self::$testSystemTabs);

        // Save current portal tabs and set test portal tabs
        self::$savedPortalTabs = self::$tabs->getPortalTabs();
        self::$tabs->setPortalTabs(array_keys(self::$testPortalTabs));

        self::setUpMobile();
    }

    /**
     * Tear down once after all tests are run
     */
    static public function tearDownAfterClass()
    {
        // Reset saved system tabs
        self::$tabs->set_system_tabs(self::$savedSystemTabs);

        // Reset saved portal tabs
        self::$tabs->setPortalTabs(self::$savedPortalTabs);

        self::tearDownMobile();

        SugarTestHelper::tearDown();
    }

    /**
     * Test getModulesInfo method for the base app
     *
     * @group MetaDataManager
     */
    public function testBaseGetModulesInfo()
    {
        global $moduleList, $modInvisList;

        $fullModuleList = array_merge($moduleList, $modInvisList);

        // Run the test
        $mm = $this->getMockBuilder('MetaDataManager')
            ->setMethods(array('getModulesData'))
            ->getMock();
        $mm->expects($this->any())
            ->method('getModulesData')
            ->will($this->returnValue(array(
                'Accounts' => array(
                    'menu' => array(
                        'quickcreate' => array(
                            'meta' => array(
                                'visible' => false,
                            ),
                        ),
                    ),
                ),
                'Cases' => array(
                    'menu' => array(
                        'quickcreate' => array(
                            'meta' => array(
                                'visible' => true,
                            ),
                        ),
                    ),
                ),
            )));
        $expectedTabs = array_keys(self::$testSystemTabs);
        $expectedSubpanels = SubPanelDefinitions::get_all_subpanels();

        $modulesInfo = $mm->getModulesInfo();

        foreach ($fullModuleList as $module) {
            //Do not check modules that are ignored via ACL's
            if (!isset($modulesInfo[$module])) {
                continue;
            }
            // Test visible
            if (in_array($module, $moduleList)) {
                $this->assertTrue($modulesInfo[$module]['visible'], $module . ' should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['visible'], $module . ' should be hidden');
            }

            // Test tabs
            if (in_array($module, $expectedTabs)) {
                $this->assertTrue($modulesInfo[$module]['display_tab'], $module . ' tab should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['display_tab'], $module . ' tab should be hidden');
            }

            // Test subpanels
            if (in_array(strtolower($module), $expectedSubpanels)) {
                $this->assertTrue($modulesInfo[$module]['show_subpanels'], $module . ' subpanels should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['show_subpanels'], $module . ' subpanels should be hidden');
            }

            // Test quickcreate
            if ($module === 'Cases') {
                $this->assertTrue($modulesInfo[$module]['quick_create'], $module . ' shortcut should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['quick_create'], $module . ' shortcut should be hidden');
            }
        }
    }

    /**
     * Test getModulesInfo method for portal
     *
     * @group MetaDataManager
     */
    public function testPortalGetModulesInfo()
    {
        $browser = new SugarPortalBrowser();
        $browser->loadModules();

        // Run the test
        $mm = $this->getMockBuilder('MetaDataManager')
            ->setMethods(array('getModulesData'))
            ->getMock();
        $mm->expects($this->any())
            ->method('getModulesData')
            ->will($this->returnValue(array(
                'Bugs' => array(
                    'menu' => array(
                        'quickcreate' => array(
                            'meta' => array(
                                'visible' => true,
                            ),
                        ),
                    ),
                ),
                'Cases' => array(
                    'menu' => array(
                        'quickcreate' => array(
                            'meta' => array(
                                'visible' => false,
                            ),
                        ),
                    ),
                ),
            )));
        $expectedTabs = array_keys(self::$testPortalTabs);
        $expectedSubpanels = SubPanelDefinitions::get_all_subpanels();

        $modulesInfo = $mm->getModulesInfo();

        foreach ($browser->modules as $module => $moduleData) {
            // Test tabs
            if (in_array($module, $expectedTabs)) {
                $this->assertTrue($modulesInfo[$module]['display_tab'], $module . ' tab should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['display_tab'], $module . ' tab should be hidden');
            }

            // Test subpanels
            if (in_array(strtolower($module), $expectedSubpanels)) {
                $this->assertTrue($modulesInfo[$module]['show_subpanels'], $module . ' subpanels should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['show_subpanels'], $module . ' subpanels should be visible');
            }

            // Test quickcreate
            if ($module === 'Bugs') {
                $this->assertTrue($modulesInfo[$module]['quick_create'], $module . ' shortcut should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['quick_create'], $module . ' shortcut should be hidden');
            }
        }
    }

    /**
     * Test getModulesInfo method for mobile
     *
     * @group MetaDataManager
     */
    public function testMobileGetModulesInfo()
    {

        // Run the test
        $mm = new MetaDataManagerMobile();
        $fullModuleList = $mm->getFullModuleList();
        $defaultEnabledModuleList = $mm->getDefaultEnabledModuleList();
        $expectedTabs = array_keys(self::$testSystemTabs);
        $expectedSubpanels = SubPanelDefinitions::get_all_subpanels();

        $modulesInfo = $mm->getModulesInfo();

        foreach ($fullModuleList as $module) {
            // Test tabs
            if (in_array($module, $defaultEnabledModuleList)) {
                $this->assertFalse($modulesInfo[$module]['display_tab'], $module . ' tab should be hidden');
            } else {
                $this->assertTrue($modulesInfo[$module]['display_tab'], $module . ' tab should be visible');
            }

            // Test subpanels
            if (in_array(strtolower($module), $expectedSubpanels)) {
                $this->assertTrue($modulesInfo[$module]['show_subpanels'], $module . ' subpanels should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['show_subpanels'], $module . ' subpanels should be hidden');
            }

            // Test quickcreate
            if ($module === 'Cases' || $module === 'Contacts') {
                $this->assertTrue($modulesInfo[$module]['quick_create'], $module . ' shortcut should be visible');
            } else {
                $this->assertFalse($modulesInfo[$module]['quick_create'], $module . ' shortcut should be visible');
            }
        }
    }

    static protected function setUpMobile()
    {
        if (file_exists(self::$customMobileMetaFile)) {
            // Backup the custom file if there is one
            self::$mobileBackedUp = true;
            rename(self::$customMobileMetaFile, self::$customMobileMetaFile . '.backup');
        } else if (!is_dir(dirname(self::$customMobileMetaFile))) {
            // If the custom path does not exist, we are gonna find the first
            // non existing folder of this path, se we can clean up later
            self::$mobileCustomPathExists = false;
            $customFolders = explode('/', dirname(self::$customMobileMetaFile));
            self::$mobileCreatedPath = '';
            foreach ($customFolders as $folder) {
                if (!empty(self::$mobileCreatedPath)) {
                    self::$mobileCreatedPath .= '/';
                }
                self::$mobileCreatedPath .= $folder;
                if (!is_dir(self::$mobileCreatedPath)) {
                    // This path does not exist. We'll have to start cleaning up
                    // from here.
                    break;
                }
            }
        }

        // Create a custom `wireless_module_registry.php` file
        // Module list must match self::$testSystemTabs
        $testFileContents = <<<EOF
<?php
\$wireless_module_registry = array(
	'Accounts' => array('disable_create' => true),
	'Contacts' => array(),
	'Leads' => array('disable_create' => true),
	'Opportunities' => array('disable_create' => true),
	'Cases' => array(),
	'Bugs' => array('disable_create' => true),
);
EOF;
        // If no custom file, need to create custom directory
        if (!self::$mobileBackedUp) {
            $filename = create_custom_directory(self::$mobileMetaFile);
        }

        // Create the custom file
        file_put_contents(self::$customMobileMetaFile, $testFileContents);
        SugarAutoLoader::addToMap(self::$customMobileMetaFile);

    }

    static protected function tearDownMobile()
    {
        // Reset backed-up custom file
        if (self::$mobileBackedUp) {
            rename(self::$customMobileMetaFile . '.backup', self::$customMobileMetaFile);
        } else {
            // Clean up custom path
            if (self::$mobileCustomPathExists) {
                unlink(self::$customMobileMetaFile);
            } else {
                rmdir_recursive(self::$mobileCreatedPath);
            }
            SugarAutoLoader::delFromMap(self::$customMobileMetaFile);
        }
    }
}

