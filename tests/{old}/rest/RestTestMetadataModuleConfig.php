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

require_once('tests/{old}/rest/RestTestBase.php');

class RestTestMetadataModuleConfig extends RestTestBase {
    protected $configs = array(
        array('name' => 'hello', 'value' => 'world', 'platform' => 'base', 'category' => 'Forecasts'),
    );

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');

        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');
        foreach($this->configs as $config){
            $admin->saveSetting($config['category'], $config['name'], $config['value'], $config['platform']);
        }

        parent::setUp();
        $this->_clearMetadataCache();
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();
        $db->query('DELETE FROM config where name = "hello" and value = "world"');
        $db->commit();

        parent::tearDown();
    }

    public function testMetaDataReturnsForecastsConfigs()
    {
        $restReply = $this->_restCall('metadata?typeFilter=&moduleFilter=Forecasts&platform=base');

        $this->assertArrayHasKey('hello', $restReply['reply']['modules']['Forecasts']['config']);
        $this->assertEquals('world', $restReply['reply']['modules']['Forecasts']['config']['hello']);
    }

    public function testMetaDataReturnEmptyConfigForModule()
    {
        $restReply = $this->_restCall('metadata?typeFilter=&moduleFilter=Accounts&platform=base');

        $this->assertEmpty($restReply['reply']['modules']['Accounts']['config']);
    }
}