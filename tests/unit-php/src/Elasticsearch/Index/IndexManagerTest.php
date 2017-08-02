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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Index;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexManager;
use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexManager
 *
 */
class IndexManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getIndexSettingsFromConfig
     * @dataProvider providerTestGetIndexSettingsFromConfig
     */
    public function testGetIndexSettingsFromConfig($index, $config, $output)
    {
        $indexManager = $this->getIndexManagerMock();
        TestReflection::setProtectedValue($indexManager, 'config', $config);
        $settings = TestReflection::callProtectedMethod($indexManager, 'getIndexSettingsFromConfig', array($index));
        $this->assertEquals($settings, $output);
    }

    public function providerTestGetIndexSettingsFromConfig()
    {
        return array(
            array(
                'index_foo',
                array(
                    'index_foo' => array (
                        'setting_A' => 'bar',
                        'setting_B' => 'fox',
                    ),
                    IndexManager::DEFAULT_INDEX_SETTINGS_KEY =>
                        array(
                            'index.mapping.ignore_malformed' => true,
                            'index.mapping.coerce' => true,
                        ),
                    'index_bar' => array(),
                ),
                array ('setting_A' => 'bar', 'setting_B' => 'fox')
            ),
            array(
                'index_foo',
                array(
                    IndexManager::DEFAULT_INDEX_SETTINGS_KEY =>
                        array(
                            'index.mapping.ignore_malformed' => true,
                            'index.mapping.coerce' => true,
                        ),
                    'index_bar' => array(),
                ),
                array(
                    'index.mapping.ignore_malformed' => true,
                    'index.mapping.coerce' => true,
                )
            ),
            array(
                'index_foo',
                array(
                    'index_foo' => array (
                        'setting_A' => 'bar',
                        'setting_B' => 'fox',
                        AnalysisBuilder::ANALYSIS => 'quick'
                    ),
                ),
                array ('setting_A' => 'bar', 'setting_B' => 'fox')
            ),
        );
    }

    /**
     * Get IndexManagerTest Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexManager
     */
    protected function getIndexManagerMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexManager')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
