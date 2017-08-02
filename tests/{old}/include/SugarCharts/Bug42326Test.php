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


class Bug42326Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $sugarChart;

	public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->sugarChart = SugarChartFactory::getInstance('Jit', 'Reports');
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * @dataProvider xmlDataBuilder
     */
    public function testStackedBarChartHasCorrectLabelJSON($xmldata, $expectedjson) {
        $json = $this->sugarChart->buildLabelsBarChart($xmldata);
        $this->assertSame($expectedjson, $json);
    }

    public function xmlDataBuilder() {
        $dataset = array(
            // check labels for regression of normal bar chart
            array('<?xml version="1.0" encoding="UTF-8"?><sugarcharts version="1.0"><data><group><title>Label1</title><value>4</value><label>4</label><subgroups></subgroups></group><group><title>Label2</title><value>3</value><label>3</label><subgroups></subgroups></group></data></sugarcharts>',
                  "\t\"label\": [\n\n\t\t\"Label1\"\n,\n\t\t\"Label2\"\n\n\t],\n\n",)
        );
        return $dataset;
    }
}

