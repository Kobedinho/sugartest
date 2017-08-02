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


class SugarWidgetFielddatetimeTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarWidgetFieldDateTime
     */
    private $widgetField;

    protected function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $layoutManager = new LayoutManager();
        $this->widgetField = new SugarWidgetFieldDateTime($layoutManager);
    }

    protected function tearDown()
    {
        unset($this->widgetField);

        SugarTestHelper::tearDown();
    }

    /**
     * Check if the returned data is formatted properly
     *
     * @param array $layout_def Layout def for the field
     * @param string $expected Expected value
     *
     * @dataProvider providerDisplayListweek
     */
    public function testDisplayListweek($layoutDef, $expected)
    {
        $display = $this->widgetField->displayListweek($layoutDef);

        $this->assertEquals($expected, $display);
    }

    /**
     * @return array ($layoutDef, $expected)
     */
    public static function providerDisplayListweek()
    {
        return array(
            array(
                array(
                    'name' => 'date_entered',
                    'column_function' => 'week',
                    'qualifier' => 'week',
                    'table_key' => 'self',
                    'table_alias' => 'opportunities',
                    'column_key' => 'self:date_entered',
                    'type' => 'datetime',
                    'fields' =>
                        array (
                            'OPPORTUNITIES_WEEK_DAT3634CE' => '2015-19',
                        ),
                ),
                'W19 2015'
            ),
        );
    }
}
