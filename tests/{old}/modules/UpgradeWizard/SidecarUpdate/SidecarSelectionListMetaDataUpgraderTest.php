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


class SidecarSelectionListMetaDataUpgraderTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SidecarSelectionListMetaDataUpgrader
     */
    protected $selectionListUpgrader;

    protected $module = 'Accounts';
    protected $client = 'base';

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, 1));

        $file = array(
            'client' => $this->client,
            'module' => $this->module,
            'type' => 'base',
            'basename' => 'popupdefs',
            'timestamp' => null,
            'fullpath' => "modules/{$this->module}/metadata/popupdefs.php",
            'package' => null,
            'deployed' => true,
            'sidecar' => false,
            'viewtype' => 'popuplist',
        );

        $this->selectionListUpgrader = new SidecarSelectionListMetaDataUpgrader(new SidecarMetaDataUpgrader(), $file);
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Default selection-list.php should be a copy of list view.
     */
    public function testConvertLegacyViewDefsToSidecar()
    {
        $this->selectionListUpgrader->convertLegacyViewDefsToSidecar();

        require "modules/{$this->module}/metadata/listviewdefs.php";
        $actualFieldNames = array_keys($listViewDefs[$this->module]);

        $sidecarListViewDefs = $this->selectionListUpgrader->getSidecarViewDefs();
        $expectedFieldNames = array_map(function ($val) {
                return $val['name'];
            },
            $sidecarListViewDefs[$this->module][$this->client]['view']['selection-list']['panels'][0]['fields']
        );

        $this->assertEquals($actualFieldNames, $expectedFieldNames, '', 0, 10, true, true);
    }
}
