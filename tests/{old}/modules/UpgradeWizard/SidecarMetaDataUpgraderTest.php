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

require_once 'tests/{old}/modules/UpgradeWizard/SidecarMetaDataFileBuilder.php';

class SidecarMetaDataUpgraderTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Flag to let us know if there is a current upgrade wizard log that is
     * backed
     * up to support this test
     *
     * @var bool
     */
    protected static $logBackedUp = false;

    /**
     * The files builder to bring in legacy files into place and prepare them
     * for upgrade
     *
     * @var SidecarMetaDataFileBuilder
     */
    public static $builder = null;

    /**
     * The upgrader object, called once to set everthing up
     *
     * @var SidecarMetaDataUpgrader
     */
    public static $upgrader = null;

    /**
     * Utility method for building and holding the builder object.
     * Because of how
     * dataProviders are called in the test stack and how this test is using
     * setUpBeforeClass and tearDownAfterClass, this needs to be done this way.
     *
     * NOTE: dataProvider methods are called before any method in the test. So
     * allowing the needed objects to be built like this is essential for the
     * dataProviders to run as expected.
     *
     * @static
     *
     * @return SidecarMetaDataFileBuilder
     */
    public static function getBuilder()
    {
        if (null == self::$builder) {
            self::$builder = new SidecarMetaDataFileBuilder();
        }

        return self::$builder;
    }

    /**
     * Gets the MetaDataUpgrader object.
     * See notes for getBuilder as to why this
     * is being handled this way.
     *
     * @static
     *
     * @return SidecarMetaDataUpgrader
     */
    public static function getUpgrader()
    {
        if (null === self::$upgrader) {
            self::$upgrader = new SidecarMetaDataUpgraderForTest();
        }

        return self::$upgrader;
    }

    public static function setUpBeforeClass()
    {
        // If there is an upgrade wizard log in place, back it up
        $GLOBALS['app_list_strings'] = return_app_list_strings_language(
            $GLOBALS['current_language']);

        // Builds all the legacy test files
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('files');
        self::getBuilder();
        self::$builder->buildFiles();

        // Run the upgrader so everything is in place for testing
        self::getUpgrader();
        self::$upgrader->upgrade();
    }

    public static function tearDownAfterClass()
    {
        self::getBuilder();
        self::$builder->teardownFiles();
        SugarTestHelper::tearDown();
    }

    public function testLegacyMetadataFilesForRemoval()
    {
        // Get files for removal - these should include our custom legacy files
        $upgrader = self::getUpgrader();
        $removals = $upgrader->getFilesForRemoval();

        // Get legacy file paths
        $builder = self::getBuilder();
        $legacyFiles = $builder->getFilesToMake('legacy');
        $sidecarfiles = $builder->getFilesToMake('sidecar');
        // don't expect removal if file is the same and drop listdefs and searchdefs since we're not deleting them
        $legacyFiles = array_filter(array_diff($legacyFiles, $sidecarfiles),
            function($name) { return strpos($name, "listviewdefs.") === false &&  strpos($name, "search") === false; });
        // Upgrader can remove additional files, so drop those that aren't in our file list
        $removed = array_filter(array_intersect($removals, $legacyFiles));

        sort($removed);
        sort($legacyFiles);
        $this->assertEquals($legacyFiles, $removed,
            'Legacy files for removal is not the same as legacy files in build');
    }

    public function testUpgraderHasNoFailures()
    {
        // Get our failures
        $upgrader = self::getUpgrader();
        $failures = $upgrader->getFailures();

        $this->assertEmpty($failures, 'There were upgrade failures: '.var_export($failures, true));
    }

    // Added for Bug 55568 - new OOTB metadata was not included in upgrade
    public function testUpgraderUsedNewViewDefs()
    {
        // Bug 55936 - Fixed hardcoded path for testing to pick up metadata
        // changes
        $filename = 'custom/working/modules/Cases/clients/portal/views/record/record.php';
        $exists = file_exists($filename);
        $this->assertTrue($exists, 'Cases portal edit metadata did not convert');

        require $filename;
        $this->assertNotEmpty($viewdefs['Cases']['portal']['view']['record']['buttons'],
            'The buttons array from the new metadata was not captured');
    }

    public function testUpgraderPreservedTabsPanels()
    {
        $module = 'Contacts';
        $filename = "custom/modules/$module/clients/base/views/record/record.php";
        $this->assertFileExists($filename);
        require $filename;

        // Handle assertion of templateMeta.useTabs right up front
        $this->assertNotEmpty($viewdefs[$module]['base']['view']['record']['templateMeta']);
        $this->assertNotEmpty($viewdefs[$module]['base']['view']['record']['templateMeta']['useTabs']);

        // Now handle panels
        $this->assertNotEmpty($viewdefs[$module]['base']['view']['record']['panels']);
        $this->assertEquals(5, count($viewdefs[$module]['base']['view']['record']['panels']));

        // Panels 1-3 should have:
        // 'newTab' => true and 'panelDefault' => 'expanded',
        // Panel 4 should have:
        // 'newTab' => false and 'panelDefault' => 'expanded',
        $panels = $viewdefs[$module]['base']['view']['record']['panels'];

        // Test panels 1 - 3
        $this->assertArrayHasKey('newTab', $panels[1]);
        $this->assertTrue($panels[1]['newTab']);
        $this->assertArrayHasKey('panelDefault', $panels[1]);
        $this->assertEquals('expanded', $panels[1]['panelDefault']);
        $this->assertArrayHasKey('newTab', $panels[2]);
        $this->assertTrue($panels[2]['newTab']);
        $this->assertArrayHasKey('panelDefault', $panels[2]);
        $this->assertEquals('expanded', $panels[2]['panelDefault']);
        $this->assertArrayHasKey('newTab', $panels[3]);
        $this->assertTrue($panels[3]['newTab']);
        $this->assertArrayHasKey('panelDefault', $panels[3]);
        $this->assertEquals('expanded', $panels[3]['panelDefault']);

        // Panel 4 should be different
        $this->assertArrayHasKey('newTab', $panels[4]);
        $this->assertFalse($panels[4]['newTab']);
        $this->assertArrayHasKey('panelDefault', $panels[4]);
        $this->assertEquals('expanded', $panels[4]['panelDefault']);

        // Assert labels for converted panels
        $this->assertArrayHasKey('label', $panels[1]);
        $this->assertEquals('LBL_RECORD_BODY', $panels[1]['label']);

        // Assert labels for converted but not relabeled panels
        $this->assertArrayHasKey('label', $panels[3]);
        $this->assertEquals('LBL_PANEL_ADVANCED', $panels[3]['label']);
        $this->assertArrayHasKey('label', $panels[4]);
        $this->assertEquals('LBL_PANEL_ASSIGNMENT', $panels[4]['label']);
    }
    public function _sidecarFilesInPlaceProvider()
    {
        $builder = self::getBuilder();
        return $builder->getFilesToMake('sidecar', true);
    }

    /**
     * @dataProvider _sidecarFilesInPlaceProvider
     *
     * @param string $file
     */
    public function testSidecarFilesInPlace($file)
    {
        if(empty($file)) {
            return;
        }
        $this->assertFileExists($file, "File $file was not upgraded");
    }

    public function _sidecarMetadataFormatProvider()
    {
        $builder = self::getBuilder();
        return $builder->getFilesToMakeByView(array('list', 'edit', 'detail'));
    }

    /**
     * @dataProvider _sidecarMetadataFormatProvider
     *
     * @param string $module
     * @param string $view
     * @param string $type
     * @param string $filepath
     */
    public function testSidecarMetadataFormat($module, $view, $type, $filepath)
    {
        if(empty($filepath)) {
            return;
        }
        $this->assertFileExists($filepath, "$filepath does not exist");
        require $filepath;

        // Begin assertions
        $this->assertNotEmpty($viewdefs[$module][$type]['view'][$view],
            "$view view defs for the $module module are empty");

        $defs = $viewdefs[$module][$type]['view'][$view];
        $this->assertArrayHasKey('panels', $defs, 'No panels array found in view defs');
        $this->assertArrayHasKey('fields', $defs['panels'][0],
            'Fields array missing or in incorrect format in view defs');
        $this->assertNotEmpty($defs['panels'][0]['fields'], 'Fields array is empty');

        // List view specific test
        if ($view == 'list') {
            $this->assertArrayHasKey('name', $defs['panels'][0]['fields'][0],
                'No name field found in the first field def');
        }
    }

    /**
     * Added for bug 57414
     * Available fields of mobile listview shown under default fields list after
     * upgrade
     * * @group Bug57414
     * @dataProvider _sidecarListEnabledFieldProvider
     */
    public function testSidecarListViewDefsProperlyFlagEnabledFields($module, $view, $type, $filepath)
    {
        $this->assertFileExists($filepath, "$filepath does not exist");
        require $filepath;

        // Begin assertions
        $this->assertNotEmpty($viewdefs[$module][$type]['view'][$view],
            "$view view defs for the $module module are empty");

        $defs = $viewdefs[$module][$type]['view'][$view];
        $this->assertTrue(isset($defs['panels'][0]['fields']),
            'Field array is missing from the upgrade file');

        // Test actual fix for this bug
        $test['name'] = array('default' => '', 'enabled' => '', 'edefault' => true,
            'eenabled' => true);
        $testfield = 'assigned_user_name';
        if ($type == 'portal') {
            $testfield = 'priority';
        }
        $test[$testfield] = array('default' => '', 'enabled' => '', 'edefault' => false,
            'eenabled' => true);

        foreach ($defs['panels'][0]['fields'] as $field) {
            if (isset($test[$field['name']])) {
                $test[$field['name']]['default'] = $field['default'];
                $test[$field['name']]['enabled'] = $field['enabled'];
            }
        }

        // Assertions
        foreach ($test as $field => $assert) {
            $this->assertEquals($assert['edefault'], $assert['default'],
                "$field default should be false but is {$assert['default']}");
            $this->assertEquals($assert['eenabled'], $assert['enabled'],
                "$field enabled should be true but is {$assert['enabled']}");
        }
    }

    public function _sidecarListEnabledFieldProvider()
    {
        $builder = self::getBuilder();
        return $builder->getFilesToMakeByView('list');
    }

    /**
     * Test for record-type upgrades
     * @param string $module
     * @param string $view
     * @param string $type
     * @param string $filepath
     * @dataProvider _sidecarRecordProvider
     */
    public function testSidecarRecordfields($module, $view, $type, $filepath)
    {
        $this->assertFileExists($filepath, "$filepath does not exist");
        require $filepath;

        $defs = $viewdefs[$module][$type]['view'][$view];
        $this->assertTrue(isset($defs['panels'][1]['fields']), 'Field array is missing from the upgrade file');
        $idfield = null;
        foreach($defs['panels'] as $panel) {
            // adding to header is wrong
            if(!empty($panel['header'])) continue;
            foreach($panel['fields'] as $field) {
                // look for description field
                if ($field == 'description' || (!empty($field['name']) && $field['name'] == 'description')) {
                    $idfield = $field;
                    break 2;
                }
            }
        }
        $this->assertNotEmpty($idfield, "Description field not found in merged view");
    }

    public function _sidecarRecordProvider()
    {
        $builder = self::getBuilder();
        return $builder->getFilesToMakeByView('record');
    }

    /**
     * Tests that merged fields are handled correctly
     *
     * @param string $module The module to test
     * @param string $view The view to test
     * @param string $type The client to test
     * @param string $filepath The full path the new defs files that has been upgraded
     * @param string $field The name of the field to check exists on
     * @param boolean $exists The expectation of the field on a layout after upgrade
     * @dataProvider _sidecarRecordviewMergeProvider
     */
    public function testRecordviewMerge($module, $view, $type, $filepath, $field, $exists)
    {
        $this->assertFileExists($filepath, "$filepath does not exist");
        require $filepath;

        $defs = $viewdefs[$module][$type]['view'][$view];
        $this->assertTrue(isset($defs['panels'][1]['fields']), 'Field array is missing from the upgrade file');

        $found = $this->_fieldExistsInDefs($field, $defs);
        $this->assertEquals($exists, $found, "Failed to prove existence of field $field in file $filepath");
    }

    /**
     * Data provider that appends some additional test data to the collection,
     * specifically for testing handling of merge fields
     *
     * @return array
     */
    public function _sidecarRecordviewMergeProvider()
    {
        // Exists is an after the fact check
        $testFields = array(
            // Tests proper address field handling
            'Accounts' => array(
                array('field' => 'billing_address', 'exists' => true,),
                array('field' => 'shipping_address', 'exists' => true,),
                array('field' => 'billing_address_street', 'exists' => false,),
                array('field' => 'shipping_address_street', 'exists' => false,),
            ),
            // Tests proper address field handling and forced removed field handling
            'Contacts' => array(
                array('field' => 'alt_address', 'exists' => true,),
                array('field' => 'primary_address', 'exists' => true,),
                array('field' => 'alt_address_street', 'exists' => false,),
                array('field' => 'primary_address_street', 'exists' => false,),
                array('field' => 'twitter_id', 'exists' => false,),
                array('field' => 'twitter', 'exists' => true,),
                array('field' => 'portal_password1', 'exists' => false,),
            ),
            // Tests proper address handling and proper date combo field handling
            'Leads' => array(
                array('field' => 'alt_address', 'exists' => true,),
                array('field' => 'primary_address', 'exists' => true,),
                array('field' => 'alt_address_street', 'exists' => false,),
                array('field' => 'primary_address_street', 'exists' => false,),
                array('field' => 'date_entered', 'exists' => false,),
                array('field' => 'date_modified', 'exists' => false,),
                array('field' => 'date_entered_by', 'exists' => true,),
                array('field' => 'date_modified_by', 'exists' => true,),
            ),
            'Opportunities' => array(
                array('field' => 'date_entered_by', 'exists' => true,),
                array('field' => 'date_modified_by', 'exists' => true,),
                array('field' => 'date_entered', 'exists' => false,),
                array('field' => 'date_modified', 'exists' => false,),
            ),
        );

        $builder = self::getBuilder();
        $rows = $builder->getFilesToMakeByView('record', 'sidecar', array('Accounts', 'Contacts', 'Leads', 'Opportunities'));
        $return = array();
        foreach ($rows as $row) {
            foreach ($testFields[$row['module']] as $fields) {
                $return[] = array_merge($row, $fields);
            }
        }

        return $return;
    }

    /**
     * Tests panel conversion from old style to new style
     * 
     * @param string $client The client to test conversion for
     * @param boolean $full Flag that decides whether to return just fields or a full conversion
     * @param array $former Former format for conversion
     * @param array $expect Expected format for testing
     * @dataProvider _getHandleConversionTestData
     */
    public function testGetConvertedPanelDefs($client, $full, $former, $expect)
    {
        $ug = new SidecarGridMetaDataUpgrader(
            new SidecarMetaDataUpgraderForTest, 
            array('client' => $client)
        );
        $converted = $ug->handleConversion($former, 'panels', $full);
        $this->assertEquals($expect, $converted);
    }

    /**
     * Data provider for testGetConverterPanelDefs
     * 
     * @return array
     */
    public function _getHandleConversionTestData()
    {
        return array(
            // Mobile
            array(
                'client' => 'mobile',
                'full' => false,
                'former' => $this->getMobileTestConversionFormer(),
                'expect' => $this->getMobileTestConversionExpect(),
                
            ),
            // Base full
            array(
                'client' => 'base',
                'full' => true,
                'former' => $this->getBaseTestConversionFormer(),
                'expect' => $this->getBaseTestConversionExpect(),
            ),
        );
    }

    /**
     * Expected result of base full metadata upgrade conversion
     * 
     * @return array
     */
    protected function getBaseTestConversionExpect()
    {
        return array(
            'templateMeta' => array(),
            'panels' => array(
                array(
                    'name' => 'panel_body',
                    'label' => 'LBL_RECORD_BODY',
                    'columns' => 2,
                    'labels' => true,
                    'labelsOnTop' => true,
                    'placeholders' => true,
                    'fields' => array(
                        'first_name',
                        'remove_from_queue_c',
                        'last_name',
                        'do_not_call',
                        'account_name',
                        '',
                        'phone_home',
                        'phone_work',
                        'phone_mobile',
                        'phone_other',
                        'email1',
                        'phone_fax',
                        'primary_address_street',
                        'alt_address_street',
                    ),
                ),
                array(
                    'name' => 'panel_hidden',
                    'label' => 'LBL_RECORD_SHOWMORE',
                    'columns' => 2,
                    'labels' => true,
                    'labelsOnTop' => true,
                    'placeholders' => true,
                    'hide' => true,
                    'fields' => array(
                        'sales_quote_amount_c',
                        'wpsourcecode_c',
                        'sales_lawn_size_c',
                        'assigned_user_name',
                        'realgreen_number_c',
                        '',
                        array(
                            'name' => 'description',
                            'span' => 12,
                        ),
                    ),
                ),
                array(
                    'name' => 'lbl_panel_advanced',
                    'label' => 'LBL_PANEL_ADVANCED',
                    'columns' => 2,
                    'labels' => true,
                    'labelsOnTop' => true,
                    'placeholders' => true,
                    'fields' => array(
                        'customer_cancelled_c',
                        'status',
                        'sales_cancel_reject_reason_c',
                        'weed_pro_customer_c',
                        'task_call_stage_list_c',
                        '',
                        'date_entered',
                        'date_modified',
                    ),
                ),
                array(
                    'name' => 'lbl_editview_panel4',
                    'label' => 'LBL_EDITVIEW_PANEL4',
                    'columns' => 2,
                    'labels' => true,
                    'labelsOnTop' => true,
                    'placeholders' => true,
                    'fields' => array(
                        'sales_lifecycle_stage_c',
                        'intakeformtype_c',
                        'hubspot_lead_score_c',
                        'hs_analytics_source_c',
                        'campaign_name_c',
                        'campaign_name',
                    ),
                ),
                array(
                    'name' => 'lbl_editview_panel2',
                    'label' => 'LBL_EDITVIEW_PANEL2',
                    'columns' => 2,
                    'labels' => true,
                    'labelsOnTop' => true,
                    'placeholders' => true,
                    'fields' => array(
                        array(
                            'name' => 'leadmap_c',
                            'label' => 'LBL_LEADMAP',
                            'span' => 12,
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Test data for a full base conversion. This represents the old style of 
     * metadata.
     * 
     * @return array
     */
    protected function getBaseTestConversionFormer()
    {
        return array(
            'panels' => array(
                'LBL_CONTACT_INFORMATION' => array(
                    array(
                        array(
                            'name' => 'first_name',
                            'comment' => 'First name of the contact',
                            'label' => 'LBL_FIRST_NAME',
                        ),
                        array(
                            'name' => 'remove_from_queue_c',
                            'label' => 'LBL_REMOVE_FROM_QUEUE',
                        ),
                    ),
                    array(
                        array(
                            'name' => 'last_name',
                            'comment' => 'Last name of the contact',
                            'label' => 'LBL_LAST_NAME',
                        ),
                        'do_not_call',
                    ),
                    array(
                        array(
                            'name' => 'account_name',
                            'displayParams' => array(),
                        ),
                        array(
                            'name' => '',
                            'displayParams' => array(
                                'enableConnectors' => true,
                                'module' => 'Leads',
                                'connectors' => array(
                                    'ext_rest_twitter',
                                ),
                            ),
                        ),
                    ),
                    array(
                        array(
                            'name' => 'phone_home',
                            'comment' => 'Home phone number of the contact',
                            'label' => 'LBL_HOME_PHONE',
                        ),
                        'phone_work',
                    ),
                    array(
                        'phone_mobile',
                        array(
                            'name' => 'phone_other',
                            'comment' => 'Other phone number for the contact',
                            'label' => 'LBL_OTHER_PHONE',
                        ),
                    ),
                    array(
                        'email1',
                        'phone_fax',
                    ),
                    array(
                        array(
                            'name' => 'primary_address_street',
                            'label' => 'LBL_PRIMARY_ADDRESS',
                            'type' => 'address',
                            'displayParams' => array(
                                'key' => 'primary',
                            ),
                        ),
                        array(
                            'name' => 'alt_address_street',
                            'label' => 'LBL_ALTERNATE_ADDRESS',
                            'type' => 'address',
                            'displayParams' => array(
                                'key' => 'alt',
                            ),
                        ),
                    ),
                ),
                'LBL_PANEL_ASSIGNMENT' => array(
                    array(
                        array(
                            'name' => 'sales_quote_amount_c',
                            'label' => 'LBL_SALES_QUOTE_AMOUNT',
                        ),
                        array(
                            'name' => 'wpsourcecode_c',
                            'studio' => 'visible',
                            'label' => 'LBL_WPSOURCECODE',
                        ),
                    ),
                    array(
                        array(
                            'name' => 'sales_lawn_size_c',
                            'label' => 'LBL_SALES_LAWN_SIZE',
                        ),
                        array(
                            'name' => 'assigned_user_name',
                            'label' => 'LBL_ASSIGNED_TO',
                        ),
                    ),
                    array(
                        array(
                            'name' => 'realgreen_number_c',
                            'label' => 'LBL_REALGREEN_NUMBER',
                        ),
                        '',
                    ),
                    array(
                        'description',
                    ),
                ),
                'LBL_PANEL_ADVANCED' => array(
                    array(
                        array(
                            'name' => 'customer_cancelled_c',
                            'studio' => 'visible',
                            'label' => 'LBL_CUSTOMER_CANCELLED',
                        ),
                        'status',
                    ),
                    array(
                        array(
                            'name' => 'sales_cancel_reject_reason_c',
                            'studio' => 'visible',
                            'label' => 'LBL_SALES_CANCEL_REJECT_REASON',
                        ),
                        array(
                            'name' => 'weed_pro_customer_c',
                            'studio' => 'visible',
                            'label' => 'LBL_WEED_PRO_CUSTOMER',
                        ),
                    ),
                    array(
                        array(
                            'name' => 'task_call_stage_list_c',
                            'studio' => 'visible',
                            'label' => 'LBL_TASK_CALL_STAGE_LIST',
                        ),
                        '',
                    ),
                    array(
                        array(
                            'name' => 'date_entered',
                            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                        ),
                        array(
                        'name' => 'date_modified',
                        'label' => 'LBL_DATE_MODIFIED',
                        'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                        ),
                    ),
                ),
                'lbl_editview_panel4' => array(
                    array(
                        array(
                            'name' => 'sales_lifecycle_stage_c',
                            'studio' => 'visible',
                            'label' => 'LBL_SALES_LIFECYCLE_STAGE',
                        ),
                        array(
                            'name' => 'intakeformtype_c',
                            'studio' => 'visible',
                            'label' => 'LBL_INTAKEFORMTYPE',
                        ),
                    ),
                    array(
                        array(
                            'name' => 'hubspot_lead_score_c',
                            'label' => 'LBL_HUBSPOT_LEAD_SCORE',
                        ),
                        array(
                            'name' => 'hs_analytics_source_c',
                            'label' => 'LBL_HS_ANALYTICS_SOURCE',
                        ),
                    ),
                    array(
                        array(
                            'name' => 'campaign_name_c',
                            'label' => 'LBL_CAMPAIGN_NAME',
                        ),
                        array(
                            'name' => 'campaign_name',
                            'label' => 'LBL_CAMPAIGN',
                        ),
                    ),
                ),
                'lbl_editview_panel2' => array(
                    array(
                        array(
                            'name' => 'leadmap_c',
                            'label' => 'LBL_LEADMAP',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Gets expected converted metadata for mobile platform
     * 
     * @return array
     */
    protected function getMobileTestConversionExpect()
    {
        return array(
            array(
                'name' => 'document_name',
                'label' => 'LBL_DOC_NAME',
            ),
            'active_date',
            'exp_date',
            'team_name',
        );
    }

    /**
     * Gets the old style metadata for mobile edit/detail view for testing 
     * conversion.
     * 
     * @return array
     */
    protected function getMobileTestConversionFormer()
    {
        return array(
            'templateMeta' => array('maxColumns' => 1),
            'panels' => array(
                array(
                    array(
                        'name' => 'document_name',
                        'label' => 'LBL_DOC_NAME',
                    ),
                ),
                array('active_date'),
                array('exp_date'),
                array('team_name'),
            ),
        );
    }

    /**
     * Checks whether a fieldname exists in a viewdef
     *
     * @param string $fieldname The fieldname
     * @param array $defs The defs, as of view type
     * @return bool
     */
    protected function _fieldExistsInDefs($fieldname, $defs) {
        foreach ($defs['panels'] as $panel) {
            foreach ($panel['fields'] as $field) {
                // Broken up into two conditions to improve readability and make
                // it "twitter rule" compliant
                if ($field == $fieldname) {
                    return true;
                }

                if (is_array($field) && isset($field['name']) && $field['name'] == $fieldname) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     *
     * @param string $module
     * @param string $view
     * @param string $type
     * @param string $filepath
     * @dataProvider _sidecarSearchProvider
     */
    public function testSidecarSearchDefs($module, $view, $type, $filepath)
    {
        $this->assertFileExists($filepath, "$filepath does not exist");
        require $filepath;

        $defs = $viewdefs[$module][$type]['filter']['default'];
        $this->assertArrayHasKey("fields", $defs);
        $this->assertArrayHasKey('$owner', $defs['fields']);
        $this->assertArrayHasKey('$favorite',  $defs['fields']);
        $this->assertArrayHasKey('team_name',  $defs['fields']);
        $this->assertArrayHasKey('address_city',  $defs['fields']);
        $this->assertArrayNotHasKey('somefield',  $defs['fields']);
    }

    public function _sidecarSearchProvider()
    {
        $builder = self::getBuilder();
        return $builder->getFilesToMakeByView('filter');
    }

    /**
     *
     * @param string $module
     * @param string $view
     * @param string $type
     * @param string $filepath
     * @dataProvider _sidecarMenuProvider
     */
    public function testSidecarMenuDefs($module, $view, $type, $filepath)
    {
        $this->assertFileExists($filepath, "$filepath does not exist");
        require $filepath;

        $_module = strtolower($module);
        $defs = $viewdefs[$module][$type]['menu']['header'];
        // create
        $this->assertEquals('edit', $defs[0]['acl_action']);
        $this->assertEquals($module, $defs[0]['acl_module']);
        $this->assertEquals('fa fa-plus', $defs[0]['icon']);
        $this->assertEquals("#$module/create", $defs[0]['route']);
        // list
        $this->assertEquals('list', $defs[1]['acl_action']);
        $this->assertEquals($module, $defs[1]['acl_module']);
        $this->assertEquals("#$module", $defs[1]['route']);
        // reports
        $this->assertEquals('list', $defs[2]['acl_action']);
        $this->assertEquals($module, $defs[2]['acl_module']);
        $this->assertEquals('fa fa-bar-chart-o', $defs[2]['icon']);
        $this->assertEquals("#Reports", $defs[2]['route']);
        // import
        $this->assertEquals('import', $defs[3]['acl_action']);
        $this->assertEquals($module, $defs[3]['acl_module']);
        $this->assertEquals('fa fa-upload', $defs[3]['icon']);
        $this->assertEquals("#bwc/index.php?module=Import&action=Step1&import_module=$module&return_module=$module&return_action=index", $defs[3]['route']);
    }

    public function _sidecarMenuProvider()
    {
        $builder = self::getBuilder();
        return $builder->getFilesToMakeByView('menu');
    }

    /**
     *
     * @param string $module
     * @param string $view
     * @param string $type
     * @param string $filepath
     * @dataProvider _sidecarQuickMenuProvider
     */
    public function testSidecarQuickMenuDefs($module, $view, $type, $filepath)
    {
        $this->assertFileExists($filepath, "$filepath does not exist");
        require $filepath;

        $defs = $viewdefs[$module]['base']['menu']['quickcreate'];
        $this->assertEquals('create', $defs['layout']);
        $this->assertEquals(false, $defs['visible']);
    }

    public function _sidecarQuickMenuProvider()
    {
        $builder = self::getBuilder();
        return $builder->getFilesToMakeByView('quickmenu');
    }

    public function testSidecarListMerge()
    {
        $module = 'Bugs';
        $field = 'fixed_in_release_name';
        $filepath = "custom/modules/$module/clients/base/views/list/list.php";

        // Simple first assertions
        $this->assertFileExists($filepath, "$filepath does not exist");
        require $filepath;

        // Begin proper assertions
        $defs = $viewdefs[$module]['base']['view']['list'];
        $this->assertTrue(isset($defs['panels'][0]['fields']), 'Field array is missing from the upgrade file');

        // Before assertions on our test field, make sure it's there
        $found = $this->_fieldExistsInDefs($field, $defs);
        $this->assertTrue($found, "Failed to prove existence of field '$field' in file $filepath");

        // Test field should be the seventh field in the field list
        $this->assertEquals($field, $defs['panels'][0]['fields'][6]['name']);

        // Tests merge of new defs
        $this->assertArrayHasKey('link', $defs['panels'][0]['fields'][6]);
        $this->assertFalse($defs['panels'][0]['fields'][6]['link']);

        // Tests conversion of defs
        $this->assertArrayHasKey('id', $defs['panels'][0]['fields'][6]);
        $this->assertEquals('FIXED_IN_RELEASE', $defs['panels'][0]['fields'][6]['id']);
    }

    public function testSidecarProductTemplatesRecordMerge()
    {
        $module = 'ProductTemplates';
        $filepath = "custom/modules/$module/clients/base/views/record/record.php";

        // Simple first assertions
        $this->assertFileExists($filepath, "$filepath does not exist");
        require $filepath;

        // Begin proper assertions
        $defs = $viewdefs[$module]['base']['view']['record'];
        $this->assertTrue(isset($defs['panels'][0]['fields']), 'Field array is missing from the upgrade file');

        // Before assertions on our test field, make sure it's there
        $found = $this->_fieldExistsInDefs('favorite', $defs);
        $this->assertFalse($found, "The `Favorite' field exists in file $filepath");

        // The "unit_test_c" field do not appear in vardefs, skip it.
        $found = $this->_fieldExistsInDefs('unit_test_c', $defs);
        $this->assertFalse($found, "The `unit_test_c` field was found in file $filepath");
    }


}

class SidecarMetaDataUpgraderForTest extends SidecarMetaDataUpgrader
{
    public function logUpgradeStatus($msg)
    {
        $GLOBALS['log']->info($msg);
    }
}
