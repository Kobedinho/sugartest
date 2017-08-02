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


class CustomSugarChartFactoryTest extends Sugar_PHPUnit_Framework_TestCase {

public static function setUpBeforeClass()
{
    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
}

public static function tearDownAfterClass()
{
    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    unset($GLOBALS['current_user']);
}


public function setUp()
{

mkdir_recursive('custom/include/SugarCharts/CustomSugarChartFactory');

$the_string = <<<EOQ
<?php


class CustomSugarChartFactory extends JsChart {

	function __construct() {
		parent::__construct();
	}

	function getChartResources() {
		return '
		<link type="text/css" href="'.getJSPath('include/SugarCharts/Jit/css/base.css').'" rel="stylesheet" />
		<!--[if IE]><script language="javascript" type="text/javascript" src="'.getJSPath('include/SugarCharts/Jit/js/Jit/Extras/excanvas.js').'"></script><![endif]-->
		<script language="javascript" type="text/javascript" src="'.getJSPath('include/SugarCharts/Jit/js/Jit/jit.js').'"></script>
		<script language="javascript" type="text/javascript" src="'.getJSPath('include/SugarCharts/Jit/js/sugarCharts.js').'"></script>
		';
	}

	function getMySugarChartResources() {
		return '
		<script language="javascript" type="text/javascript" src="'.getJSPath('include/SugarCharts/Jit/js/mySugarCharts.js').'"></script>
		';
	}


	function display(\$name, \$xmlFile, \$width='320', \$height='480', \$resize=false) {

		parent::display(\$name, \$xmlFile, \$width, \$height, \$resize);

		return \$this->ss->fetch('include/SugarCharts/Jit/tpls/chart.tpl');
	}


	function getDashletScript(\$id,\$xmlFile="") {

		parent::getDashletScript(\$id,\$xmlFile);
		return \$this->ss->fetch('include/SugarCharts/Jit/tpls/DashletGenericChartScript.tpl');
	}

}

?>
EOQ;

    SugarAutoLoader::put('custom/include/SugarCharts/CustomSugarChartFactory/CustomSugarChartFactory.php', $the_string);
}

public function tearDown()
{
	rmdir_recursive('custom/include/SugarCharts/CustomSugarChartFactory');
	SugarAutoLoader::delFromMap('custom/include/SugarCharts/CustomSugarChartFactory', false);
}


public function testCustomFactory()
{
	$sugarChart = SugarChartFactory::getInstance('CustomSugarChartFactory');
	$name = get_class($sugarChart);
	$this->assertEquals('CustomSugarChartFactory', $name, 'Assert engine is CustomSugarChartFactory');
}

}
