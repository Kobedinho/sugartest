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
require_once 'upgrade/scripts/post/7_UpdateEmailFieldOnSidecarListViews.php';

/**
 * Test for fixing email1 to email field on listview on upgrade
 */
class SugarUpgradeUpdateEmailFieldOnSidecarListViewsTest extends UpgradeTestCase
{
    /**
     * Tests sanitization of viewdefs
     * @dataProvider getSanitizedDefsProvider
     * @param array $panels
     * @param array $expect
     */
    public function testGetSanitizedDefs($panels, $expect)
    {
        $ug = new SugarUpgradeUpdateEmailFieldOnSidecarListViews($this->upgrader);
        $actual = $ug->getSanitizedDefs($panels);
        $this->assertEquals($expect, $actual);
    }

    /**
     * Tests that a change triggered the save flag
     * @dataProvider hasChangesProvider
     * @param array $panels
     * @param boolean $expect
     */
    public function testHasChanges($panels, $expect)
    {
        $ug = new SugarUpgradeUpdateEmailFieldOnSidecarListViews($this->upgrader);
        $ug->getSanitizedDefs($panels);
        $actual = $ug->hasChanges();
        $this->assertEquals($expect, $actual);
    }

    /**
     * Tests that a list of files produces the correct data
     * @dataProvider getViewFileDataFromFilesProvider
     * @param array $files Array of file paths
     * @param array $defs Mock defs that should be returned when fetched
     * @param array $expect Expectation
     */
    public function testGetViewFileDataFromFiles($files, $expect)
    {
        $ug = new SugarUpgradeUpdateEmailFieldOnSidecarListViewsMock($this->upgrader);
        $actual = $ug->getViewFileDataFromFiles($files);
        $this->assertEquals($expect, $actual);
    }

    public function getSanitizedDefsProvider()
    {
        return array(
            // Test the actual change
            array(
                'panels' => array(
                    array(
                        'fields' => array(
                            array('name' => 'name'),
                            // Tests the update of the label as well
                            array('name' => 'email1', 'label' => 'LBL_EMAIL_ADDRESS'),
                            array('name' => 'id'),
                        ),
                    ),
                ),
                'expect' => array(
                    array(
                        'fields' => array(
                            array('name' => 'name'),
                            array('name' => 'email', 'label' => 'LBL_ANY_EMAIL'),
                            array('name' => 'id'),
                        ),
                    ),
                ),
            ),
            // Test no change of email field
            array(
                'panels' => array(
                    array(
                        'fields' => array(
                            array('name' => 'name'),
                            array('name' => 'email'),
                            array('name' => 'id'),
                        ),
                    ),
                ),
                'expect' => array(
                    array(
                        'fields' => array(
                            array('name' => 'name'),
                            array('name' => 'email'),
                            array('name' => 'id'),
                        ),
                    ),
                ),
            ),
            
            // Test no change of no email1 field
            array(
                'panels' => array(
                    array(
                        'fields' => array(
                            array('name' => 'name'),
                            array('name' => 'id'),
                        ),
                    ),
                ),
                'expect' => array(
                    array(
                        'fields' => array(
                            array('name' => 'name'),
                            array('name' => 'id'),
                        ),
                    ),
                ),
            ),
        );
    }

    public function hasChangesProvider()
    {
        return array(
            // Test the actual change
            array(
                'panels' => array(
                    array(
                        'fields' => array(
                            array('name' => 'name'),
                            array('name' => 'email1'),
                            array('name' => 'id'),
                        ),
                    ),
                ),
                'expect' => true,
            ),
            // Test no change of email field
            array(
                'panels' => array(
                    array(
                        'fields' => array(
                            array('name' => 'name'),
                            array('name' => 'email'),
                            array('name' => 'id'),
                        ),
                    ),
                ),
                'expect' => false,
            ),
            
            // Test no change of no email1 field
            array(
                'panels' => array(
                    array(
                        'fields' => array(
                            array('name' => 'name'),
                            array('name' => 'id'),
                        ),
                    ),
                ),
                'expect' => false,
            ),
        );
    }

    public function getViewFileDataFromFilesProvider()
    {
        return array(
            array(
                'files' => array(
                    'modules/aaa_Test/clients/base/views/list/list.php',
                    'modules/bbb_Test/clients/base/views/list/list.php',
                    'modules/Accounts/clients/base/views/list/list.php',
                    'custom/modules/Accounts/clients/base/views/list/list.php',
                    'custom/modules/Bugs/clients/portal/views/list/list.php',
                    'custom/modules/Contacts/clients/mobile/views/list/list.php',
                    'custom/modules/Leads/clients/base/views/list/list.php',
                    'custom/modules/Notes/clients/base/views/list/list.php',
                ),
                'expect' => array(
                    array(
                        'file' => 'modules/aaa_Test/clients/base/views/list/list.php',
                        'module' => 'aaa_Test',
                        'client' => 'base',
                        'custom' => false,
                        'defs' => array(
                            'panels' => array(
                                'fields' => array(
                                    array('name' => 'name'),
                                    array('name' => 'email1'),
                                    array('name' => 'id'),
                                ),
                            ),
                        ),
                    ),
                    array(
                        'file' => 'modules/Accounts/clients/base/views/list/list.php',
                        'module' => 'Accounts',
                        'client' => 'base',
                        'custom' => false,
                        'defs' => array(
                            'panels' => array(
                                'fields' => array(
                                    array('name' => 'name'),
                                    array('name' => 'id'),
                                ),
                            ),
                        ),
                    ),
                    array(
                        'file' => 'custom/modules/Accounts/clients/base/views/list/list.php',
                        'module' => 'Accounts',
                        'client' => 'base',
                        'custom' => true,
                        'defs' => array(
                            'panels' => array(
                                'fields' => array(
                                    array('name' => 'name'),
                                    array('name' => 'email1'),
                                    array('name' => 'id'),
                                ),
                            ),
                        ),
                    ),
                    array(
                        'file' => 'custom/modules/Bugs/clients/portal/views/list/list.php',
                        'module' => 'Bugs',
                        'client' => 'portal',
                        'custom' => true,
                        'defs' => array(
                            'panels' => array(
                                'fields' => array(
                                    array('name' => 'name'),
                                    array('name' => 'email1'),
                                    array('name' => 'id'),
                                ),
                            ),
                        ),
                    ),
                    array(
                        'file' => 'custom/modules/Contacts/clients/mobile/views/list/list.php',
                        'module' => 'Contacts',
                        'client' => 'mobile',
                        'custom' => true,
                        'defs' => array(
                            'panels' => array(
                                'fields' => array(
                                    array('name' => 'name'),
                                    array('name' => 'id'),
                                ),
                            ),
                        ),
                    ),
                    
                    array(
                        'file' => 'custom/modules/Notes/clients/base/views/list/list.php',
                        'module' => 'Notes',
                        'client' => 'base',
                        'custom' => true,
                        'defs' => array(),
                    ),
                ),
            ),
        );
    }
}

/**
 * Used to mock an iterated return value
 */
class SugarUpgradeUpdateEmailFieldOnSidecarListViewsMock extends SugarUpgradeUpdateEmailFieldOnSidecarListViews
{
    protected $testDefs = array(
        'modules/aaa_Test/clients/base/views/list/list.php' => array(
            'aaa_Test' => array(
                'base' => array(
                    'view' => array(
                        'list' => array(
                            'panels' => array(
                                'fields' => array(
                                    array('name' => 'name'),
                                    array('name' => 'email1'),
                                    array('name' => 'id'),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'modules/Accounts/clients/base/views/list/list.php' => array(
            'Accounts' => array(
                'base' => array(
                    'view' => array(
                        'list' => array(
                            'panels' => array(
                                'fields' => array(
                                    array('name' => 'name'),
                                    array('name' => 'id'),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'custom/modules/Accounts/clients/base/views/list/list.php' => array(
            'Accounts' => array(
                'base' => array(
                    'view' => array(
                        'list' => array(
                            'panels' => array(
                                'fields' => array(
                                    array('name' => 'name'),
                                    array('name' => 'email1'),
                                    array('name' => 'id'),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'custom/modules/Bugs/clients/portal/views/list/list.php' => array(
            'Bugs' => array(
                'portal' => array(
                    'view' => array(
                        'list' => array(
                            'panels' => array(
                                'fields' => array(
                                    array('name' => 'name'),
                                    array('name' => 'email1'),
                                    array('name' => 'id'),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'custom/modules/Contacts/clients/mobile/views/list/list.php' => array(
            'Contacts' => array(
                'mobile' => array(
                    'view' => array(
                        'list' => array(
                            'panels' => array(
                                'fields' => array(
                                    array('name' => 'name'),
                                    array('name' => 'id'),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'custom/modules/Leads/clients/base/views/list/list.php' => array(
            'base' => array(
                'view' => array(
                    'list' => array(
                        'panels' => array(
                            'fields' => array(
                                array('name' => 'name'),
                                array('name' => 'id'),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'custom/modules/Notes/clients/base/views/list/list.php' => array(
            'Notes' => array(
                'base' => array(
                    'view' => array(
                        'list' => array(),
                    ),
                ),
            ),
        ),
    );

    /**
     * Mocks the including of a viewdef files and data return
     * @param string $file The file to include
     * @return array
     */
    protected function getViewDefsFromFile($file)
    {
        return isset($this->testDefs[$file]) ? $this->testDefs[$file] : array();
    }

    /**
     * Mocks checking if a module is a sidecar Company type module
     * @param string $module The module name
     * @return boolean
     */
    protected function isSidecarCompanyModule($module)
    {
        if ($module === 'bbb_Test') {
            return false;
        }

        return true;
    }
}
