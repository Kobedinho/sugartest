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
//require_once 'modules/UpgradeWizard/UpgradeDriver.php';
require_once 'tests/{old}/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/4_AddNewModulesToMegamenu.php';

/**
 * Test for adding new modules to the megamenu on upgrade
 */
class SugarUpgradeAddNewModulesToMegamenuTest extends UpgradeTestCase
{
    /**
     * Mock tabs array to be used in the TabController mock
     * @var array
     */
    public $mockTabs = array(
        '0' => array(
            'Accounts' => 'Accounts',
            'Bugs' => 'Bugs',
            'Contacts' => 'Contacts',
            ),
        '1' => array(
            'Blah' => 'Blah',
        ),
    );

    /**
     * Tests the criteria builder
     *
     * @param array $def new module definition array
     * @dataProvider newModuleDefProvider
     */
    public function testBuildCheckCriteria($def)
    {
        $ug = new SugarUpgradeAddNewModulesToMegamenu($this->upgrader);

        // Setup the upgrader to have the correct versions for this test
        $this->setupUpgraderVersions($ug, $def['setupVersions']);

        // Run the check
        $check = $ug->buildCheckCriteria($def);

        // Check the return
        $this->assertEquals($def['expectCheck'], $check);
    }

    /**
     * Tests the addition routine
     *
     * @param array $def new module definition array
     * @dataProvider moduleDefDataProvider
     */
    public function testGetNewTabsList($def, $expect)
    {
        $ug = new SugarUpgradeAddNewModulesToMegamenu($this->upgrader);

        $actual = $ug->getNewTabsList($this->mockTabs, $def);

        $this->assertEquals($expect, $actual);
    }

    /**
     * Tests the log message getter
     *
     * @param array $def new module definition array
     * @dataProvider newModuleDefProvider
     */
    public function testGetMessageToLog($def)
    {
        // Set the upgrader
        $ug = new SugarUpgradeAddNewModulesToMegamenu($this->upgrader);

        // Get the log message
        $msg = $ug->getMessageToLog($def);

        // Test it
        $this->assertEquals($def['expectMsg'], $msg);
    }

    public function newModuleDefProvider()
    {
        return array(
            array(
                'def' => array(
                    'name' => 'PMSE Modules',
                    'toFlavor' => 'ent',
                    'fromVersion' => array('7.6.0', '<'),
                    'modules' => array(
                        'pmse_Project',
                        'pmse_Inbox',
                        'pmse_Business_Rules',
                        'pmse_Emails_Templates',
                    ),
                    'setupVersions' => array(
                        'from_version' => '6.7.23',
                        'from_flavor' => 'corp',
                        'to_version' => '7.6.1',
                        'to_flavor' => 'ent',
                    ),
                    'expectCheck' => true,
                    'expectMsg' => 'Megamenu module list updated with PMSE Modules',
                ),
            ),
            array(
                'def' => array(
                    'name' => 'Tags Module',
                    'fromVersion' => array('7.7.0', '<'),
                    'modules' => array(
                        'Tags',
                    ),
                    'setupVersions' => array(
                        'from_version' => '7.6.1',
                        'from_flavor' => 'ent',
                        'to_version' => '7.7.0',
                        'to_flavor' => 'ent',
                    ),
                    'expectCheck' => true,
                    'expectMsg' => 'Megamenu module list updated with Tags Module',
                ),
            ),
            array(
                'def' => array(
                    'toFlavor' => 'ult',
                    'modules' => array(
                        'Test1',
                    ),
                    'setupVersions' => array(
                        'from_version' => '6.7.23',
                        'from_flavor' => 'ent',
                        'to_version' => '7.6.1',
                        'to_flavor' => 'ent',
                    ),
                    'expectCheck' => false,
                    'expectMsg' => 'Megamenu module list updated',
                ),
            ),
            array(
                'def' => array(
                    'fromVersion' => array('7.6.1', '<'),
                    'toVersion' => array('7.6.2', '>='),
                    'modules' => array(
                        'Test2',
                    ),
                    'setupVersions' => array(
                        'from_version' => '7.6.1',
                        'from_flavor' => 'ent',
                        'to_version' => '7.6.2',
                        'to_flavor' => 'ent',
                    ),
                    'expectCheck' => false,
                    'expectMsg' => 'Megamenu module list updated',
                ),
            ),
            array(
                'def' => array(
                    'fromVersion' => array('7.6.1', '<='),
                    'toVersion' => array('7.6.2', '>='),
                    'modules' => array(
                        'Test2',
                    ),
                    'setupVersions' => array(
                        'from_version' => '7.6.1',
                        'from_flavor' => 'ent',
                        'to_version' => '7.6.2',
                        'to_flavor' => 'ent',
                    ),
                    'expectCheck' => true,
                    'expectMsg' => 'Megamenu module list updated',
                ),
            ),
            // Tests flavor conversion on 7.7+
            array(
                'def' => array(
                    'name' => 'PMSE Modules Converted',
                    'fromFlavor' => 'pro',
                    'toFlavor' => array('ent', 'ult'),
                    'fromVersion' => array('7.7', '>='),
                    'modules' => array(
                        'pmse_Project',
                        'pmse_Inbox',
                        'pmse_Business_Rules',
                        'pmse_Emails_Templates',
                    ),
                    'setupVersions' => array(
                        'from_version' => '7.7.0.0',
                        'from_flavor' => 'pro',
                        'to_version' => '7.7.0.0',
                        'to_flavor' => 'ult',
                    ),
                    'expectCheck' => true,
                    'expectMsg' => 'Megamenu module list updated with PMSE Modules Converted',
                ),
            ),
        );
    }

    public function moduleDefDataProvider()
    {
        return array(
            array(
                'def' => array(
                    'modules' => array(
                        'Voldemort',
                        'Gandalf',
                        'Palpatine',
                    ),
                ),
                'expect' => array(
                    '0' => array(
                        'Accounts' => 'Accounts',
                        'Bugs' => 'Bugs',
                        'Contacts' => 'Contacts',
                        'Voldemort' => 'Voldemort',
                        'Gandalf' => 'Gandalf',
                        'Palpatine' => 'Palpatine',
                    ),
                    '1' => array(
                        'Blah' => 'Blah',
                    ),
                ),
            ),
            array(
                'def' => array(
                    'modules' => array(
                        'Accounts',
                        'Azog',
                        'Hammerhead',
                    ),
                ),
                'expect' => array(
                    '0' => array(
                        'Accounts' => 'Accounts',
                        'Bugs' => 'Bugs',
                        'Contacts' => 'Contacts',
                        'Azog' => 'Azog',
                        'Hammerhead' => 'Hammerhead',
                    ),
                    '1' => array(
                        'Blah' => 'Blah',
                    ),
                ),
            ),
        );
    }

    public function setupUpgraderVersions($ug, $data)
    {
        foreach ($data as $k => $v) {
            $ug->$k = $v;
            $ug->upgrader->$k = $v;
        }
    }
}
