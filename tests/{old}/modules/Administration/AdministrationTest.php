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

class AdministrationTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $configs = array(
        array('name' => 'AdministrationTest', 'value' => 'Base', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'AdministrationTest', 'value' => 'Portal', 'platform' => 'portal', 'category' => 'Forecasts'),
        array('name' => 'AdministrationTest', 'value' => '["Portal"]', 'platform' => 'json', 'category' => 'Forecasts'),
    );

    public static function setUpBeforeClass()
    {
        sugar_cache_clear('admin_settings_cache');
    }

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('moduleList');
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config where name = 'AdministrationTest'");
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');
        foreach($this->configs as $config){
            $admin->saveSetting($config['category'], $config['name'], $config['value'], $config['platform']);
        }
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config where name = 'AdministrationTest'");
        $db->commit();
    }

    public function testRetrieveSettingsByInvalidModuleReturnsEmptyArray()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('InvalidModule', 'base');

        $this->assertEmpty($results);
    }

    public function testRetrieveSettingsByValidModuleWithPlatformReturnsOneRow()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertTrue(count($results) > 0);
    }

    public function testRetrieveSettingsByValidModuleWithPlatformOverRidesBasePlatform()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'portal');

        $this->assertEquals('Portal', $results['AdministrationTest']);
    }

    public function testCacheExist()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertNotEmpty(sugar_cache_retrieve("ModuleConfig-Forecasts"));
    }

    public function testCacheSameAsReturn()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertSame($results, sugar_cache_retrieve("ModuleConfig-Forecasts"));
    }

    public function testCacheClearedAfterSave()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $admin->saveSetting("Forecasts", "AdministrationTest", "testCacheClearedAfterSave", "base");

        $this->assertEmpty(sugar_cache_retrieve("ModuleConfig-Forecasts"));
    }

    public function testJsonValueIsArray()
    {
         /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'json');

        $this->assertEquals(array("Portal"), $results['AdministrationTest']);
    }

    /**
     * @dataProvider configValueIntegrityProvider
     */
    public function testConfigValueIntegrity($value, $expected)
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting('PHPUnit', 'Test', $value, 'base');
        $config = $admin->getConfigForModule('PHPUnit', 'base', true);
        $this->assertSame($expected, $config['Test']);
    }

    /**
     * @return array
     */
    public function configValueIntegrityProvider()
    {
        return array(
            array('A', 'A'), // simple string
            array('A\\B', 'A\\B'), // slashes
            array('Русский', 'Русский'), // unicode
            array('7.0', '7.0'), // simple number
            array('7.0.0', '7.0.0'),
            array(7, 7),      // integer
            array(array('portal'), array('portal')), // indexed array
            array(array('foo' => 'bar'), array('foo' => 'bar')), // associative array
            array('"value1"', '"value1"'), // quoted string
            array(array(2 => '"val"ue2'), array(2 => '"val"ue2')), // array with quoted string
        );
    }
}
