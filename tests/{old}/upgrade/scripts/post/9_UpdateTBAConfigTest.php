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
require_once 'upgrade/scripts/post/9_UpdateTBAConfig.php';

class SugarUpgradeUpdateTBAConfigTest extends UpgradeTestCase
{
    /**
     * @var TeamBasedACLConfigurator
     */
    private $tbaConfig;

    /**
     * @var array
     */
    private $globalEnabledModules;

    /**
     * @var boolean
     */
    private $globalTBA;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user', [true, true]);
        $this->tbaConfig = $this->getMock(
            'TeamBasedACLConfigurator',
            ['applyTBA', 'restoreTBA', 'fallbackTBA', 'applyFallback', 'getListOfPublicTBAModules']
        );
        $config = new Configurator();
        $this->globalTBA = $config->config[TeamBasedACLConfigurator::CONFIG_KEY]['enabled'];
        $this->globalEnabledModules = $config->config[TeamBasedACLConfigurator::CONFIG_KEY]['enabled_modules'];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        // Important to have a fresh object.
        $config = new Configurator();

        // Configurator does not override a list without unsetting it.
        $config->config[TeamBasedACLConfigurator::CONFIG_KEY]['enabled_modules'] = false;
        $config->handleOverride();
        $config->config[TeamBasedACLConfigurator::CONFIG_KEY]['enabled_modules'] = $this->globalEnabledModules;
        $config->handleOverride();
        $config->clearCache();
        SugarConfig::getInstance()->clearCache();

        $this->tbaConfig->setGlobal($this->globalTBA);

        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Test that previously enabled modules appear in the `enabled_modules` configuration key.
     * Should work even if TBA is disabled.
     */
    public function testMigrateEnabledModulesToNewConfig()
    {
        $availableModules = [
            'Calls',
            'Leads',
        ];
        $disabledModules = [
            'Leads',
        ];
        $expectedEnabledModules = [
            'Calls',
        ];

        // Old format.
        $config = new Configurator();
        $config->config[TeamBasedACLConfigurator::CONFIG_KEY]['disabled_modules'] = false;
        $config->handleOverride();
        $config->config[TeamBasedACLConfigurator::CONFIG_KEY]['disabled_modules'] = $disabledModules;
        $config->handleOverride();

        $this->tbaConfig->expects($this->any())
            ->method('getListOfPublicTBAModules')
            ->will($this->returnValue($availableModules));

        $scriptMock = $this->getMock(
            'SugarUpgradeUpdateTBAConfig',
            ['getTBAConfigurator'],
            [$this->getMockForAbstractClass('UpgradeDriver')]
        );
        $scriptMock->expects($this->any())
            ->method('getTBAConfigurator')
            ->will($this->returnValue($this->tbaConfig));

        $this->tbaConfig->setGlobal(false);

        $scriptMock->from_version = '7.8.0.0.RC.3';
        $scriptMock->to_version = '7.8.0.0';
        $scriptMock->run();

        $actualConfig = $this->tbaConfig->getConfig();

        $this->assertEquals($expectedEnabledModules, $actualConfig['enabled_modules']);
        $this->assertEquals(false, $actualConfig['disabled_modules']);
    }
}
