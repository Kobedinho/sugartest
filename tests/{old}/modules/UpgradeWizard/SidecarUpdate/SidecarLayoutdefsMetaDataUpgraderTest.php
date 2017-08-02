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


class SidecarLayoutdefsMetaDataUpgraderTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SidecarLayoutdefsMetaDataUpgraderMock
     */
    protected $upgrader;

    /**
     * @var string
     */
    protected $module = 'Accounts';

    /**
     * @var array
     */
    protected $subpanelData = array();

    public function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));

        $this->upgrader = new SidecarLayoutdefsMetaDataUpgraderMock(
            new SidecarMetaDataUpgrader(),
            array('module' => $this->module)
        );

        $this->subpanelData = array(
            'test_module' => array(
                'module'        => 'module_that_doesnt_exist',
                'subpanel_name' => 'default',
                'title_key'     => 'LBL_TEST_TITLE_0',
            ),
            'test_relationship' => array(
                'module'            => $this->module,
                'subpanel_name'     => 'default',
                'title_key'         => 'LBL_TEST_TITLE_1',
                'get_subpanel_data' => 'unexisting_relationships',
            ),
            'test_function' => array(
                'module'            => $this->module,
                'subpanel_name'     => 'default',
                'title_key'         => 'LBL_TEST_TITLE_2',
                'get_subpanel_data' => 'function:global_function',
            ),
            'test_correct_subpanel' => array(
                'module'        => $this->module,
                'subpanel_name' => 'default',
                'title_key'     => 'LBL_TEST_TITLE_3',
            ),
        );
        $this->upgrader->loadSubpanelData($this->module, $this->subpanelData);
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @dataProvider convertSubpanelDataProvider
     */
    public function testConvertSubpanelData($key, $expected)
    {
        $this->upgrader->setLegacyViewdefs(array($key => $this->subpanelData[$key]));
        $this->upgrader->convertLegacyViewDefsToSidecar();

        $actual = $this->upgrader->getSidecarViewDefs();
        $this->assertEquals($expected, $actual);
    }

    public function convertSubpanelDataProvider()
    {
        return array(
            array(
                'test_module',
                array(),
            ), // Test subpanel with link to non-existing module should not be converted
            array(
                'test_function',
                array(),
            ), // Test get_subpanel_data with "function" should not be converted
            array(
                'test_relationship',
                array(),
            ), // Test get_subpanel_data with non-existing relationship should not be converted
            array(
                'test_correct_subpanel',
                array(
                    'label'  => 'LBL_TEST_TITLE_3',
                    'layout' => 'subpanel',
                ),
            ), // Test correct subpanel should be converted to new defs
        );
    }

    /**
     * @ticket BR-1597
     */
    public function testBogusOverride()
    {
        $this->subpanelData['projects'] = array('override_subpanel_name' => 'Quotedefault');
        $this->upgrader->loadSubpanelData($this->module, $this->subpanelData);

        $this->upgrader->setLegacyViewdefs(array("projects" => array('override_subpanel_name' => 'Quotedefault')));
        $this->upgrader->convertLegacyViewDefsToSidecar();
        $this->assertEmpty($this->upgrader->getSidecarViewDefs(), "Should not convert bogus data");
    }
}

/**
 * Mock SidecarLayoutdefsMetaDataUpgrader for test purposes
 */
class SidecarLayoutdefsMetaDataUpgraderMock extends SidecarLayoutdefsMetaDataUpgrader
{
    /**
     * Fill static subpanel defs.
     *
     * @param string $module Module name.
     * @param array $data
     */
    public function loadSubpanelData($module, array $data = array())
    {
        self::$supanelData[$module] = $data;
    }

    /**
     * Load legacy view defs manually.
     *
     * @param array $data $layout_defs[{module}]['subpanel_setup'].
     * @return void
     */
    public function setLegacyViewdefs(array $data = array())
    {
        $this->legacyViewdefs = $data;
    }
}
