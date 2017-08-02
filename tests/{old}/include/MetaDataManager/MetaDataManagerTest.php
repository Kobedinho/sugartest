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

class MetaDataManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $mm;
    protected $mdc;
    protected $configBackup;

    public function setup()
    {
        SugarTestHelper::setup('beanFiles');
        SugarTestHelper::setup('beanList');
        SugarTestHelper::setup('current_user', array(true, true));
        SugarTestHelper::setup('files');

        // Backup current language settings so manipulation can be tested
        $this->configBackup['languages'] = $GLOBALS['sugar_config']['languages'];
        if (isset($GLOBALS['sugar_config']['disabled_languages'])) {
            $this->configBackup['disabled_languages'] = $GLOBALS['sugar_config']['disabled_languages'];
        }

        $this->setTestLanguageSettings();
        $this->mm = MetaDataManager::getManager();
        $this->mdc = new MetaDataCache(DBManagerFactory::getInstance());
    }

    public function tearDown()
    {
        MetaDataManager::enableCache();

        // Restore changed config stuff
        $GLOBALS['sugar_config']['languages'] = $this->configBackup['languages'];
        if (isset($this->configBackup['disabled_languages'])) {
            $GLOBALS['sugar_config']['disabled_languages'] = $this->configBackup['disabled_languages'];
        }

        MetaDataFiles::clearModuleClientCache();
        $this->mdc->reset();
        MetaDataManager::resetManagers();
        SugarTestHelper::tearDown();
    }

    public function testGetAllLanguages()
    {

        $languages = $this->mm->getAllLanguages();

        $this->assertArrayHasKey('enabled', $languages, "Enabled languages is missing.");
        $this->assertArrayHasKey('disabled', $languages, "Disabled languages is missing.");
        $this->assertNotEmpty($languages['enabled'], "Enabled languages is empty.");
        $this->assertNotEmpty($languages['disabled'], "Disabled languages is empty");

        // Test content of each list
        $this->assertArrayHasKey(2, $languages['enabled'], "Missing element of enabled languages");
        $this->assertArrayHasKey(1, $languages['disabled'], "Missing element of disabled languages");
        $this->assertEquals('snazzy', $languages['enabled'][2], "Incorrect value for disabled language 2");
        $this->assertEquals('br_ikea', $languages['disabled'][1], "Incorrect value for disabled language 1");
    }

    /**
     * This is a functional test rather than a unit test.
     * This is due to MetaDataManager and MetaDataFiles not having
     * any tests.
     *
     * This test covers two scenarios,
     * the first one is if there exists no data, than we should expect
     * the metadata to not pick up any controllers.
     *
     * The second scenario covers metadata manager picking up on
     * provided collection / model controllers.
     */
    public function testFinalMetadataJSSource()
    {

        // Scenario 1
        // Create empty module with correct metadata structure.
        sugar_mkdir("modules/TestModule/clients/base/datas/model", 0700, true);

        $moduleMeta = $this->mm->getModuleDatas('TestModule');

        // We verify our assumptions that we should have an empty set of metadata.
        $this->assertArrayHasKey("_hash", $moduleMeta, "Metadata does not contain a hash");
        $this->assertEquals(count($moduleMeta), 1, "Metadata has incorrect amount of elements");

        // Clear our metadata cache.
        MetaDataFiles::clearModuleClientCache("TestModule");

        // Scenario 2
        // Add a model controller to our datas directory.
        SugarAutoLoader::touch("modules/TestModule/clients/base/datas/model/model.js");
        $moduleMeta = $this->mm->getModuleDatas('TestModule');

        // We now verify if we have additional controller metadata in our return.
        $this->assertArrayHasKey("model", $moduleMeta, "Metadata does not contain a controller");
        $this->assertEquals(count($moduleMeta), 2, "Metadata doesn't include the controller");

        // Clean up our test.
        MetaDataFiles::clearModuleClientCache("TestModule");
        SugarAutoLoader::unlink("modules/TestModule/clients/base/datas/model/model.js");
        rmdir_recursive("modules/TestModule/");
        SugarAutoLoader::buildCache();
    }

    protected function setTestLanguageSettings()
    {
        $GLOBALS['sugar_config']['languages'] = array(
            'br_test' => 'Test Language',
            'br_mine' => 'My Language',
            'snazzy' => 'Snazzy Language',
            'whiskey' => 'Whiskey Language',
            'awesome' => 'Awesome Sauce',
            'br_ikea' => 'Ikead an idea',
        );

        $GLOBALS['sugar_config']['disabled_languages'] = "whiskey,br_ikea";
    }

    public function testGetConfigs()
    {
        $sugarConfig = array(
            'list_max_entries_per_page' => 1,
            'max_record_fetch_size' => 2,
            'mass_actions' => array(
                'mass_update_chunk_size' => 3,
                'not_on_white_list' => 4,
            ),
            'analytics' => array(
                'enabled' => true,
            ),
        );

        $expectedConfigs = array(
            'maxQueryResult' => 1,
            'maxRecordFetchSize' => 2,
            'massActions' => array(
                'massUpdateChunkSize' => 3,
            ),
            'analytics' => array(
                'enabled' => true,
            ),
            'inboundEmailCaseSubjectMacro' => '[CASE:%1]',
        );

        $administration = new Administration();
        $administration->retrieveSettings();
        if (!empty($administration->settings['system_name'])) {
            $expectedConfigs['systemName'] = $administration->settings['system_name'];
        }
    
        $manager = $this->createPartialMock('MetadataManagerMock', array('getSugarConfig'));
        $manager->expects($this->any())
            ->method('getSugarConfig')
            ->will($this->returnValue($sugarConfig));

        // Get the configs from metadata manager
        $actualConfigs = $manager->getConfigs();

        // Test that connectors are part of the config array
        $this->assertArrayHasKey('connectors', $actualConfigs);
        $this->assertNotEmpty($actualConfigs['connectors']);

        // Remove connectors from the configs now, since testing data that could
        // change seems sorta not proper
        unset($actualConfigs['connectors']);

        // Run the actual config test
        $this->assertEquals($expectedConfigs, $actualConfigs);
    }

    public function testNormalizeMetadata()
    {
        // Test data, to be used for testing both mobile and base
        $data = array(
            'modules' => array(
                'Accounts' => array(
                    'menu' => true,
                    'views' => array(
                        'record' => true,
                    ),
                    'layouts' => array(
                        'record' => true,
                    ),
                ),
            ),
        );

        // Test base first, which should be equality
        $mm = MetaDataManager::getManager();
        $test = $mm->normalizeMetadata($data);
        $this->assertEquals($test, $data, "Base data was manipulated and it should not have been");

        $mm = MetaDataManager::getManager('mobile');
        $test = $mm->normalizeMetadata($data);
        $this->assertNotEquals($test, $data, "Mobile metadata was not manipulated and it should have been");
        $this->assertFalse(isset($test['modules']['Accounts']['menu']));
        $this->assertEmpty($test['modules']['Accounts']['views']);
        $this->assertEmpty($test['modules']['Accounts']['layouts']);
    }

    public function testGetAppListStrings()
    {
        $mm = MetaDataManager::getManager();
        $normalList = $mm->getAppListStrings('en_us');
        $tupleList = $mm->getAppListStrings('en_us', true);

        //Would be nice to mock the app_list_strings, but this currently isn't possible with return_app_list_strings_language
        $this->assertEquals(
            $normalList['checkbox_dom'],
            array(
                '' => '',
                '1' => 'Yes',
                '2' => 'No',
            )
        );

        $this->assertEquals(
            $tupleList['checkbox_dom'],
            array(
                array('', ''),
                array('1', 'Yes'),
                array('2', 'No'),
            )
        );
    }

    public function getLanguageDataProvider()
    {
        return array(
            array(
                array(
                    'lang' => 'en_us',
                    'ordered' => true
                )
            ),
            array(
                array(
                    'lang' => 'en_us',
                    'ordered' => false
                )
            )
        );
    }

    /**
     * @group BR-1730
     * @group unit
     * @dataProvider getLanguageDataProvider
     */
    public function testGetLanguage($params)
    {
        $manager = $this->getMockBuilder('MetaDataManager')
            ->disableOriginalConstructor()->setMethods(array('getAppListStrings', 'getLangUrl'))->getMock();
        //Skipping the constructor requires we set up the db ourselves
        $manager->db = DBManagerFactory::getInstance();

        $manager->expects($this->once())->method('getAppListStrings')
            ->with($params['lang'], $params['ordered'])->will($this->returnValue(array()));

        $fileName = md5(microtime());
        SugarAutoLoader::delFromMap($fileName, false);

        $manager->expects($this->exactly(3))->method('getLangUrl')
            ->with($params['lang'], $params['ordered'])->will($this->returnValue($fileName));

        $manager->getLanguage($params);
    }

    /**
     * @dataProvider providerTestGetModuleView
     * @covers MetaDataManager::getModuleView
     * @group unit
     */
    public function testGetModuleView($module, $view, $metadata, $expected)
    {
        $mm = $this->getMockBuilder('MetaDataManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getModuleViews'))
            ->getMock();

        $mm->expects($this->once())
            ->method('getModuleViews')
            ->with($this->equalTo($module))
            ->will($this->returnValue($metadata));

        $this->assertEquals($expected, $mm->getModuleView($module, $view));
    }

    public function providerTestGetModuleView()
    {
        return array(
            // existing view
            array(
                'Accounts',
                'record',
                array('record' => array('foo', 'bar')),
                array('foo', 'bar'),
            ),
            // non-existing view
            array(
                'Accounts',
                'blaat',
                array('record' => array('foo', 'bar')),
                array(),
            ),
        );
    }

    /**
     * @dataProvider providerTestGetModuleViewFields
     * @covers MetaDataManager::getModuleViewFields
     * @covers MetaDataManager::getFieldNames
     * @group unit
     */
    public function testGetModuleViewFields($module, $view, $viewData, $expectedFields, $expectedDisplayParams)
    {
        /** @var MetaDataManager|PHPUnit_Framework_MockObject_MockObject $mm */
        $mm = $this->getMockBuilder('MetaDataManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getModuleView'))
            ->getMock();

        $mm->expects($this->once())
            ->method('getModuleView')
            ->with($this->equalTo($module), $this->equalTo($view))
            ->will($this->returnValue($viewData));

        $fields = $mm->getModuleViewFields($module, $view, $displayParams);
        $this->assertEquals($expectedFields, $fields);
        $this->assertEquals($expectedDisplayParams, $displayParams);
    }

    public function providerTestGetModuleViewFields()
    {
        return array(
            // empty view data
            array(
                'Contacts',
                'record',
                array(),
                array(),
                array(),
            ),
            // real view data
            array(
                'Contacts',
                'record',
                array(
                    'meta' => array(
                        'panels' => array(
                            array(
                                'fields' => array(

                                    // string based field def
                                    'first_name',

                                    // array based field def
                                    array(
                                        'name' => 'last_name',
                                    ),

                                    // link field
                                    array(
                                        'name' => 'tasks',
                                        'fields' => array('id', 'date_due'),
                                        'order_by' => 'date_due:desc',
                                    ),

                                    // array based invalid field
                                    array(
                                        'span',
                                    ),

                                    // non-string/array invalid field
                                    69,

                                    // nested field set
                                    array(
                                        'name' => 'primary_address',
                                        'fields' => array(
                                            'street',
                                            array(
                                                'name' => 'country',
                                            ),
                                        ),
                                    ),

                                    // anonymous nested field set
                                    array(
                                        'fields' => array(
                                            'foo',
                                            array(
                                                'name' => 'bar',
                                            ),

                                            // link field inside field set
                                            array(
                                                'name' => 'opportunities',
                                                'fields' => array('id', 'name'),
                                            ),
                                        ),
                                    ),

                                    // related field set
                                    array(
                                        'related_fields' => array(
                                            array(
                                                'name' => 'good',
                                            ),
                                            'karma',

                                            // link field inside related fields
                                            array(
                                                'name' => 'bugs',
                                            ),
                                        )
                                    ),
                                    // link field as string
                                    'calls',
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'first_name',
                    'last_name',
                    'tasks',
                    'primary_address',
                    'street',
                    'country',
                    'foo',
                    'bar',
                    'opportunities',
                    'good',
                    'karma',
                    'bugs',
                    'calls',
                ),
                array(
                    'first_name' => array(),
                    'last_name' => array(),
                    'tasks' => array(
                        'fields' => array('id', 'date_due'),
                        'order_by' => 'date_due:desc',
                    ),
                    'street' => array(),
                    'country' => array(),
                    'primary_address' => array(),
                    'foo' => array(),
                    'bar' => array(),
                    'opportunities' => array(
                        'fields' => array('id', 'name'),
                    ),
                    'good' => array(),
                    'karma' => array(),
                    'bugs' => array(),
                    'calls' => array(),
                ),
            ),
        );
    }

    public function testGetPlatformList()
    {
        SugarTestHelper::saveFile('custom/clients/platforms.php');
        SugarAutoLoader::ensureDir('custom/clients');

        $contents = <<<PLATFORMS
<?php
\$platforms[] = 'metadata-manager-test';
PLATFORMS;
        SugarAutoLoader::put('custom/clients/platforms.php', $contents);

        SugarTestHelper::saveFile('custom/application/Ext/Platforms/platforms.ext.php');
        SugarAutoLoader::ensureDir('custom/application/Ext/Platforms');

        $contents = <<<PLATFORMS
<?php
\$platforms[] = 'extension-platform';
PLATFORMS;
        SugarAutoLoader::put('custom/application/Ext/Platforms/platforms.ext.php', $contents);

        $platforms = MetaDataManager::getPlatformList();
        $this->assertContains('base', $platforms);
        $this->assertContains('mobile', $platforms);
        $this->assertContains('portal', $platforms);
        $this->assertContains('metadata-manager-test', $platforms);
        $this->assertContains('extension-platform', $platforms);
    }

    /**
     * @param array $input
     * @param array $expected
     *
     * @dataProvider removeDisabledFieldsProvider
     */
    public function testRemoveDisabledFields($input, $expected)
    {
        $mm = new MetaDataManager();
        $actual = SugarTestReflection::callProtectedMethod($mm, 'removeDisabledFields', array($input));
        $this->assertSame($actual, $expected);
    }

    public static function removeDisabledFieldsProvider()
    {
        return array(
            array(
                array(
                    'some-arbitrary-structure' => array(
                        'fields' => array(
                            array(
                                'name' => 'f1',
                                'enabled' => true,
                            ),
                            array(
                                'name' => 'f2',
                                'enabled' => false,
                            ),
                            array(
                                'name' => 'f3',
                            ),
                            'f4',
                        ),
                    ),
                ),
                array(
                    'some-arbitrary-structure' => array(
                        'fields' => array(
                            array(
                                'name' => 'f1',
                                'enabled' => true,
                            ),
                            array(
                                'name' => 'f3',
                            ),
                            'f4',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider cacheStaticProvider
     */
    public function testEnableCacheStatic($method, array $arguments, $dbMethod)
    {
        $db = $this->getCacheEnabledDatabaseMock($dbMethod);
        $this->callCacheStatic($db, $method, $arguments);
    }

    /**
     * @dataProvider cacheStaticProvider
     */
    public function testDisableCacheStatic($method, array $arguments, $dbMethod)
    {
        MetaDataManager::disableCache();
        $db = $this->getCacheDisabledDatabaseMock($dbMethod);
        $this->callCacheStatic($db, $method, $arguments);
    }

    private function callCacheStatic(DBManager $db, $method, array $arguments)
    {
        SugarTestHelper::setUp('mock_db', $db);
        SugarTestReflection::callProtectedMethod('MetaDataManager', $method, $arguments);
    }

    public static function cacheStaticProvider()
    {
        return array(
            array('getPlatformsWithCachesInDatabase', array('some-key'), 'getConnection'),
        );
    }

    private function getCacheEnabledDatabaseMock($method)
    {
        $stmt = $this->getMockBuilder('\Doctrine\DBAL\Statement')
            ->disableOriginalConstructor()
            ->setMethods(array('fetchAll'))
            ->getMock();
        $stmt->expects($this->atLeastOnce())
            ->method('fetchAll')
            ->willReturn(array());

        $conn = $this->getMockBuilder('\Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->setMethods(array('executeQuery'))
            ->getMock();
        $conn->expects($this->atLeastOnce())
            ->method('executeQuery')
            ->willReturn($stmt);

        $db = $this->getAbstractDbManagerMock($method);
        $db->expects($this->atLeastOnce())
            ->method($method)
            ->willReturn($conn);

        return $db;
    }

    private function getCacheDisabledDatabaseMock($method)
    {
        $db = $this->getAbstractDbManagerMock($method);
        $db->expects($this->never())
            ->method($method);

        return $db;
    }

    protected function getAbstractDbManagerMock($method)
    {
        return $this->getMockBuilder('DBManager')
            ->setMethods(array($method))
            ->getMockForAbstractClass();
    }

    /**
     * @dataProvider getPlatformsWithCachesInFilesystemProvider
     */
    public function testGetPlatformsWithCachesInFilesystem($fileName, $platformName)
    {
        $dir = 'cache/api/metadata';
        SugarTestHelper::saveFile($dir . '/' . $fileName);

        SugarAutoLoader::ensureDir($dir);
        SugarAutoLoader::put($dir . '/' . $fileName, '');

        $platforms = SugarTestReflection::callProtectedMethod('MetaDataManager', 'getPlatformsWithCachesInFilesystem');
        $this->assertContains($platformName, $platforms);
    }

    public static function getPlatformsWithCachesInFilesystemProvider()
    {
        return array(
            array(
                'en_us_test_base_public_ordered.json',
                'base'
            ),
            array(
                'en_us_test_portal_public.json',
                'portal'
            ),
            array(
                'metadata_test_mobile_private.php',
                'mobile'
            )
        );
    }

    /**
     * @dataProvider getPlatformsWithCachesInDatabaseProvider
     */
    public function testGetPlatformsWithCachesInDatabase($key, $expected)
    {
        $this->assertNotEmpty($expected);
        $this->mdc->set($key, true);

        $platforms = SugarTestReflection::callProtectedMethod('MetaDataManager', 'getPlatformsWithCachesInDatabase');
        foreach ($expected as $platform) {
            $this->assertContains($platform, $platforms);
        }
    }

    public static function getPlatformsWithCachesInDatabaseProvider()
    {
        return array(
            array(
                'meta_hash_public_base',
                array(
                    'base' => 'base',
                ),
            ),
            array(
                'meta_hash_base_mobile',
                array(
                    'base' => 'base',
                    'mobile' => 'mobile',
                ),
            ),
        );
    }

    /**
     * @dataProvider getEditableDropdownFilterProvider
     */
    public function testGetEditableDropdownFilter($filter, $defaults, $expected) {
        global $app_list_strings;


        $app_list_strings['md_fix_filter_test'] = $defaults;

        $mock = $this->createPartialMock('MetaDataManager', array('getRawFilter'));
        $mock->expects($this->any())->method('getRawFilter')->willReturn($filter);
        $actual = $mock->getEditableDropdownFilter('md_fix_filter_test', 'foo');

        $this->assertEquals($expected, $actual);
        //Also assert that the JSON version will encode correctly. php equivalent is not enough
        $this->assertEquals(json_encode($expected), json_encode($actual));

        unset($app_list_strings['md_fix_filter_test']);

    }

    public static function getEditableDropdownFilterProvider()
    {
        return array(
            //Arrays with numeric keys
            array(
                array(),
                array(
                    '01' => 'one',
                    '02' => 'two',
                    '10' => 'ten',
                ),
                array(
                    '01' => true,
                    '02' => true,
                    '10' => true,
                )
            ),
            //Non-empty filter should not contain new values by default
            //nor entries that were in the filter but not the default
            array(
                array(
                    'a' => false,
                    'b' => true,
                    'c' => true,
                ),
                array(
                    'a' => 'A',
                    'c' => 'C',
                    'd' => 'D',
                ),
                array(
                    'a' => false,
                    'c' => true,
                    'd' => false,
                )
            ),
            //Order should be preserved from the filter
            array(
                array(
                    'b' => true,
                    'a' => true,
                    'c' => false,
                ),
                array(
                    'a' => 'A',
                    'b' => 'B',
                    'c' => 'C',
                ),
                array(
                    'b' => true,
                    'a' => true,
                    'c' => false,
                )
            ),
        );
    }

}

class MetadataManagerMock extends MetaDataManager
{
    public function getConfigs()
    {
        return parent::getConfigs();
    }
}
