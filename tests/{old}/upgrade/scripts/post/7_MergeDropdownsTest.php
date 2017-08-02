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
require_once 'upgrade/scripts/post/7_MergeDropdowns.php';

/**
 * @covers SugarUpgradeMergeDropdowns
 */
class SugarUpgradeMergeDropdownsTest extends UpgradeTestCase
{
    /**
     * @covers SugarUpgradeMergeDropdowns::run
     */
    public function testRun_NothingToMerge_ReturnsBeforeDoingAnything()
    {
        $mockMerger = $this->getMockBuilder('SugarUpgradeMergeDropdowns')
            ->setMethods(['getDropdownHelper', 'getDropdownParser'])
            ->setConstructorArgs([$this->upgrader])
            ->getMock();
        $mockMerger->expects($this->never())->method('getDropdownParser');

        $this->upgrader->state['dropdowns_to_merge'] = array();

        $mockMerger->run();
    }

    /**
     * @param array $old Dropdown options in previous version
     * @param array $new Dropdown options in new version
     * @param array $custom Customized dropdown options in previous version
     * @param array $expected Customized dropdown options after merging
     *
     * @covers SugarUpgradeMergeDropdowns::run
     * @dataProvider dataProviderSavesAMergedDropdown
     */
    public function testRun_SavesAMergedDropdown($old, $new, $custom, $expected)
    {
        $mockParser = $this->createPartialMock('ParserDropDown', array('saveDropDown'));
        $mockParser->expects($this->once())->method('saveDropDown')->with($this->equalTo($expected));

        $mockHelper = $this->createPartialMock('UpgradeDropdownsHelper', array('getDropdowns', 'getDropdownsToPush'));
        $mockHelper->expects($this->once())->method('getDropdowns')->willReturn($new);
        $mockHelper->expects($this->once())->method('getDropdownsToPush')->willReturn(array());

        $mockMerger = $this->getMockBuilder('SugarUpgradeMergeDropdowns')
            ->setMethods(['getDropdownHelper', 'getDropdownParser'])
            ->setConstructorArgs([$this->upgrader])
            ->getMock();
        $mockMerger->expects($this->once())->method('getDropdownParser')->willReturn($mockParser);
        $mockMerger->expects($this->once())->method('getDropdownHelper')->willReturn($mockHelper);

        $this->upgrader->state['dropdowns_to_merge'] = array(
            'en_us' => array(
                'old' => $old,
                'custom' => $custom,
            ),
        );

        $mockMerger->run();
    }

    public static function dataProviderSavesAMergedDropdown()
    {
        return array(
            // Add the option from $new
            array(
                'old' => array(
                    'activity_dom' => array(
                        'Call' => 'Call',
                        'Meeting' => 'Meeting',
                        'Task' => 'Task',
                        'Email' => 'Email',
                        'Note' => 'Note',
                    ),
                ),
                'new' => array(
                    'activity_dom' => array(
                        'New' => 'New Value',
                        'Call' => 'Call',
                        'Meeting' => 'Meeting',
                        'Task' => 'Task',
                        'Email' => 'Email',
                        'Note' => 'Note',
                    ),
                ),
                'custom' => array(
                    'activity_dom' => array(
                        'Call' => 'Call',
                        'Meeting' => 'Meeting',
                        'Email' => 'Email',
                        'Note' => 'Note',
                    ),
                ),
                'expected' => array(
                    'dropdown_lang' => 'en_us',
                    'dropdown_name' => 'activity_dom',
                    'list_value' => '[["New","New Value"],["Call","Call"],["Meeting","Meeting"],["Email","Email"],["Note","Note"]]',
                    'skip_sync' => true,
                    'view_package' => 'studio',
                    'use_push' => false,
                    'skipSaveExemptDropdowns' => true,
                ),
            ),
            // Change value to the one from $new
            array(
                'old' => array(
                    'activity_dom' => array(
                        'Email' => 'Email',
                        'Task' => 'Task',
                    ),
                ),
                'new' => array(
                    'activity_dom' => array(
                        'Email' => 'Email',
                        'Task' => 'To Do',
                    ),
                ),
                'custom' => array(
                    'activity_dom' => array(),
                ),
                'expected' => array(
                    'dropdown_lang' => 'en_us',
                    'dropdown_name' => 'activity_dom',
                    'list_value' => '[["Email","Email"],["Task","To Do"]]',
                    'skip_sync' => true,
                    'view_package' => 'studio',
                    'use_push' => false,
                    'skipSaveExemptDropdowns' => true,
                ),
            ),
            // Change value to the one from $custom
            array(
                'old' => array(
                    'activity_dom' => array(
                        'Email' => 'Email',
                        'Task' => 'Task',
                    ),
                ),
                'new' => array(
                    'activity_dom' => array(
                        'Email' => 'Email',
                        'Task' => 'To Do',
                    ),
                ),
                'custom' => array(
                    'activity_dom' => array(
                        'Task' => 'To Do 2',
                    ),
                ),
                'expected' => array(
                    'dropdown_lang' => 'en_us',
                    'dropdown_name' => 'activity_dom',
                    'list_value' => '[["Task","To Do 2"]]',
                    'skip_sync' => true,
                    'view_package' => 'studio',
                    'use_push' => false,
                    'skipSaveExemptDropdowns' => true,
                ),
            ),
        );
    }

    /**
     * @covers SugarUpgradeMergeDropdowns::run
     */
    public function testRun_SavesDropdownsInMultipleLanguages()
    {
        $firstLanguage = 'en_us';
        $secondLanguage = 'es_ES';

        $old = array(
            'activity_dom' => array(
                'Call' => 'Call',
                'Meeting' => 'Meeting',
                'Task' => 'Task',
                'Email' => 'Email',
                'Note' => 'Note',
            ),
        );

        $custom = array(
            'activity_dom' => array(
                'Call' => 'Call',
                'Meeting' => 'Meeting',
                'Task' => 'To Do',
                'Email' => 'Email',
                'Note' => 'Note',
            ),
        );

        $new = array(
            'activity_dom' => array(
                'Call' => 'Call',
                'Meeting' => 'Meeting',
                'Task' => 'Task',
                'Email' => 'Email',
                'Note' => 'Note',
            ),
        );

        $mockParser = $this->createPartialMock('ParserDropDown', array('saveDropDown'));
        $mockParser->expects($this->exactly(2))
            ->method('saveDropDown')
            ->withConsecutive(
                array(
                    array(
                        'dropdown_lang' => $firstLanguage,
                        'dropdown_name' => 'activity_dom',
                        'list_value' => '[["Call","Call"],["Meeting","Meeting"],["Task","To Do"],["Email","Email"],["Note","Note"]]',
                        'skip_sync' => true,
                        'view_package' => 'studio',
                        'use_push' => false,
                        'skipSaveExemptDropdowns' => true,
                    ),
                ),
                array(
                    array(
                        'dropdown_lang' => $secondLanguage,
                        'dropdown_name' => 'activity_dom',
                        'list_value' => '[["Call","Call"],["Meeting","Meeting"],["Task","To Do"],["Email","Email"],["Note","Note"]]',
                        'skip_sync' => true,
                        'view_package' => 'studio',
                        'use_push' => false,
                        'skipSaveExemptDropdowns' => true,
                    ),
                )
            );

        $mockHelper = $this->createPartialMock('UpgradeDropdownsHelper', array('getDropdowns', 'getDropdownsToPush'));
        $mockHelper->expects($this->exactly(2))->method('getDropdowns')->willReturn($new);
        $mockHelper->expects($this->exactly(2))->method('getDropdownsToPush')->willReturn(array());

        $mockMerger = $this->getMockBuilder('SugarUpgradeMergeDropdowns')
            ->setMethods(['getDropdownHelper', 'getDropdownParser'])
            ->setConstructorArgs([$this->upgrader])
            ->getMock();
        $mockMerger->expects($this->once())->method('getDropdownParser')->willReturn($mockParser);
        $mockMerger->expects($this->once())->method('getDropdownHelper')->willReturn($mockHelper);

        $this->upgrader->state['dropdowns_to_merge'] = array();
        $this->upgrader->state['dropdowns_to_merge'][$firstLanguage] = array(
            'old' => $old,
            'custom' => $custom,
        );
        $this->upgrader->state['dropdowns_to_merge'][$secondLanguage] = array(
            'old' => $old,
            'custom' => $custom,
        );

        $mockMerger->run();
    }

    /**
     * Test for check settings of use_push parameter
     *
     * @param array $old Old $app_list_strings values
     * @param array $new New $app_list_strings values
     * @param array $custom Custom $app_list_strings values
     * @param array $dropdownsToPush Array of dropdonws that need to be used with use_push parameter
     * @param array $use_push Expected result of use_push parameter
     *
     * @dataProvider usePushSettingsProvider
     */
    public function testRun_UsePushSettings($old, $new, $custom, $dropdownsToPush, $use_push)
    {
        $params = array();

        $parserMock = $this->createPartialMock('ParserDropDown', array('saveDropDown'));
        $parserMock->expects($this->once())
                   ->method('saveDropDown')
                   ->will(
                       $this->returnCallback(
                           function ($data) use (&$params) {
                               $params = $data;
                               return $data;
                           }
                       )
                   );

        $helperMock = $this->createPartialMock('UpgradeDropdownsHelper', array('getDropdowns', 'getDropdownsToPush'));
        $helperMock->expects($this->once())->method('getDropdowns')->willReturn($new);
        $helperMock->expects($this->once())->method('getDropdownsToPush')->willReturn($dropdownsToPush);

        $this->upgrader->state['dropdowns_to_merge'] = array(
            'en_us' => array(
                'old' => $old,
                'custom' => $custom,
            ),
        );

        $mockObject = $this->getMockBuilder('SugarUpgradeMergeDropdowns')
            ->setMethods(['getDropdownHelper', 'getDropdownParser'])
            ->setConstructorArgs([$this->upgrader])
            ->getMock();

        $mockObject->expects($this->once())->method('getDropdownParser')->willReturn($parserMock);
        $mockObject->expects($this->once())->method('getDropdownHelper')->willReturn($helperMock);

        $mockObject->run();

        $this->assertEquals($params['use_push'], $use_push);

    }

    public function usePushSettingsProvider()
    {
        return array(
            array(
                'old' => array(
                    'moduleList' => array(
                        'ACLRoles' => 'Roles',
                        'Bugs' => 'Bugs',
                        'iFrames' => 'My Sites',
                        'test' => 'test',
                    ),
                ),
                'new' => array(
                    'moduleList' => array(
                        'ACLRoles' => 'Roles',
                        'Bugs' => 'Bug Tracker',
                        'WebLogicHooks' => 'Web Logic Hooks',
                        'iFrames' => 'My Sites',
                    ),
                ),
                'custom' => array(
                    'moduleList' => array('Bugs' => 'Help Desks'),
                ),
                'dropdowns' => array('moduleList'),
                true,
            ),
            array(
                'old' => array(
                    'moduleList' => array(
                        'ACLRoles' => 'Roles',
                        'Bugs' => 'Bugs',
                        'iFrames' => 'My Sites',
                        'test' => 'test',
                    ),
                ),
                'new' => array(
                    'moduleList' => array(
                        'ACLRoles' => 'Roles',
                        'Bugs' => 'Bug Tracker',
                        'WebLogicHooks' => 'Web Logic Hooks',
                        'iFrames' => 'My Sites',
                    ),
                ),
                'custom' => array(
                    'moduleList' => array('Bugs' => 'Help Desks'),
                ),
                'dropdowns' => array(),
                false,
            ),
        );
    }
}
