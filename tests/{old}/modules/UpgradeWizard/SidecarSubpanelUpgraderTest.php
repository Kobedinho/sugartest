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


class SidecarSubpanelUpgraderTest extends PHPUnit_Framework_TestCase
{
    protected $oldDefsDefault;
    protected $oldDefsAccountDefault;
    protected $oldDefs;
    protected $expectedDefs;

    /** @var SidecarMetaDataUpgrader */
    protected $upgrader;
    protected $filesToRemove = array();

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('files');
        $this->upgrader = new SidecarMetaDataUpgrader();
        $this->filesToRemove = array();
    }

    protected function tearDown()
    {
        foreach ($this->filesToRemove as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
        SugarTestHelper::tearDown();
    }

    public function testViewDefsUpgrade()
    {
        $this->setUpViewDefs();
        $oldFileName = 'custom/modules/Accounts/metadata/subpanels/ForAwesometest.php';
        if (!is_dir(dirname($oldFileName))) {
            sugar_mkdir(dirname($oldFileName), null, true);
        }
        write_array_to_file(
            "subpanel_layout",
            $this->oldDefs,
            $oldFileName
        );
        $this->filesToRemove[] = $oldFileName;

        $fileArray = array(
            'module' => 'Accounts',
            'client' => 'base',
            'fullpath' => $oldFileName,
        );

        $subpanelUpgrader = new SidecarSubpanelViewDefUpgraderMock($this->upgrader, $fileArray);
        $subpanelUpgrader->upgrade();

        $this->assertEquals($this->expectedDefs, $subpanelUpgrader->sidecarViewdefs);
    }

    public function testLayoutDefsUpgrade()
    {
        $this->setUpLayoutDefs();
        $fileArray = array(
            'module' => 'Accounts',
            'client' => 'base',
            'filename' => 'overridecalls.php',
            'fullpath' => 'custom/Extension/modules/Accounts/Ext/Layoutdefs/overridecalls.php',
        );
        $subpanelUpgrader = new SidecarSubpanelLayoutdefsMetaDataUpgraderMock($this->upgrader, $fileArray);
        if (!is_dir("custom/modules/Accounts/metadata/")) {
            sugar_mkdir("custom/modules/Accounts/metadata/", null, true);
        }
        if (!is_dir("custom/Extension/modules/Accounts/Ext/Layoutdefs/")) {
            sugar_mkdir("custom/Extension/modules/Accounts/Ext/Layoutdefs/", null, true);
        }

        if (!is_dir("custom/modules/Accounts/Ext/Layoutdefs/")) {
            sugar_mkdir("custom/modules/Accounts/Ext/Layoutdefs/", null, true);
        }

        write_array_to_file(
            "layout_defs['Accounts']['subpanel_setup']",
            $this->oldLayoutDefs,
            "custom/modules/Accounts/metadata/subpaneldefs.php"
        );
        $this->filesToRemove[] = 'custom/modules/Accounts/metadata/subpaneldefs.php';

        write_array_to_file(
            "layout_defs['Accounts']['subpanel_setup']['calls']['override_subpanel_name']",
            'ForCalls',
            "custom/Extension/modules/Accounts/Ext/Layoutdefs/overridecalls.php"
        );
        $this->filesToRemove[] = 'custom/Extension/modules/Accounts/Ext/Layoutdefs/overridecalls.php';

        write_array_to_file(
            "layout_defs['Accounts']['subpanel_setup']['calls']['override_subpanel_name']",
            'ForCalls',
            "custom/modules/Accounts/Ext/Layoutdefs/layoutdefs.ext.php"
        );
        $this->filesToRemove[] = 'custom/modules/Accounts/Ext/Layoutdefs/layoutdefs.ext.php';

        $subpanelFileName = 'custom/modules/Calls/metadata/subpanels/ForCalls.php';
        if (!is_dir(dirname($subpanelFileName))) {
            sugar_mkdir(dirname($subpanelFileName), null, true);
        }
        write_array_to_file(
            "subpanel_layout",
            $this->oldDefs,
            $subpanelFileName
        );
        $this->filesToRemove[] = $subpanelFileName;
        
        $subpanelUpgrader->upgrade();

        $this->assertFileExists(
            "custom/Extension/modules/Accounts/Ext/clients/base/layouts/subpanels/overridecalls.php"
        );

        include "custom/Extension/modules/Accounts/Ext/clients/base/layouts/subpanels/overridecalls.php";

        $this->assertEquals($this->expectedNewLayoutDefs, $viewdefs['Accounts']['base']['layout']['subpanels']['components'][0]);
    }

    /**
     * Test of correct behaviour when subpanel file exists in package folder
     * and .
     * @result override file is created in clients 
     */
    public function testCheckCorrectSubpanelDefinition()
    {
        if (defined('SHADOW_INSTANCE_DIR')) {
            self::markTestSkipped('Does not work on Shadow Enabled System');
        }

        $this->setUpDefinitionDefs();

        // create overridden subpanel file
        $accountDefaultSubpanel = 'custom/modules/Cases/metadata/subpanels/Accountdefault.php';
        if (!is_dir(dirname($accountDefaultSubpanel))) {
            sugar_mkdir(dirname($accountDefaultSubpanel), null, true);
        }
        write_array_to_file(
            "subpanel_layout",
            $this->oldDefsAccountDefault,
            $accountDefaultSubpanel
        );
        SugarTestHelper::saveFile($accountDefaultSubpanel);

        // create default subpanel file
        $defaultSubpanel = 'modules/Cases/metadata/subpanels/default.php';
        if (!is_dir(dirname($defaultSubpanel))) {
            sugar_mkdir(dirname($defaultSubpanel), null, true);
        }
        write_array_to_file(
            "subpanel_layout",
            $this->oldDefsDefault,
            $defaultSubpanel
        );
        SugarTestHelper::saveFile($defaultSubpanel);

        $fileArray = array(
            'module' => 'Accounts',
            'client' => 'base',
            'filename' => '_overrideAccountCasesdefault.php',
            'fullpath' => 'custom/Extension/modules/Accounts/Ext/Layoutdefs/_overrideAccountCasesdefault.php',
        );

        $subpanelUpgrader = new SidecarSubpanelLayoutdefsMetaDataUpgraderMock($this->upgrader, $fileArray);

        if (!is_dir("modules/Accounts/metadata/")) {
            sugar_mkdir("custom/modules/Accounts/metadata/", null, true);
        }
        if (!is_dir("custom/Extension/modules/Accounts/Ext/Layoutdefs/")) {
            sugar_mkdir("custom/Extension/modules/Accounts/Ext/Layoutdefs/", null, true);
        }

        if (!is_dir("custom/modules/Accounts/Ext/Layoutdefs/")) {
            sugar_mkdir("custom/modules/Accounts/Ext/Layoutdefs/", null, true);
        }

        write_array_to_file(
            "layout_defs['Accounts']['subpanel_setup']",
            $this->testLayoutDefs,
            "custom/modules/Accounts/metadata/subpaneldefs.php"
        );
        SugarTestHelper::saveFile('custom/modules/Accounts/metadata/subpaneldefs.php');

        // put overridden_subpanel_name with exist link to Accountdefault
        write_array_to_file(
            "layout_defs['Accounts']['subpanel_setup']['cases']['override_subpanel_name']",
            'Accountdefault',
            "custom/Extension/modules/Accounts/Ext/Layoutdefs/_overrideAccountCasesdefault.php"
        );
        SugarTestHelper::saveFile('custom/Extension/modules/Accounts/Ext/Layoutdefs/_overrideAccountCasesdefault.php');

        write_array_to_file(
            "layout_defs['Accounts']['subpanel_setup']['cases']['override_subpanel_name']",
            'Accountdefault',
            "custom/modules/Accounts/Ext/Layoutdefs/layoutdefs.ext.php"
        );
        SugarTestHelper::saveFile('custom/modules/Accounts/Ext/Layoutdefs/layoutdefs.ext.php');

        // Upgrade will check if override_subpanel_name link exists and if appropriate subpanel file exists 
        // in package /subpanels/ folder.
        $subpanelUpgrader->upgrade();

        $this->assertNotNull($subpanelUpgrader->getSidecarViewDefs());

        // If overridden link is present, it will be used and file will be created in clients/ after upgrade.
        $this->assertFileExists(
            "custom/Extension/modules/Accounts/Ext/clients/base/layouts/subpanels/_overrideAccountCasesdefault.php"
        );

        SugarTestHelper::saveFile('custom/Extension/modules/Accounts/Ext/clients/base/layouts/subpanels/_overrideAccountCasesdefault.php');
    }

    /**
     * Test of incorrect behaviour when subpanel has not have a definition file in package,
     * but relation overridden to non-exists subpanel link.
     * @result override file is ignored and did not created in clients
     */
    public function testCheckIncorrectSubpanelDefinition()
    {
        if (defined('SHADOW_INSTANCE_DIR')) {
            self::markTestSkipped('Does not work on Shadow Enabled System');
        }
        
        $this->setUpDefinitionDefs();

        // create default subpanel file
        $defaultSubpanel = 'modules/Calls/metadata/subpanels/default.php';
        if (!is_dir(dirname($defaultSubpanel))) {
            sugar_mkdir(dirname($defaultSubpanel), null, true);
        }
        write_array_to_file(
            "subpanel_layout",
            $this->oldDefsDefault,
            $defaultSubpanel
        );
        SugarTestHelper::saveFile($defaultSubpanel);

        $fileArray = array(
            'module' => 'Accounts',
            'client' => 'base',
            'filename' => '_overrideAccountCallsdefault.php',
            'fullpath' => 'custom/Extension/modules/Accounts/Ext/Layoutdefs/_overrideAccountCallsdefault.php',
        );

        $subpanelUpgrader = new SidecarSubpanelLayoutdefsMetaDataUpgraderMock($this->upgrader, $fileArray);

        if (!is_dir("modules/Accounts/metadata/")) {
            sugar_mkdir("custom/modules/Accounts/metadata/", null, true);
        }
        if (!is_dir("custom/Extension/modules/Accounts/Ext/Layoutdefs/")) {
            sugar_mkdir("custom/Extension/modules/Accounts/Ext/Layoutdefs/", null, true);
        }
        if (!is_dir("custom/modules/Accounts/Ext/Layoutdefs/")) {
            sugar_mkdir("custom/modules/Accounts/Ext/Layoutdefs/", null, true);
        }

        write_array_to_file(
            "layout_defs['Accounts']['subpanel_setup']",
            $this->testLayoutDefs,
            "custom/modules/Accounts/metadata/subpaneldefs.php"
        );
        SugarTestHelper::saveFile('custom/modules/Accounts/metadata/subpaneldefs.php');

        // put overridden_subpanel_name with non-existent link Accountdefault
        write_array_to_file(
            "layout_defs['Accounts']['subpanel_setup']['calls']['override_subpanel_name']",
            'Accountdefault',
            "custom/Extension/modules/Accounts/Ext/Layoutdefs/_overrideAccountCallsdefault.php"
        );
        SugarTestHelper::saveFile('custom/Extension/modules/Accounts/Ext/Layoutdefs/_overrideAccountCallsdefault.php');

        write_array_to_file(
            "layout_defs['Accounts']['subpanel_setup']['calls']['override_subpanel_name']",
            'Accountdefault',
            "custom/modules/Accounts/Ext/Layoutdefs/layoutdefs.ext.php"
        );
        SugarTestHelper::saveFile('custom/modules/Accounts/Ext/Layoutdefs/layoutdefs.ext.php');

        // Upgrade will check if override_subpanel_name link exists and if appropriate subpanel file exists 
        // in package /subpanels/ folder.
        $subpanelUpgrader->upgrade();

        // If overridden link is not present, it will be ignored and file wouldn't be created at all.
        $this->assertFileNotExists(
            "custom/Extension/modules/Accounts/Ext/clients/base/layouts/subpanels/_overrideAccountCallsdefault.php"
        );
    }

    public function setUpDefinitionDefs() {
        $this->testLayoutDefs = array(
            'cases' => array(
                'module' => 'Cases',
                'subpanel_name' => 'default',
                'get_subpanel_data' => 'cases',
            ),
        );

        //
        $this->oldDefsDefault = array(
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopCreateButton'),
                array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'Cases'),
            ),

            'where' => '',

            'list_fields' => array(
                'name'=>array(
                    'vname' => 'LBL_NAME',
                    'widget_class' => 'SubPanelDetailViewLink',
                    'width' => '45%',
                ),
                'date_modified'=>array(
                    'vname' => 'LBL_DATE_MODIFIED',
                    'width' => '45%',
                ),
                'edit_button'=>array(
                    'widget_class' => 'SubPanelEditButton',
                    'module' => 'Calls',
                    'width' => '4%',
                ),
                'remove_button'=>array(
                    'widget_class' => 'SubPanelRemoveButton',
                    'module' => 'Calls',
                    'width' => '5%',
                ),
            ),
        );

        $this->oldDefsAccountDefault = array(
            'list_fields' => array(
                'name' =>
                    array(
                        'vname' => 'LBL_NAME',
                        'widget_class' => 'SubPanelDetailViewLink',
                        'width' => '15%',
                        'default' => true,
                    ),
                'reseller_type' =>
                    array(
                        'type' => 'enum',
                        'studio' => 'visible',
                        'vname' => 'LBL_RESELLER_TYPE',
                        'width' => '10%',
                        'default' => true,
                    ),
                'date_modified' =>
                    array(
                        'vname' => 'LBL_DATE_MODIFIED',
                        'width' => '5%',
                        'default' => true,
                    ),
                'edit_button' =>
                    array(
                        'widget_class' => 'SubPanelEditButton',
                        'module' => 'Calls',
                        'width' => '4%',
                        'default' => true,
                    ),
                'remove_button' =>
                    array(
                        'widget_class' => 'SubPanelRemoveButton',
                        'module' => 'Calls',
                        'width' => '5%',
                        'default' => true,
                    ),
            )
        );
    }
    
    public function setUpLayoutDefs()
    {
        $this->oldLayoutDefs = array(
            'calls' => array(
                'module' => 'Calls',
                'subpanel_name' => 'ForHistory',
                'get_subpanel_data' => 'calls',
            ),
        );

        $this->expectedNewLayoutDefs = array(
            'override_subpanel_list_view' => array(
                'view' => 'subpanel-for-calls',
                'link' => 'calls',
            ),
        );

    }

    public function setUpViewDefs()
    {
        // setup old defs
        $this->oldDefs = array(
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopCreateButton'),
                array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'Accounts'),
            ),
            'where' => '',
            'list_fields' => array(
                'name' =>
                array(
                    'vname' => 'LBL_LIST_ACCOUNT_NAME',
                    'widget_class' => 'SubPanelDetailViewLink',
                    'width' => '45%',
                    'default' => true,
                ),
                'billing_address_city' =>
                array(
                    'vname' => 'LBL_LIST_CITY',
                    'width' => '20%',
                    'default' => true,
                ),
                'billing_address_country' =>
                array(
                    'type' => 'varchar',
                    'vname' => 'LBL_BILLING_ADDRESS_COUNTRY',
                    'width' => '7%',
                    'default' => true,
                ),
                'phone_office' =>
                array(
                    'vname' => 'LBL_LIST_PHONE',
                    'width' => '20%',
                    'default' => true,
                ),
                'edit_button' =>
                array(
                    'vname' => 'LBL_EDIT_BUTTON',
                    'widget_class' => 'SubPanelEditButton',
                    'width' => '4%',
                    'default' => true,
                ),
                'remove_button' =>
                array(
                    'vname' => 'LBL_REMOVE',
                    'widget_class' => 'SubPanelRemoveButtonAccount',
                    'width' => '4%',
                    'default' => true,
                ),
            )
        );

        $this->expectedDefs = array(
            'panels' =>
            array(
                array(
                    'name' => 'panel_header',
                    'label' => 'LBL_PANEL_1',
                    'fields' =>
                    array(
                        array(
                            'default' => true,
                            'label' => 'LBL_LIST_ACCOUNT_NAME',
                            'enabled' => true,
                            'name' => 'name',
                            'link' => true,
                            'type' => 'name'
                        ),
                        array(
                            'default' => true,
                            'label' => 'LBL_LIST_CITY',
                            'enabled' => true,
                            'name' => 'billing_address_city',
                        ),
                        array(
                            'type' => 'varchar',
                            'default' => true,
                            'label' => 'LBL_BILLING_ADDRESS_COUNTRY',
                            'enabled' => true,
                            'name' => 'billing_address_country',
                        ),
                        array(
                            'default' => true,
                            'label' => 'LBL_LIST_PHONE',
                            'enabled' => true,
                            'name' => 'phone_office',
                            'type' => 'phone'
                        ),
                    ),
                ),
            ),
            'type' => 'subpanel-list'
        );
    }
}

class SidecarSubpanelViewDefUpgraderMock extends SidecarSubpanelMetaDataUpgrader
{
    public $sidecarViewdefs  = 'bad default';

    public function handleSave()
    {
        // do nothing
    }
}

class SidecarSubpanelLayoutdefsMetaDataUpgraderMock extends SidecarLayoutdefsMetaDataUpgrader
{
    public function __construct(SidecarMetaDataUpgrader $upgrader, Array $file) {
        // restore self::$supanelData for each test. 
        self::$supanelData = array();
        
        parent::__construct($upgrader, $file);
    }
}
