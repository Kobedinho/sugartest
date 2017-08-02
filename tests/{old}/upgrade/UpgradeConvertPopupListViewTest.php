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
require_once 'tests/{old}/upgrade/UpgradeTestCase.php';
require_once 'modules/ModuleBuilder/parsers/constants.php';

/**
 * Class UpgradeConvertPopupListViewTest
 * Testing conversion popupdefs into sidecar format.
 */
class UpgradeConvertPopupListViewTest extends UpgradeTestCase
{
    /**
     * @var string
     */
    public $popupListPath;

    /**
     * @var string
     */
    public $selectionListPath;

    /**
     * @var array
     */
    public $newDefs;

    /**
     * @var string
     */
    public $module = 'Accounts';

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        if (is_file($this->popupListPath)) {
            SugarAutoLoader::unlink($this->popupListPath);
        }
        if (is_file($this->selectionListPath)) {
            SugarAutoLoader::unlink($this->selectionListPath);
        }
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Init popupdefs.
     * @param array $defs
     */
    protected function initDefs($defs)
    {
        $this->newDefs = $defs;
        $this->popupListPath = "custom/modules/{$this->module}/metadata/popupdefs.php";
        $this->selectionListPath = "custom/modules/{$this->module}" .
            '/clients/base/views/selection-list/selection-list.php';

        $parser = new PopupMetaDataParser(MB_POPUPLIST, $this->module);
        $parser->_viewdefs = $this->newDefs;
        $parser->handleSave(false);
    }

    /**
     * Test popupdefs conversion into sidecar format.
     *
     * @param array $defs
     * @param string $field
     *
     * @dataProvider defsDataProvider
     */
    public function testConvertPopupListFieldsToSidecarFormat($defs, $field)
    {
        $this->initDefs($defs);

        $script = $this->upgrader->getScript('post', '7_ConvertPopupListView');
        $script->from_version = 6.7;
        $script->to_version = 7.2;
        $script->run();

        $this->assertFileExists($this->selectionListPath);
        $sidecarParser = new SidecarListLayoutMetaDataParser(MB_SIDECARPOPUPVIEW, $this->module, null, 'base');

        $fieldDefs = $sidecarParser->panelGetField($field);
        $this->assertTrue($fieldDefs['field']['default']);
    }

    /**
     * Returns defs data.
     * @return array
     */
    public function defsDataProvider()
    {
        return array(
            array(
                array(
                    'NAME' => array(
                        'width' => '40%',
                        'label' => 'LBL_LIST_ACCOUNT_NAME',
                        'link' => true,
                        'default' => false,
                        'name' => 'name',
                    ),
                ),
                'name',
            ),
            array(
                array(
                    // Hidden by default field.
                    'DESCRIPTION' => array(
                        'type' => 'text',
                        'label' => 'LBL_DESCRIPTION',
                        'sortable' => false,
                        'width' => '10%',
                        // Name intentionally omitted.
                        'default' => true,
                    ),
                ),
                'description',
            ),
        );
    }
}
