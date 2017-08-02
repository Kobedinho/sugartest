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
require_once 'tests/{old}/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/2_RepairConnectorNames.php';

/**
 * Test for fixing the Connector name value (removing copyright) as well as 
 * validating and filtering what should be in a config
 */
class SugarUpgradeRepairConnectorNamesTest extends UpgradeTestCase
{
    protected $ug;

    public function setUp()
    {
        parent::setUp();
        $this->ug = new SugarUpgradeRepairConnectorNames($this->upgrader);
    }

    /**
     * Tests the cleansing of the actual name property
     * @param array $data
     * @param array $expect
     * @dataProvider getSanitizedConfigProvider
     */
    public function testGetSanitizedConfig($data, $expect)
    {
        $actual = $this->ug->getSanitizedConfig($data);
        $this->assertEquals($actual, $expect);
    }

    /**
     * Tests the cleansing of config params
     * @param array $config
     * @param array $base
     * @param array $expect
     * @dataProvider getSanitizedConfigParamsProvider
     */
    public function testGetSanitizedConfigParams($config, $base, $expect)
    {
        $actual = $this->ug->getSanitizedConfigParams($config, $base);
        $this->assertEquals($actual, $expect);
    }
    
    public function getSanitizedConfigProvider()
    {
        return array(
            // Test actual working case
            array(
                'data' => array(
                    'name' => 'Test&#169;',
                ),
                'expect' => array(
                    'name' => 'Test',
                ),
            ),
            // Test no change
            array(
                'data' => array(
                    'name' => 'Test',
                ),
                'expect' => array(
                    'name' => 'Test',
                ),
            ),
            // Test nothing to do
            array(
                'data' => array(
                    'foo' => 'bar',
                    'baz' => 'zim',
                ),
                'expect' => array(
                    'foo' => 'bar',
                    'baz' => 'zim',
                ),
            ),
        );
    }
    
    public function getSanitizedConfigParamsProvider()
    {
        return array(
            // Test straight across filtering of custom configs
            array(
                'config' => array(
                    'name' => 'Twitter',
                    'test' => 'Remove',
                    'eapm' => array(
                        'enabled' => true,
                        'foo' => 'bar',
                    ),
                    'order' => 5,
                    'properties' => array (
                        'oauth_consumer_key' => '',
                        'oauth_consumer_secret' => '',
                        'bad_index' => '',
                    ),
                ),
                'base' => array(
                    'name' => 'Twitter',
                    'eapm' => array(
                        'enabled' => true,
                    ),
                    'order' => 5,
                    'properties' => array (
                        'oauth_consumer_key' => '',
                        'oauth_consumer_secret' => '',
                    ),
                ),
                'expect' => array(
                    'name' => 'Twitter',
                    'eapm' => array(
                        'enabled' => true,
                    ),
                    'order' => 5,
                    'properties' => array (
                        'oauth_consumer_key' => '',
                        'oauth_consumer_secret' => '',
                    ),
                ),
            ),
            // Test maintaining custom values
            array(
                'config' => array(
                    'name' => 'Twitter',
                    'eapm' => array(
                        'enabled' => false,
                    ),
                    'order' => 20,
                    'properties' => array (
                        'oauth_consumer_key' => 'hex',
                        'oauth_consumer_secret' => 'box',
                        'bad_index' => '',
                    ),
                ),
                'base' => array(
                    'name' => 'Twitter',
                    'eapm' => array(
                        'enabled' => true,
                    ),
                    'order' => 5,
                    'properties' => array (
                        'oauth_consumer_key' => '',
                        'oauth_consumer_secret' => '',
                    ),
                ),
                'expect' => array(
                    'name' => 'Twitter',
                    'eapm' => array(
                        'enabled' => false,
                    ),
                    'order' => 20,
                    'properties' => array (
                        'oauth_consumer_key' => 'hex',
                        'oauth_consumer_secret' => 'box',
                    ),
                ),
            ),
        );
    }
}
