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


class SugarChartFactoryTest extends Sugar_PHPUnit_Framework_TestCase
{
    var $engine;

	public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        global $sugar_config;
        if(!empty($sugar_config['chartEngine']))
        {
            $this->engine = $sugar_config['chartEngine'];
        }
    }

    public function tearDown()
    {
        if(!empty($this->engine))
        {
            global $sugar_config;
            $sugar_config['chartEngine'] = $this->engine;
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testChartFactoryDefault()
    {
        $sugarChart = SugarChartFactory::getInstance();
        $name = get_class($sugarChart);
        $this->assertEquals('nvd3', $name, 'Assert chart engine defaults to nvd3');
    }

    public function testChartFactoryJit()
    {
        $sugarChart = SugarChartFactory::getInstance('Jit');
        $name = get_class($sugarChart);
        $this->assertEquals('Jit', $name, 'Assert engine is Jit');

        $sugarChart = SugarChartFactory::getInstance('Jit', 'Reports');
        $name = get_class($sugarChart);
        $this->assertEquals('JitReports', $name, 'Assert chart engine is JitReport');
    }

    public function testChartFactoryNvd3()
    {
        $sugarChart = SugarChartFactory::getInstance('nvd3');
        $name = get_class($sugarChart);
        $this->assertEquals('nvd3', $name, 'Assert engine is nvd3');

        $sugarChart = SugarChartFactory::getInstance('nvd3', 'Reports');
        $name = get_class($sugarChart);
        $this->assertEquals('nvd3Reports', $name, 'Assert chart engine is nvd3Reports');
    }

    public function testConfigChartFactory()
    {
        global $sugar_config;
        $sugar_config['chartEngine'] = 'Jit';
        $sugarChart = SugarChartFactory::getInstance();
        $name = get_class($sugarChart);
        $this->assertEquals('Jit', $name, 'Assert chart engine set in global sugar_config is correct');
    }
}
