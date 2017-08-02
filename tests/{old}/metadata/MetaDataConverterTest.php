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

class MetaDataConverterTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $defs = array(
        'detail' => array(
            'templateMeta' => array(
                'maxColumns' => '1',
                'widths' => array(
                    array('label' => '10', 'field' => '30'),
                ),
            ),
            'panels' => array(
                array(
                    'label' => 'LBL_PANEL_DEFAULT',
                    'fields' => array(
                        'bug_number',
                        array(
                            'name'=>'name',
                            'displayParams'=>array(
                                'required'=>true,
                                'wireless_edit_only'=>true,
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'edit' => array(
            'templateMeta' => array(
                'maxColumns' => '1',
                'widths' => array(
                    array('label' => '10', 'field' => '30'),
                ),
            ),
            'panels' => array(
                array(
                    'label' => 'LBL_PANEL_DEFAULT',
                    'fields' => array(
                        array(
                            'name'=>'name',
                            'displayParams'=>array(
                                'required'=>true,
                                'wireless_edit_only'=>true,
                            ),
                        ),
                        'phone_office',
                        array(
                            'name'=>'website',
                            'displayParams'=>array(
                                'type'=>'link',
                            ),
                        ),
                        'email',
                    ),
                ),
            ),
        ),
        'list' => array(
            'panels' => array(
                array(
                    'label' => 'LBL_PANEL_DEFAULT',
                    'fields' => array(
                        array(
                            'name' => 'name',
                            'label' => 'LBL_NAME',
                            'default' => true,
                            'enabled' => true,
                            'link' => true,
                            'width' => '10%',
                        ),
                        array(
                            'name' => 'bug_number',
                            'enabled' => true,
                            'width' => '10%',
                            'default' => true,
                        ),
                    ),
                ),
            ),
        ),
        'search' => array(
            'templateMeta' => array(
                'maxColumns' => '1',
                'widths' => array('label' => '10', 'field' => '30'),
            ),
            'layout' => array(
                'basic_search' => array(
                    'name',
                ),
            ),
        ),
    );

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testConvertWirelessListToLegacy() {
        $converted = MetaDataConverter::toLegacy('list', $this->defs['list']);
        $this->assertArrayHasKey('BUG_NUMBER', $converted, 'BUG_NUMBER missing from the conversion');
        $this->assertArrayHasKey('NAME', $converted, 'NAME missing from the conversion');
    }
    
    public function testConvertWirelessDetailToLegacy() {
        $converted = MetaDataConverter::toLegacy('detail', $this->defs['detail']);
        $this->assertNotEmpty($converted['panels'][0][0], 'First string field name is missing');
        $this->assertEquals('bug_number', $converted['panels'][0][0], 'First field name is not as expected');
    }
    
    public function testNoConversionForNonConvertableViewType() {
        $converted = MetaDataConverter::toLegacy('search', $this->defs['search']);
        $this->assertEquals($converted, $this->defs['search'], 'Viewdefs converted unexpectedly');
    }
    
    public function testConvertFieldsets() {
        $file = 'tests/{old}/metadata/supportfiles/Callsmobileedit.php';
        require $file;
        
        $this->assertInternalType('array', $viewdefs['Calls']['mobile']['view']['edit'], 'Expected view def structure not found for Calls mobile edit');
        $converted = MetaDataConverter::toLegacy('edit', $viewdefs['Calls']['mobile']['view']['edit']);
        $converted = MetaDataConverter::fromGridFieldsets($converted);
        
        $this->assertTrue(isset($converted['panels'][4][0]), "Conversion failed to convert fieldset at offset 4");
        $this->assertEquals('duration_hours', $converted['panels'][4][0], "duration_hours did not convert from a fieldset");
        
        $this->assertTrue(isset($converted['panels'][5][0]), "Conversion failed to convert fieldset at offset 5");
        $this->assertEquals('duration_minutes', $converted['panels'][5][0], "duration_minutes did not convert from a fieldset");
    }
    /**
     * Test converting subpanels
     */
    public function testConvertSubpanels()
    {
        static $fieldMap = array(
            'name' => true,
            'label' => true,
            'type' => true,
            'target_module' => true,
            'target_record_key' => true,
        );
        $converter = new MetaDataConverter();
        $bean = BeanFactory::newBean('Users');

        $spDefs = new SubPanelDefinitions($bean);
        $layout_defs = $spDefs->layout_defs;
        $this->assertTrue(is_array($layout_defs));
        $this->assertNotEmpty($layout_defs['subpanel_setup']);

        foreach ($layout_defs['subpanel_setup'] as $name => $subpanel_info) {
            $aSubPanel = $spDefs->load_subpanel($name, '', $bean);
            $this->assertInstanceOf('aSubpanel', $aSubPanel);

            // no collections
            if ($aSubPanel->isCollection()) {
                continue;
            }
            $panel_definition = $converter->fromLegacySubpanelsViewDefs($aSubPanel->panel_definition, 'Users');
        }

        $this->assertNotEmpty($panel_definition, "Panel Definition not set");
        foreach ($panel_definition['panels'] as $panel) {
            $this->assertArrayHasKey('name', $panel, "Panel should have a name field");
            $this->assertArrayHasKey('label', $panel, "Panel should have a label field");
            $this->assertArrayHasKey('fields', $panel, "Panels should have fields");
            foreach ($panel['fields'] as $fieldDef) {
                foreach ($fieldDef as $key => $value) {
                    $this->assertContains($key, $fieldMap);
                }
            }
        }
    }
    /**
     * Test adding custom link to profileactions
     */
    public function testConvertProfileactions()
    {
        $converter = new MetaDataConverter();

        // Create Input field map includes different test link to see if these links are
        // converted properly which has correct label, route and acl_action
        $testlink = array();
        $testlink['google'] = array('linkinfo' => array('Google' => 'https://www.google.com/'),
            'submenu' => ''
        );
        $testlink['contact'] = array('linkinfo' => array('LBL_CONTACTS' => '#Contacts'),
            'submenu' => ''
        );
        $testlink['report'] = array('linkinfo' => array('LBL_REPORTS' => 'index.php?module=Reports&action=index'),
            'submenu' => ''
        );
        $testlink['administration'] = array('linkinfo' => array('LBL_ADMIN' => 'index.php?module=Administration&action=index'),
            'submenu' => ''
        );
        $testlink['support'] = array(
            'linkinfo' => array('LBL_TRAINING' => 'javascript:void(window.open(\'http://support.sugarcrm.com\'))'),
            'submenu' => ''
        );
        $testlink['task'] = array('linkinfo' => array('LBL_TASKS' => '#Tasks'),
            'submenu' => array(
                'case' => array('LBL_CASES' => '#Cases'),
                'note' => array('LBL_NOTES' => '#Notes'),
                'bug'  => array('LBL_BUGS' => '#Bugs'),
                'support' => array('LBL_TRAINING' => 'javascript:void(window.open(\'http://support.sugarcrm.com\'))'),
            )
        );
        // Transform globalcontrollink format into regular associate array format
        $inputTestLinks = $converter->processFromGlobalControlLinkFormat($testlink);

        // Expected output field map
        $expectedOutput = array(
            'Google' => array( 'label' => 'Google', 'route' => 'https://www.google.com/', 'acl_action' => ''),
            'LBL_CONTACTS' => array( 'label' => 'LBL_CONTACTS', 'route' => '#Contacts', 'acl_action' => ''),
            'LBL_TRAINING' => array( 'label' => 'LBL_TRAINING', 'route' => 'http://support.sugarcrm.com', 'acl_action' => '', 'openwindow' => true),
            'LBL_TASKS' => array( 'label' => 'LBL_TASKS', 'route' => '#Tasks', 'acl_action' => '',
                'submenu' => array(
                    array( 'label' => 'LBL_CASES', 'route' => '#Cases', 'acl_action' => ''),
                    array( 'label' => 'LBL_NOTES', 'route' => '#Notes', 'acl_action' => ''),
                    array( 'label' => 'LBL_BUGS', 'route' => '#Bugs', 'acl_action' => ''),
                    array( 'label' => 'LBL_TRAINING', 'route' => 'http://support.sugarcrm.com', 'acl_action' => '', 'openwindow' => true),
                )
            ),
            'LBL_REPORTS' => array( 'label' => 'LBL_REPORTS', 'route' => '#Reports', 'acl_action' => 'list'),
            'LBL_ADMIN' => array( 'label' => 'LBL_ADMIN', 'route' => '#bwc/index.php?module=Administration&action=index', 'acl_action' => 'admin'),
        );

        // Test if custom links are converted correctly by comparing with expectedOutput
        foreach($inputTestLinks as $item){
            $convertedItem = $converter->convertCustomMenu($item);
            if(!empty($item['SUBMENU'])){
                $convertedSubmenu = array();
                foreach($item['SUBMENU'] as $submenu){
                    $convertedSubmenu[] = $converter->convertCustomMenu($submenu);
                }
                $convertedItem['submenu'] = $convertedSubmenu;
            }
            $this->assertEquals($expectedOutput[$convertedItem['label']], $convertedItem, "{$convertedItem['label']} array did not convert correctly");
        }
    }

    public function testEmptyConvertProfileactions()
    {
        $testlink = array(
            'attachment' => array(
                'linkinfo' => array('ATTACHMENTS' => 'client/base/views/attachments/attachments.php'),
            'submenu' => ''
            )
        );
        $converter = new MetaDataConverter();
        $result = $converter->processFromGlobalControlLinkFormat($testlink);
        $this->assertCount(1, $result);
        $result = array_shift($result);
        $convertedItem = $converter->convertCustomMenu($result);
        $this->assertEmpty($convertedItem);
    }

    /**
     * Test for not removing extra fields from defs while conversion.
     *
     * @param $viewdef
     * @param $vardef
     * @param $result
     * @dataProvider provider_convertLegacyViewDefsToSidecar
     */
    public function testConvertLegacyViewDefsToSidecar($viewdef, $vardef, $result)
    {
        $mock = $this->createPartialMock(
            'MetaDataConverter',
            array('loadSearchFields')
        );
        $mock->expects($this->any())
            ->method('loadSearchFields')
            ->will($this->returnValue($viewdef));
        $defs = array();
        $defs['layout']['basic_search'] = $viewdef;
        $fields = $mock->convertLegacyViewDefsToSidecar($defs, "", $vardef, "", "", "");
        $this->assertEquals($result, $fields['fields']);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function provider_convertLegacyViewDefsToSidecar()
    {
        return array(
            array(
                array(
                    'name' => array(
                        'query_type' => 'default',
                        'label' => 'LBL_NAME'
                    ),
                    'account_name' => array(
                        'query_type' => 'default',
                        'label' => 'LBL_ACC',
                        'db_field' => array(
                            'accounts.name',
                        ),
                    ),
                    'wrong_field' => array(
                        'label' => 'LBL_FLD',
                        'db_field' => array()
                    ),
                    'another_field' => array(
                        'label' => 'LBL_FLD2',
                        'db_field' => array('name'),
                        'type' => 'bool',
                    ),
                ),
                array(
                    'name' => array(
                        'name' => 'name',
                        'type' => 'varchar',
                    ),
                    'account_name' => array(
                        'name' => 'account_name',
                        'type' => 'relate',
                    ),
                ),
                array(
                    'name' => array(),
                    'account_name' => array(
                        'dbFields' => array(),
                        'vname' => 'LBL_ACC'
                    ),
                    'another_field' => array(
                        'dbFields' => array('name'),
                        'vname' => 'LBL_FLD2',
                        'type' => 'bool',
                    ),
                    '$owner' => array(
                        'predefined_filter' => 1,
                        'vname' => 'LBL_CURRENT_USER_FILTER',
                    ),
                    '$favorite' => array(
                        'predefined_filter' => 1,
                        'vname' => 'LBL_FAVORITES_FILTER',
                    )
                ),
            ),
        );
    }
}
