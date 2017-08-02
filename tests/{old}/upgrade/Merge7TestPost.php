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
require_once "tests/{old}/upgrade/UpgradeTestCase.php";

class Merge7TestPost extends UpgradeTestCase
{

    protected $new_dir;

    public function setUp()
    {
        parent::setUp();
        $this->upgrader->setVersions("7.0.0", "ent", "7.2.0", "ent");
    }

    public function tearDown()
    {
        parent::tearDown();
        rmdir_recursive("modules/Accounts/clients/test");
        rmdir_recursive("custom/modules/Accounts/clients/test");
    }


    protected function createView($viewname, $data, $prefix = '')
    {
        $filename = "modules/Accounts/clients/test/views/$viewname/$viewname.php";
        if ($prefix) {
            $filename = "$prefix/$filename";
        }
        $pdata = array('panels' => $data);
        mkdir_recursive(dirname($filename));
        SugarTestHelper::saveFile($filename);
        write_array_to_file("viewdefs['Accounts']['test']['view']['$viewname']", $pdata, $filename);
    }

    public function mergeData()
    {
        return array(
            // add field with out panel name, but panel label
            array(
                // pre
                array(
                    array(
                        'label' => 'panel1',
                        'fields' => array('email', 'phone', 'fax')
                    )
                ),
                // post
                array(
                    array(
                        'label' => 'panel1',
                        'fields' => array('email', 'phone', 'fax', 'description')
                    )
                ),
                // custom
                array(
                    array(
                        'label' => 'panel1',
                        'fields' => array('email', 'phone', 'fax', "custom_c")
                    )
                ),
                // result
                array(
                    array(
                        'label' => 'panel1',
                        'fields' => array('email', 'phone', 'fax', "custom_c", 'description')
                    )
                ),
            ),
            // add field
            array(
                // pre
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', 'fax')
                    )
                ),
                // post
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', 'fax', 'description')
                    )
                ),
                // custom
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', 'fax', "custom_c")
                    )
                ),
                // result
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', 'fax', "custom_c", 'description')
                    )
                ),
            ),
            // add field to another panel
            array(
                // pre
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('email')
                    ),
                    array(
                        'name' => 'panel2',
                        'fields' => array('phone', 'fax')
                    ),
                ),
                // post
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('email')
                    ),
                    array(
                        'name' => 'panel2',
                        'fields' => array('phone', 'fax', array("name" => 'description', "type" => "text"))
                    ),
                ),
                // custom
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('custom_c')
                    ),
                    array(
                        'name' => 'panel2',
                        'fields' => array('phone', 'fax')
                    ),
                ),
                // result
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('custom_c')
                    ),
                    array(
                        'name' => 'panel2',
                        'fields' => array('phone', 'fax', array("name" => 'description', "type" => "text"))
                    ),
                ),
            ),
            // remove field
            array(
                // pre
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', 'fax', array("name" => "address"))
                    )
                ),
                // post
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', 'description')
                    )
                ),
                // custom
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', 'fax', "custom_c")
                    ),
                    array(
                        "name" => "panel2",
                        'fields' => array(array("name" => "address"))
                    )
                ),
                // result
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', "custom_c", 'description')
                    ),
                    array(
                        "name" => "panel2",
                        'fields' => array()
                    )
                ),
            ),
            // field changed in new
            array(
                // pre
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', array("name" => 'fax', "type" => "text"))
                    )
                ),
                // post
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', array("name" => 'fax', "type" => "phone"), 'description')
                    )
                ),
                // custom
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', array("name" => 'fax', "type" => "text"), "custom_c")
                    )
                ),
                // result
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array(
                            'email', 'phone', array("name" => 'fax', "type" => "phone"), "custom_c", 'description'
                        )
                    )
                ),
            ),
            // field changed in custom
            array(
                // pre
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', array("name" => 'fax', "type" => "text"))
                    )
                ),
                // post
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', array("name" => 'fax', "type" => "phone"), 'description')
                    )
                ),
                // custom
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', array("name" => 'fax', "type" => "enum"), "custom_c")
                    )
                ),
                // result
                array(
                    array(
                        'name' => 'panel1',
                        'fields' => array(
                            'email', 'phone', array("name" => 'fax', "type" => "enum"), "custom_c", 'description'
                        )
                    )
                ),
            ),

        );
    }

    /**
     * Test for Merge7Templates
     * @dataProvider mergeData
     */
    public function testMerge7Pre($pre_data, $post_data, $custom_data, $result)
    {
        $this->createView("mergetest", $post_data);
        $this->createView("mergetest", $custom_data, "custom");
        $this->upgrader->state['for_merge']["modules/Accounts/clients/test/views/mergetest/mergetest.php"]['Accounts']['test']['view']['mergetest']['panels'] = $pre_data;

        $script = $this->upgrader->getScript("post", "7_Merge7Templates");
        $script->run();
        $this->assertFileExists("custom/modules/Accounts/clients/test/views/mergetest/mergetest.php");
        include 'custom/modules/Accounts/clients/test/views/mergetest/mergetest.php';
        $this->assertEquals($result, $viewdefs['Accounts']['test']['view']['mergetest']['panels']);
    }

    /**
     * Tests merging of non panel defs in the merge upgrader
     *
     * @param array   $old Old viewdefs
     * @param array   $new New viewdefs
     * @param array   $cst Custom viewdefs
     * @param boolean $noChange If there are changes to be picked up
     * @param boolean $needSave If the changes require a save
     * @param array   $exp Expected result
     *
     * @dataProvider getMergeTestData
     */
    public function testMergeOtherDefs($old, $new, $cst, $noChange, $needSave, $exp)
    {
        // Set some stuff that both the test and the upgrader need
        $module = 'Test1';
        $client = 'foo';
        $view = 'bar';

        // Get the script and set some vars
        $script = $this->upgrader->getScript("post", "7_Merge7Templates");
        $script->moduleName = $module;
        $script->clientType = $client;
        $script->viewName = $view;

        // Test change checker... for testing, this should always be false
        $test = $script->defsUnchanged($old, $new, $cst);
        $this->assertEquals($noChange, $test, "Unexpected defsUnchanged result");

        // Make "real" defs out of the test data
        $oldDefs[$module][$client]['view'][$view] = $old;
        $newDefs[$module][$client]['view'][$view] = $new;
        $cstDefs[$module][$client]['view'][$view] = $cst;

        // Set the actual expected to the full array path
        $expect[$module][$client]['view'][$view] = $exp;

        // Test merge piece
        $actual = $script->mergeOtherDefs($oldDefs, $newDefs, $cstDefs);
        $this->assertEquals($expect, $actual);

        // Test the save flag... this should always be true
        $this->assertEquals($needSave, $script->needSave, "Unexpected needSave result");
    }

    /**
     * Tests sanitization of viewdefs at the top level after merge.
     *
     * @see SC-5024
     * @param array $input
     * @param array $expect
     * @dataProvider getSantizeDataProvider
     */
    public function testSanitizeTopLevelDefElements($input, $expect)
    {
        $script = $this->upgrader->getScript("post", "7_Merge7Templates");
        $actual = $script->sanitizeTopLevelDefElements($input);
        $this->assertEquals($actual, $expect);
    }

    public function getSantizeDataProvider()
    {
        return array(
            // Test empty top level viewdefs element is removed
            array(
                'input' => array(
                    'buttons' => array(),
                    'panels' => array(
                        array(
                            'name' => 'panel1',
                            'fields' => array(
                                'test1',
                                'test2',
                            ),
                        ),
                    ),
                    'templateMeta' => array(
                        'maxColumns' => '1',
                        'widths' => array(
                            array(
                                'label' => '10',
                                'field' => '30',
                            ),
                        ),
                    ),
                ),
                'expect' => array(
                    'panels' => array(
                        array(
                            'name' => 'panel1',
                            'fields' => array(
                                'test1',
                                'test2',
                            ),
                        ),
                    ),
                    'templateMeta' => array(
                        'maxColumns' => '1',
                        'widths' => array(
                            array(
                                'label' => '10',
                                'field' => '30',
                            ),
                        ),
                    ),
                ),
            ),
            // Test not empty top level viewdefs element not changed
            array(
                'input' => array(
                    'buttons' => array('Test not empty'),
                    'panels' => array(
                        array(
                            'name' => 'panel1',
                            'fields' => array(
                                'test1',
                                'test2',
                            ),
                        ),
                    ),
                    'templateMeta' => array(
                        'maxColumns' => '1',
                        'widths' => array(
                            array(
                                'label' => '10',
                                'field' => '30',
                            ),
                        ),
                    ),
                ),
                'expect' => array(
                    'buttons' => array('Test not empty'),
                    'panels' => array(
                        array(
                            'name' => 'panel1',
                            'fields' => array(
                                'test1',
                                'test2',
                            ),
                        ),
                    ),
                    'templateMeta' => array(
                        'maxColumns' => '1',
                        'widths' => array(
                            array(
                                'label' => '10',
                                'field' => '30',
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    public function getMergeTestData()
    {
        return array(
            // Old/new same, no custom... take old/new
            array(
                'old' => array(
                    'buttons' => array(
                        array(
                            'type' => 'button',
                            'name' => 'cancel_button',
                            'label' => 'LBL_CANCEL_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                            'showOn' => 'edit',
                        ),
                        array(
                            'type' => 'rowaction',
                            'event' => 'button:save_button:click',
                            'name' => 'save_button',
                            'label' => 'LBL_SAVE_BUTTON_LABEL',
                            'css_class' => 'btn btn-primary',
                            'showOn' => 'edit',
                            'acl_action' => 'edit',
                        ),
                    ),
                ),
                'new' => array(
                    'buttons' => array(
                        array(
                            'type' => 'button',
                            'name' => 'cancel_button',
                            'label' => 'LBL_CANCEL_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                            'showOn' => 'edit',
                        ),
                        array(
                            'type' => 'rowaction',
                            'event' => 'button:save_button:click',
                            'name' => 'save_button',
                            'label' => 'LBL_SAVE_BUTTON_LABEL',
                            'css_class' => 'btn btn-primary',
                            'showOn' => 'edit',
                            'acl_action' => 'edit',
                        ),
                    ),
                ),
                'cst' => array(),
                'noChange' => true,
                'needSave' => false,
                'expect' => array(),
            ),
            // Old/New same, Custom different, merge custom with changes
            array(
                'old' => array(
                    'a1' => array(
                        'id' => 'record_view',
                        'defaults' => array(
                            'show_more' => 'more'
                        ),
                    ),
                    'a2' => array(
                        'node' => array(
                            'foo' => 'bar',
                        ),
                    ),
                ),
                'new' => array(
                    'a1' => array(
                        'id' => 'record_view',
                        'defaults' => array(
                            'show_more' => 'more'
                        ),
                    ),
                    'a2' => array(
                        'node' => array(
                            'foo' => 'bar',
                        ),
                    ),
                ),
                'cst' => array(
                    'a1' => array(
                        'id' => 'list_view',
                    ),
                ),
                'noChange' => false,
                'needSave' => false,
                'expect' => array(
                    'a1' => array(
                        'id' => 'list_view',
                    ),
                ),
            ),
            // Old/New different, no Custom, take changes between new and old
            array(
                'old' => array(
                    'buttons' => array(
                        array(
                            'type' => 'button',
                            'name' => 'cancel_button',
                            'label' => 'LBL_CANCEL_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                            'showOn' => 'edit',
                        ),
                        array(
                            'type' => 'rowaction',
                            'event' => 'button:save_button:click',
                            'name' => 'save_button',
                            'label' => 'LBL_SAVE_BUTTON_LABEL',
                            'css_class' => 'btn btn-primary',
                            'showOn' => 'edit',
                            'acl_action' => 'edit',
                        ),
                    ),
                ),
                'new' => array(
                    'buttons' => array(
                        array(
                            'type' => 'button',
                            'name' => 'modify_button',
                            'label' => 'LBL_MODIFY_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                        ),
                    ),
                ),
                'cst' => array(),
                'noChange' => false,
                'needSave' => false,
                'expect' => array(),
            ),
            // Old, new and Custom all different, merge custom with changes
            array(
                'old' => array(
                    'buttons' => array(
                        array(
                            'type' => 'button',
                            'name' => 'cancel_button',
                            'label' => 'LBL_CANCEL_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                            'showOn' => 'edit',
                        ),
                        array(
                            'type' => 'rowaction',
                            'event' => 'button:save_button:click',
                            'name' => 'save_button',
                            'label' => 'LBL_SAVE_BUTTON_LABEL',
                            'css_class' => 'btn btn-primary',
                            'showOn' => 'edit',
                            'acl_action' => 'edit',
                        ),
                    ),
                ),
                'new' => array(
                    'buttons' => array(
                        array(
                            'type' => 'button',
                            'name' => 'modify_button',
                            'label' => 'LBL_MODIFY_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                        ),
                    ),
                ),
                'cst' => array(
                    'buttons' => array(
                        array(
                            'type' => 'button',
                            'name' => 'send_button',
                            'label' => 'LBL_SEND_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                            'showOn' => array('edit', 'record'),
                        ),
                    ),
                ),
                'noChange' => false,
                'needSave' => true,
                'expect' => array(
                    'buttons' => array(
                        array(
                            'type' => 'button',
                            'name' => 'modify_button',
                            'label' => 'LBL_MODIFY_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                        ),
                        array(
                            'type' => 'button',
                            'name' => 'send_button',
                            'label' => 'LBL_SEND_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                            'showOn' => array('edit', 'record'),
                        ),
                    ),
                ),
            ),
            // From ticket # BR-1804... 
            // old is 7.2.0 OOTB Accounts record viewdefs
            // new is 7.2.1 OOTB Accounts record viewdefs
            // cst is 7.2.0 custom Accounts record viewdefs
            // expect contains panels, last_state and buttons
            array(
                'old' => array(
                    'panels' => array(
                        array(
                            'name' => 'panel_header',
                            'label' => 'LBL_PANEL_HEADER',
                            'header' => true,
                            'fields' => array(
                                array(
                                    'name' => 'picture',
                                    'type' => 'avatar',
                                    'size' => 'large',
                                    'dismiss_label' => true,
                                    'readonly' => true,
                                ),
                                'name',
                                array(
                                    'name' => 'favorite',
                                    'label' => 'LBL_FAVORITE',
                                    'type' => 'favorite',
                                    'dismiss_label' => true,
                                ),
                                array(
                                    'name' => 'follow',
                                    'label' => 'LBL_FOLLOW',
                                    'type' => 'follow',
                                    'readonly' => true,
                                    'dismiss_label' => true,
                                ),
                            )
                        ),
                        array(
                            'name' => 'panel_body',
                            'label' => 'LBL_RECORD_BODY',
                            'columns' => 2,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => array(
                                'website',
                                'industry',
                                'parent_name',
                                'account_type',
                                'assigned_user_name',
                                'phone_office',
                            ),
                        ),
                        array(
                            'name' => 'panel_hidden',
                            'label' => 'LBL_RECORD_SHOWMORE',
                            'hide' => true,
                            'columns' => 2,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => array(
                                array(
                                    'name' => 'billing_address',
                                    'type' => 'fieldset',
                                    'css_class' => 'address',
                                    'label' => 'LBL_BILLING_ADDRESS',
                                    'fields' => array(
                                        array(
                                            'name' => 'billing_address_street',
                                            'css_class' => 'address_street',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_STREET',
                                        ),
                                        array(
                                            'name' => 'billing_address_city',
                                            'css_class' => 'address_city',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_CITY',
                                        ),
                                        array(
                                            'name' => 'billing_address_state',
                                            'css_class' => 'address_state',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_STATE',
                                        ),
                                        array(
                                            'name' => 'billing_address_postalcode',
                                            'css_class' => 'address_zip',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_POSTALCODE',
                                        ),
                                        array(
                                            'name' => 'billing_address_country',
                                            'css_class' => 'address_country',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_COUNTRY',
                                        ),
                                    ),
                                ),
                                array(
                                    'name' => 'shipping_address',
                                    'type' => 'fieldset',
                                    'css_class' => 'address',
                                    'label' => 'LBL_SHIPPING_ADDRESS',
                                    'fields' => array(
                                        array(
                                            'name' => 'shipping_address_street',
                                            'css_class' => 'address_street',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_STREET',
                                        ),
                                        array(
                                            'name' => 'shipping_address_city',
                                            'css_class' => 'address_city',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_CITY',
                                        ),
                                        array(
                                            'name' => 'shipping_address_state',
                                            'css_class' => 'address_state',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_STATE',
                                        ),
                                        array(
                                            'name' => 'shipping_address_postalcode',
                                            'css_class' => 'address_zip',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_POSTALCODE',
                                        ),
                                        array(
                                            'name' => 'shipping_address_country',
                                            'css_class' => 'address_country',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_COUNTRY',
                                        ),
                                        array(
                                            'name' => 'copy',
                                            'label' => 'NTC_COPY_BILLING_ADDRESS',
                                            'type' => 'copy',
                                            'mapping' => array(
                                                'billing_address_street' => 'shipping_address_street',
                                                'billing_address_city' => 'shipping_address_city',
                                                'billing_address_state' => 'shipping_address_state',
                                                'billing_address_postalcode' => 'shipping_address_postalcode',
                                                'billing_address_country' => 'shipping_address_country',
                                            ),
                                        ),
                                    ),
                                ),
                                array(
                                    'name' => 'phone_alternate',
                                    'label' => 'LBL_OTHER_PHONE',
                                ),
                                'email',
                                'phone_fax',
                                'campaign_name',
                                'twitter',
                                array(
                                    'name' => 'description',
                                    'span' => 12,
                                ),
                                'sic_code',
                                'ticker_symbol',
                                'annual_revenue',
                                'employees',
                                'ownership',
                                'rating',

                                array(
                                    'name' => 'duns_num',
                                    'readonly' => true,
                                ),
                                array(
                                    'name' => 'date_entered_by',
                                    'readonly' => true,
                                    'type' => 'fieldset',
                                    'label' => 'LBL_DATE_ENTERED',
                                    'fields' => array(
                                        array(
                                            'name' => 'date_entered',
                                        ),
                                        array(
                                            'type' => 'label',
                                            'default_value' => 'LBL_BY',
                                        ),
                                        array(
                                            'name' => 'created_by_name',
                                        ),
                                    ),
                                ),
                                'team_name',
                                array(
                                    'name' => 'date_modified_by',
                                    'readonly' => true,
                                    'type' => 'fieldset',
                                    'label' => 'LBL_DATE_MODIFIED',
                                    'fields' => array(
                                        array(
                                            'name' => 'date_modified',
                                        ),
                                        array(
                                            'type' => 'label',
                                            'default_value' => 'LBL_BY',
                                        ),
                                        array(
                                            'name' => 'modified_by_name',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'new' => array(
                    'buttons' => array(
                        array(
                            'type' => 'button',
                            'name' => 'cancel_button',
                            'label' => 'LBL_CANCEL_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                            'showOn' => 'edit',
                        ),
                        array(
                            'type' => 'rowaction',
                            'event' => 'button:save_button:click',
                            'name' => 'save_button',
                            'label' => 'LBL_SAVE_BUTTON_LABEL',
                            'css_class' => 'btn btn-primary',
                            'showOn' => 'edit',
                            'acl_action' => 'edit',
                        ),
                        array(
                            'type' => 'actiondropdown',
                            'name' => 'main_dropdown',
                            'primary' => true,
                            'showOn' => 'view',
                            'buttons' => array(
                                array(
                                    'type' => 'rowaction',
                                    'event' => 'button:edit_button:click',
                                    'name' => 'edit_button',
                                    'label' => 'LBL_EDIT_BUTTON_LABEL',
                                    'acl_action' => 'edit',
                                ),
                                array(
                                    'type' => 'shareaction',
                                    'name' => 'share',
                                    'label' => 'LBL_RECORD_SHARE_BUTTON',
                                    'acl_action' => 'view',
                                ),
                                array(
                                    'type' => 'pdfaction',
                                    'name' => 'download-pdf',
                                    'label' => 'LBL_PDF_VIEW',
                                    'action' => 'download',
                                    'acl_action' => 'view',
                                ),
                                array(
                                    'type' => 'pdfaction',
                                    'name' => 'email-pdf',
                                    'label' => 'LBL_PDF_EMAIL',
                                    'action' => 'email',
                                    'acl_action' => 'view',
                                ),
                                array(
                                    'type' => 'divider',
                                ),
                                array(
                                    'type' => 'rowaction',
                                    'event' => 'button:find_duplicates_button:click',
                                    'name' => 'find_duplicates_button',
                                    'label' => 'LBL_DUP_MERGE',
                                    'acl_action' => 'edit',
                                ),
                                array(
                                    'type' => 'rowaction',
                                    'event' => 'button:duplicate_button:click',
                                    'name' => 'duplicate_button',
                                    'label' => 'LBL_DUPLICATE_BUTTON_LABEL',
                                    'acl_module' => 'Accounts',
                                    'acl_action' => 'create',
                                ),
                                array(
                                    'type' => 'rowaction',
                                    'event' => 'button:historical_summary_button:click',
                                    'name' => 'historical_summary_button',
                                    'label' => 'LBL_HISTORICAL_SUMMARY',
                                    'acl_action' => 'view',
                                ),
                                array(
                                    'type' => 'rowaction',
                                    'event' => 'button:audit_button:click',
                                    'name' => 'audit_button',
                                    'label' => 'LNK_VIEW_CHANGE_LOG',
                                    'acl_action' => 'view',
                                ),
                                array(
                                    'type' => 'divider',
                                ),
                                array(
                                    'type' => 'rowaction',
                                    'event' => 'button:delete_button:click',
                                    'name' => 'delete_button',
                                    'label' => 'LBL_DELETE_BUTTON_LABEL',
                                    'acl_action' => 'delete',
                                ),
                            ),
                        ),
                        array(
                            'name' => 'sidebar_toggle',
                            'type' => 'sidebartoggle',
                        ),
                    ),
                    'panels' => array(
                        array(
                            'name' => 'panel_header',
                            'label' => 'LBL_PANEL_HEADER',
                            'header' => true,
                            'fields' => array(
                                array(
                                    'name' => 'picture',
                                    'type' => 'avatar',
                                    'size' => 'large',
                                    'dismiss_label' => true,
                                    'readonly' => true,
                                ),
                                'name',
                                array(
                                    'name' => 'favorite',
                                    'label' => 'LBL_FAVORITE',
                                    'type' => 'favorite',
                                    'dismiss_label' => true,
                                ),
                                array(
                                    'name' => 'follow',
                                    'label' => 'LBL_FOLLOW',
                                    'type' => 'follow',
                                    'readonly' => true,
                                    'dismiss_label' => true,
                                ),
                            )
                        ),
                        array(
                            'name' => 'panel_body',
                            'label' => 'LBL_RECORD_BODY',
                            'columns' => 2,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => array(
                                'website',
                                'industry',
                                'parent_name',
                                'account_type',
                                'assigned_user_name',
                                'phone_office',
                            ),
                        ),
                        array(
                            'name' => 'panel_hidden',
                            'label' => 'LBL_RECORD_SHOWMORE',
                            'hide' => true,
                            'columns' => 2,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => array(
                                array(
                                    'name' => 'billing_address',
                                    'type' => 'fieldset',
                                    'css_class' => 'address',
                                    'label' => 'LBL_BILLING_ADDRESS',
                                    'fields' => array(
                                        array(
                                            'name' => 'billing_address_street',
                                            'css_class' => 'address_street',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_STREET',
                                        ),
                                        array(
                                            'name' => 'billing_address_city',
                                            'css_class' => 'address_city',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_CITY',
                                        ),
                                        array(
                                            'name' => 'billing_address_state',
                                            'css_class' => 'address_state',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_STATE',
                                        ),
                                        array(
                                            'name' => 'billing_address_postalcode',
                                            'css_class' => 'address_zip',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_POSTALCODE',
                                        ),
                                        array(
                                            'name' => 'billing_address_country',
                                            'css_class' => 'address_country',
                                            'placeholder' => 'LBL_BILLING_ADDRESS_COUNTRY',
                                        ),
                                    ),
                                ),
                                array(
                                    'name' => 'shipping_address',
                                    'type' => 'fieldset',
                                    'css_class' => 'address',
                                    'label' => 'LBL_SHIPPING_ADDRESS',
                                    'fields' => array(
                                        array(
                                            'name' => 'shipping_address_street',
                                            'css_class' => 'address_street',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_STREET',
                                        ),
                                        array(
                                            'name' => 'shipping_address_city',
                                            'css_class' => 'address_city',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_CITY',
                                        ),
                                        array(
                                            'name' => 'shipping_address_state',
                                            'css_class' => 'address_state',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_STATE',
                                        ),
                                        array(
                                            'name' => 'shipping_address_postalcode',
                                            'css_class' => 'address_zip',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_POSTALCODE',
                                        ),
                                        array(
                                            'name' => 'shipping_address_country',
                                            'css_class' => 'address_country',
                                            'placeholder' => 'LBL_SHIPPING_ADDRESS_COUNTRY',
                                        ),
                                        array(
                                            'name' => 'copy',
                                            'label' => 'NTC_COPY_BILLING_ADDRESS',
                                            'type' => 'copy',
                                            'mapping' => array(
                                                'billing_address_street' => 'shipping_address_street',
                                                'billing_address_city' => 'shipping_address_city',
                                                'billing_address_state' => 'shipping_address_state',
                                                'billing_address_postalcode' => 'shipping_address_postalcode',
                                                'billing_address_country' => 'shipping_address_country',
                                            ),
                                        ),
                                    ),
                                ),
                                array(
                                    'name' => 'phone_alternate',
                                    'label' => 'LBL_OTHER_PHONE',
                                ),
                                'email',
                                'phone_fax',
                                'campaign_name',
                                'twitter',
                                array(
                                    'name' => 'description',
                                    'span' => 12,
                                ),
                                'sic_code',
                                'ticker_symbol',
                                'annual_revenue',
                                'employees',
                                'ownership',
                                'rating',

                                array(
                                    'name' => 'duns_num',
                                    'readonly' => true,
                                ),
                                array(
                                    'name' => 'date_entered_by',
                                    'readonly' => true,
                                    'type' => 'fieldset',
                                    'label' => 'LBL_DATE_ENTERED',
                                    'fields' => array(
                                        array(
                                            'name' => 'date_entered',
                                        ),
                                        array(
                                            'type' => 'label',
                                            'default_value' => 'LBL_BY',
                                        ),
                                        array(
                                            'name' => 'created_by_name',
                                        ),
                                    ),
                                ),
                                'team_name',
                                array(
                                    'name' => 'date_modified_by',
                                    'readonly' => true,
                                    'type' => 'fieldset',
                                    'label' => 'LBL_DATE_MODIFIED',
                                    'fields' => array(
                                        array(
                                            'name' => 'date_modified',
                                        ),
                                        array(
                                            'type' => 'label',
                                            'default_value' => 'LBL_BY',
                                        ),
                                        array(
                                            'name' => 'modified_by_name',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'cst' => array(
                    'panels' => array(
                        array(
                            'name' => 'panel_header',
                            'label' => 'LBL_PANEL_HEADER',
                            'header' => true,
                            'fields' => array(
                                array(
                                    'name' => 'picture',
                                    'type' => 'avatar',
                                    'size' => 'large',
                                    'dismiss_label' => true,
                                    'readonly' => true,
                                ),
                                'name',
                                array(
                                    'name' => 'favorite',
                                    'label' => 'LBL_FAVORITE',
                                    'type' => 'favorite',
                                    'dismiss_label' => true,
                                ),
                                array(
                                    'name' => 'follow',
                                    'label' => 'LBL_FOLLOW',
                                    'type' => 'follow',
                                    'readonly' => true,
                                    'dismiss_label' => true,
                                ),
                            )
                        ),
                        array(
                            'name' => 'panel_body',
                            'label' => 'LBL_RECORD_BODY',
                            'columns' => 2,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => array(
                                'website',
                                'industry',
                                'parent_name',
                                'account_type',
                                'assigned_user_name',
                                'phone_office',
                            ),
                        ),
                    ),
                    'last_state' => array(
                        'id' => 'record_view',
                        'defaults' => array(
                            'show_more' => 'more'
                        ),
                    ),
                ),
                'noChange' => false,
                'needSave' => true,
                'expect' => array(
                    'panels' => array(
                        array(
                            'name' => 'panel_header',
                            'label' => 'LBL_PANEL_HEADER',
                            'header' => true,
                            'fields' => array(
                                array(
                                    'name' => 'picture',
                                    'type' => 'avatar',
                                    'size' => 'large',
                                    'dismiss_label' => true,
                                    'readonly' => true,
                                ),
                                'name',
                                array(
                                    'name' => 'favorite',
                                    'label' => 'LBL_FAVORITE',
                                    'type' => 'favorite',
                                    'dismiss_label' => true,
                                ),
                                array(
                                    'name' => 'follow',
                                    'label' => 'LBL_FOLLOW',
                                    'type' => 'follow',
                                    'readonly' => true,
                                    'dismiss_label' => true,
                                ),
                            )
                        ),
                        array(
                            'name' => 'panel_body',
                            'label' => 'LBL_RECORD_BODY',
                            'columns' => 2,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => array(
                                'website',
                                'industry',
                                'parent_name',
                                'account_type',
                                'assigned_user_name',
                                'phone_office',
                            ),
                        ),
                    ),
                    'buttons' => array(
                        array(
                            'type' => 'button',
                            'name' => 'cancel_button',
                            'label' => 'LBL_CANCEL_BUTTON_LABEL',
                            'css_class' => 'btn-invisible btn-link',
                            'showOn' => 'edit',
                        ),
                        array(
                            'type' => 'rowaction',
                            'event' => 'button:save_button:click',
                            'name' => 'save_button',
                            'label' => 'LBL_SAVE_BUTTON_LABEL',
                            'css_class' => 'btn btn-primary',
                            'showOn' => 'edit',
                            'acl_action' => 'edit',
                        ),
                        array(
                            'type' => 'actiondropdown',
                            'name' => 'main_dropdown',
                            'primary' => true,
                            'showOn' => 'view',
                            'buttons' => array(
                                array(
                                    'type' => 'rowaction',
                                    'event' => 'button:edit_button:click',
                                    'name' => 'edit_button',
                                    'label' => 'LBL_EDIT_BUTTON_LABEL',
                                    'acl_action' => 'edit',
                                ),
                                array(
                                    'type' => 'shareaction',
                                    'name' => 'share',
                                    'label' => 'LBL_RECORD_SHARE_BUTTON',
                                    'acl_action' => 'view',
                                ),
                                array(
                                    'type' => 'pdfaction',
                                    'name' => 'download-pdf',
                                    'label' => 'LBL_PDF_VIEW',
                                    'action' => 'download',
                                    'acl_action' => 'view',
                                ),
                                array(
                                    'type' => 'pdfaction',
                                    'name' => 'email-pdf',
                                    'label' => 'LBL_PDF_EMAIL',
                                    'action' => 'email',
                                    'acl_action' => 'view',
                                ),
                                array(
                                    'type' => 'divider',
                                ),
                                array(
                                    'type' => 'rowaction',
                                    'event' => 'button:find_duplicates_button:click',
                                    'name' => 'find_duplicates_button',
                                    'label' => 'LBL_DUP_MERGE',
                                    'acl_action' => 'edit',
                                ),
                                array(
                                    'type' => 'rowaction',
                                    'event' => 'button:duplicate_button:click',
                                    'name' => 'duplicate_button',
                                    'label' => 'LBL_DUPLICATE_BUTTON_LABEL',
                                    'acl_module' => 'Accounts',
                                    'acl_action' => 'create',
                                ),
                                array(
                                    'type' => 'rowaction',
                                    'event' => 'button:historical_summary_button:click',
                                    'name' => 'historical_summary_button',
                                    'label' => 'LBL_HISTORICAL_SUMMARY',
                                    'acl_action' => 'view',
                                ),
                                array(
                                    'type' => 'rowaction',
                                    'event' => 'button:audit_button:click',
                                    'name' => 'audit_button',
                                    'label' => 'LNK_VIEW_CHANGE_LOG',
                                    'acl_action' => 'view',
                                ),
                                array(
                                    'type' => 'divider',
                                ),
                                array(
                                    'type' => 'rowaction',
                                    'event' => 'button:delete_button:click',
                                    'name' => 'delete_button',
                                    'label' => 'LBL_DELETE_BUTTON_LABEL',
                                    'acl_action' => 'delete',
                                ),
                            ),
                        ),
                        array(
                            'name' => 'sidebar_toggle',
                            'type' => 'sidebartoggle',
                        ),
                    ),
                    'last_state' => array(
                        'id' => 'record_view',
                        'defaults' => array(
                            'show_more' => 'more'
                        ),
                    ),
                ),
            ),
        );
    }
}
