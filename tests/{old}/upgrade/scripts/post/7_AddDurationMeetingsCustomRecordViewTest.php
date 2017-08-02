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
require_once 'upgrade/scripts/post/7_AddDurationMeetingsCustomRecordView.php';

/**
 * Class SugarUpgradeAddDurationMeetingsCustomRecordViewTest test
 * for SugarUpgradeAddDurationMeetingsCustomRecordView script.
 */
class SugarUpgradeAddDurationMeetingsCustomRecordViewTest extends UpgradeTestCase
{
    protected $testClassName = 'SugarUpgradeAddDurationMeetingsCustomRecordView';

    public function testRunNotVersion()
    {
        $mock = $this->getMockBuilder($this->testClassName)
            ->setConstructorArgs(array($this->upgrader))
            ->setMethods(array('updateCustomRecordViews', 'saveFile'))
            ->getMock();

        $mock->from_version = '7.6';
        $mock->to_version = '7.7';

        $mock->expects($this->never())
            ->method('updateCustomRecordViews');

        $mock->expects($this->never())
            ->method('saveFile');

        $mock->run();
    }

    /**
     * Test use cases where the duration field is not added to Record layout
     *
     * @dataProvider providerDurationNotAddedCustomRecordTabLayout
     * @paramarray $viewdefs
     */
    public function testDurationNotAddedCustomRecordTabLayout($viewdefs)
    {
        $defs['Meetings']['base']['view']['record'] = $viewdefs;

        $mock = $this->getMockBuilder($this->testClassName)
            ->setConstructorArgs(array($this->upgrader))
            ->setMethods(array('getCustomRecordViewDef', 'saveFile', 'getNewDurationFieldDef', 'hasCustomRecordView'))
            ->getMock();
        $mock->from_version = '7.5';
        $mock->to_version = '7.8';


        $mock->expects($this->once())
            ->method('getCustomRecordViewDef')
            ->will($this->returnValue($defs));

        $mock->expects($this->once())
            ->method('hasCustomRecordView')
            ->will($this->returnValue(true));

        $mock->expects($this->never())
            ->method('saveFile');

        $mock->expects($this->never())
            ->method('getNewDurationFieldDef');

        $mock->run();
    }

    /**
     * Tests use case where duration gets added to the record layout
     *
     * @dataProvider providerDurationAddedCustomRecordTabLayout
     * @param array $viewdefs
     */
    public function testDurationAddedCustomRecordTabLayout($viewdefs)
    {
        $defs['Meetings']['base']['view']['record'] = $viewdefs;

        $expDurationFieldDef = array(
            'name' => 'duration'
        );

        $mock = $this->getMockBuilder($this->testClassName)
            ->setConstructorArgs(array($this->upgrader))
            ->setMethods(array('getCustomRecordViewDef', 'saveFile', 'getNewDurationFieldDef', 'hasCustomRecordView'))
            ->getMock();
        $mock->from_version = '7.5';
        $mock->to_version = '7.8';


        $mock->expects($this->once())
            ->method('getCustomRecordViewDef')
            ->will($this->returnValue($defs));

        $mock->expects($this->once())
            ->method('hasCustomRecordView')
            ->will($this->returnValue(true));

        $mock->expects($this->once())
            ->method('saveFile');

        $mock->expects($this->once())
            ->method('getNewDurationFieldDef')
            ->will($this->returnValue($expDurationFieldDef));

        $mock->run();

        $viewDef = $mock->viewdefs;
        $fields = $viewDef['Meetings']['base']['view']['record']['panels'][1]['fields'];

        $actDurationFieldDef = array_pop($fields);

        $this->assertEquals(
            $expDurationFieldDef,
            $actDurationFieldDef,
            'Duration field was not added as last item in business card'
        );

    }

    public function providerDurationNotAddedCustomRecordTabLayout()
    {
        return array(
            //custom tab layout duration field exist
            array(
                array(
                    'panels' => array(
                        0 => array(
                            'name' => 'panel_header',
                            'header' => true,
                            'fields' =>
                                array(
                                    0 =>
                                        array(
                                            'name' => 'picture',
                                            'type' => 'avatar',
                                            'size' => 'large',
                                            'dismiss_label' => true,
                                            'readonly' => true,
                                        ),
                                ),
                        ),
                        1 => array(
                            'name' => 'panel_body',
                            'label' => 'LBL_RECORD_BODY',
                            'columns' => 2,
                            'labels' => true,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => array(
                                0 => array(
                                    'name' => 'duration',
                                    'type' => 'duration',
                                ),
                            ),
                            'newTab' => true,
                            'panelDefault' => 'expanded',
                        ),
                        2 => array(
                            'name' => 'panel_hidden',
                            'label' => 'LBL_RECORD_SHOWMORE',
                            'columns' => 2,
                            'labels' => true,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'hide' => true,
                            'fields' => array(
                                0 => 'assigned_user_name',
                            ),
                            'newTab' => false,
                            'panelDefault' => 'expanded',
                        ),
                    ),
                    'templateMeta' =>
                        array(
                            'maxColumns' => '2',
                            'useTabs' => true,
                        ),
                ),
            ),
            //custom panel layout duration field exists
            array(
                array(
                    'panels' => array(
                        0 => array(
                            'name' => 'panel_header',
                            'header' => true,
                            'fields' =>
                                array(
                                    0 => array(
                                        'name' => 'picture',
                                        'type' => 'avatar',
                                        'size' => 'large',
                                        'dismiss_label' => true,
                                        'readonly' => true,
                                    ),
                                ),
                        ),
                        1 => array(
                            'name' => 'panel_body',
                            'label' => 'LBL_RECORD_BODY',
                            'columns' => 2,
                            'labels' => true,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => array(
                                0 => array(
                                    'name' => 'type',
                                ),
                                1 => array(
                                    'name' => 'duration',
                                    'type' => 'duration',
                                ),
                            ),
                            'newTab' => true,
                            'panelDefault' => 'expanded',
                        ),
                        2 => array(
                            'name' => 'panel_hidden',
                            'label' => 'LBL_RECORD_SHOWMORE',
                            'columns' => 2,
                            'labels' => true,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'hide' => true,
                            'fields' => array(
                                0 => 'assigned_user_name',
                            ),
                            'newTab' => false,
                            'panelDefault' => 'expanded',
                        ),
                    ),
                ),
            ),
            //custom panel layout duration field is missing
            array(
                array(
                    'panels' => array(
                        0 => array(
                            'name' => 'panel_header',
                            'header' => true,
                            'fields' => array(
                                0 => array(
                                    'name' => 'picture',
                                    'type' => 'avatar',
                                    'size' => 'large',
                                    'dismiss_label' => true,
                                    'readonly' => true,
                                ),
                            ),
                        ),
                        1 => array(
                            'name' => 'panel_body',
                            'label' => 'LBL_RECORD_BODY',
                            'columns' => 2,
                            'labels' => true,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => array(
                                0 => array(
                                    'name' => 'type',
                                ),
                            ),
                            'newTab' => true,
                            'panelDefault' => 'expanded',
                        ),
                        2 => array(
                            'name' => 'panel_hidden',
                            'label' => 'LBL_RECORD_SHOWMORE',
                            'columns' => 2,
                            'labels' => true,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'hide' => true,
                            'fields' => array(
                                0 => 'assigned_user_name',
                            ),
                            'newTab' => false,
                            'panelDefault' => 'expanded',
                        ),
                    ),
                    'templateMeta' => array(
                        'maxColumns' => '2',
                        'useTabs' => false,
                    ),
                ),
            ),
        );
    }


    public function providerDurationAddedCustomRecordTabLayout()
    {
        return array(
            //custom tab layout duration field is missing
            array(
                array(
                    'panels' => array(
                        0 => array(
                            'name' => 'panel_header',
                            'header' => true,
                            'fields' => array(
                                0 => array(
                                    'name' => 'picture',
                                    'type' => 'avatar',
                                    'size' => 'large',
                                    'dismiss_label' => true,
                                    'readonly' => true,
                                ),
                            ),
                        ),
                        1 => array(
                            'name' => 'panel_body',
                            'label' => 'LBL_RECORD_BODY',
                            'columns' => 2,
                            'labels' => true,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => array(
                                0 => array(
                                    'name' => 'type',
                                ),
                            ),
                            'newTab' => true,
                            'panelDefault' => 'expanded',
                        ),
                        2 => array(
                            'name' => 'panel_hidden',
                            'label' => 'LBL_RECORD_SHOWMORE',
                            'columns' => 2,
                            'labels' => true,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'hide' => true,
                            'fields' => array(
                                0 => 'assigned_user_name',
                            ),
                            'newTab' => false,
                            'panelDefault' => 'expanded',
                        ),
                    ),
                    'templateMeta' => array(
                        'maxColumns' => '2',
                        'useTabs' => true,
                    ),
                ),
            ),
            //custom tab layout duration field not the correct one
            array(
                array(
                    'panels' => array(
                        0 => array(
                            'name' => 'panel_header',
                            'header' => true,
                            'fields' => array(
                                0 => array(
                                    'name' => 'picture',
                                    'type' => 'avatar',
                                    'size' => 'large',
                                    'dismiss_label' => true,
                                    'readonly' => true,
                                ),
                            ),
                        ),
                        1 => array(
                            'name' => 'panel_body',
                            'label' => 'LBL_RECORD_BODY',
                            'columns' => 2,
                            'labels' => true,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'fields' => array(
                                0 => array(
                                    'name' => 'type',
                                ),
                                1 => array(
                                    'name' => 'duration',
                                ),
                            ),
                            'newTab' => true,
                            'panelDefault' => 'expanded',
                        ),
                        2 => array(
                            'name' => 'panel_hidden',
                            'label' => 'LBL_RECORD_SHOWMORE',
                            'columns' => 2,
                            'labels' => true,
                            'labelsOnTop' => true,
                            'placeholders' => true,
                            'hide' => true,
                            'fields' => array(
                                0 => 'assigned_user_name',
                            ),
                            'newTab' => false,
                            'panelDefault' => 'expanded',
                        ),
                    ),
                    'templateMeta' => array(
                        'maxColumns' => '2',
                        'useTabs' => true,
                    ),
                ),
            ),
        );
    }
}
