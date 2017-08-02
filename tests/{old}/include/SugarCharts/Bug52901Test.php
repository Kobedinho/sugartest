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


class Bug52901Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }


    /**
     * DataProvider function for test
     * @static
     * @return array
     */
    public static function dataFeed()
    {
        $dataSeed = array(
            // Accounts:
            // 1. type = '', industry = '';
            // 2. type = 'Bar', industry = 'Foo'
            'emptyTypeAndIndustry' => array(
                array(
                    '' =>
                    array(
                        '' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => '',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => '',
                        ),
                    ),
                    'Bar' =>
                    array(
                        'Foo' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Bar',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Foo',
                        ),
                    ),
                ),
                array('', 'Foo'),
                array('type', 'industry'),
                array('', 'Foo', '', 'Foo'),
            ),
            // Accounts:
            // 1. type = 'Foo', industry = 'Bar';
            // 2. type = 'Bar', industry = 'Foo'
            'bothDifferentTypeAndIndustry' => array(
                array(
                    'Foo' =>
                    array(
                        'Bar' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Foo',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Bar',
                        ),
                    ),
                    'Bar' =>
                    array(
                        'Foo' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Bar',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Foo',
                        ),
                    ),
                ),
                array('Foo', 'Bar'),
                array('type', 'industry'),
                array('Foo', 'Bar', 'Foo', 'Bar'),
            ),
            // Accounts:
            // 1. type = 'Foo', industry = 'Foo';
            // 2. type = 'Bar', industry = 'Bar'
            'bothEqualTypeAndIndustry' => array(
                array(
                    'Bar' =>
                    array(
                        'Bar' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Bar',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Bar',
                        ),
                    ),
                    'Foo' =>
                    array(
                        'Foo' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Foo',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Foo',
                        ),
                    ),
                ),
                array('Foo', 'Bar'),
                array('type', 'industry'),
                array('Foo', 'Bar', 'Foo', 'Bar'),
            ),
            // Accounts: Single group by. only by type
            // 1. type = 'Foo'
            // 2. type = 'Bar'
            'onlyByType' => array(
                array(
                    'Bar' =>
                    array(
                        'Bar' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Bar',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Bar',
                        ),
                    ),
                    'Foo' =>
                    array(
                        'Foo' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Foo',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Foo',
                        ),
                    ),
                ),
                array('Foo', 'Bar'),
                array('type'),
                // SFA-1466 needs subgroups
                array('Bar', 'Foo'),
            ),
        );

        return $dataSeed;
    }

    /**
     * Test that <subgroups> is filled properly
     *
     * @param $dataSet  array dataSet for JitReports
     * @param $superSet array super_set for JitReports
     * @param $groupBy array array of group_by levels
     * @param $expectedSubgroupNodesTitles array expected list of values of node <title> in each node <subgroups>
     *
     * @dataProvider dataFeed
     * @group 52901
     */
    public function testXMLIsGeneratedProperly($dataSet, $superSet, $groupBy, $expectedSubgroupNodesTitles)
    {
        $JR = new JitReports();
        $JR->setData($dataSet);
        $JR->super_set = $superSet;
        $JR->setDisplayProperty('thousands', false);
        $JR->group_by = $groupBy;

        // We do this because the function which is under the test (xmlDataReportChart()) returns XML without root node and thus causes XML parse error
        $actualXML = '<data>' . $JR->xmlDataReportChart() . '</data>';

        // Get the list of <title> node value elements of each <subgroup>
        $dom = new DomDocument();
        $dom->loadXML($actualXML);
        $xpath = new DomXPath($dom);
        $nodes = $xpath->query('group/subgroups/group/title');
        $actualSubgroupNodesTitlesArray = array();
        foreach ($nodes as $node)
        {
            $actualSubgroupNodesTitlesArray[] = $node->nodeValue;
        }

        $this->assertEquals($expectedSubgroupNodesTitles, $actualSubgroupNodesTitlesArray);
    }

}