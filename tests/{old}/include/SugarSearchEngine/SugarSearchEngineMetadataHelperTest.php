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




class SugarSearchEngineMetadataHelperTest extends Sugar_PHPUnit_Framework_TestCase
{

    private $_cacheRenamed;
    private $_cacheFile;
    private $_backupCacheFile;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');

        $this->_cacheFile = sugar_cached('modules/ftsModulesCache.php');
        $this->_backupCacheFile = sugar_cached('modules/ftsModulesCache.php').'.save';

        if (file_exists($this->_cacheFile))
        {
            $this->_cacheRenamed = true;
            rename($this->_cacheFile, $this->_backupCacheFile);
        }
        else
        {
            $this->_cacheRenamed = false;
        }
    }

    public function tearDown()
    {
        if ($this->_cacheRenamed)
        {
            if (file_exists($this->_backupCacheFile))
            {
                rename($this->_backupCacheFile, $this->_cacheFile);
            }
        }
        else if (file_exists($this->_cacheFile))
        {
            unlink($this->_cacheFile);
        }
        SugarTestHelper::tearDown();
    }

    public function testGetFtsSearchFields()
    {
        $ftsFields = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsPerModule('Accounts');
        $this->assertContains('name', array_keys($ftsFields));
        $this->assertArrayHasKey('name', $ftsFields['name'], 'name key not found');

        //Pass in a sugar bean for the test
        $account = BeanFactory::getBean('Accounts', null);
        $ftsFields = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsPerModule($account);
        $this->assertContains('name', array_keys($ftsFields));
    }


    public function testGetFtsSearchFieldsForAllModules()
    {
        $ftsFieldsByModule = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsForAllModules();
        $this->assertContains('Contacts', array_keys($ftsFieldsByModule));
        $this->assertContains('first_name', array_keys($ftsFieldsByModule['Contacts']));
    }


    public function isModuleEnabledProvider()
    {
        return array(
            array('Contacts', true),
            array('BadModule', false),
            array('Notifications', false),
        );
    }

    /**
     * @dataProvider isModuleEnabledProvider
     */
    public function testIsModuleFtsEnabled($module,$actualResult)
    {
        $expected = SugarSearchEngineMetadataHelper::isModuleFtsEnabled($module);
        $this->assertEquals($expected, $actualResult);
    }

    public function testClearCache()
    {
        // testing clearCache() is dangerous because various UnifiedSearchAdvanced
        // methods depend on the cache values. So, to prevent other tests failing
        // due to us clearing the cache, let's preserve the cache values for
        // every key we're going to clear, and then restore them when we're
        // done.
        $preTestCacheValues = array();
        SugarSearchEngineMetadataHelper::getUserEnabledFTSModules(); // populates the cache.
        $usa = new UnifiedSearchAdvanced();
        $list = $usa->retrieveEnabledAndDisabledModules();
        foreach ($list as $modules) {
            foreach ($modules as $module) {
                $cacheKey = SugarSearchEngineMetadataHelper::FTS_FIELDS_CACHE_KEY_PREFIX . $module['module'];
                $preTestCacheValues[$cacheKey] = sugar_cache_retrieve($cacheKey);
            }
        }

        SugarSearchEngineMetadataHelper::clearCache();

        foreach ($list as $modules) {
            foreach ($modules as $module) {
                $cacheKey = SugarSearchEngineMetadataHelper::FTS_FIELDS_CACHE_KEY_PREFIX . $module['module'];
                $cacheValue = sugar_cache_retrieve($cacheKey);
                $errorMsg = "Cache value for module {$module['module']} is not empty after clearCache().";
                $this->assertTrue(empty($cacheValue), $errorMsg);
            }
        }

        foreach ($preTestCacheValues as $key => $value) {
            sugar_cache_put($key, $value);
        }
    }
}
