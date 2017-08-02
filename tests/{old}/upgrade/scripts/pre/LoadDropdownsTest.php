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
require_once 'upgrade/scripts/pre/LoadDropdowns.php';

/**
 * @covers SugarUpgradeLoadDropdowns
 */
class SugarUpgradeLoadDropdownsTest extends UpgradeTestCase
{
    /**
     * @covers SugarUpgradeLoadDropdowns::run
     */
    public function testRun_IdentifiesCustomizedDropdowns()
    {
        $mockHelper = $this->createPartialMock('UpgradeDropdownsHelper', array('getDropdowns'));
        $mockHelper->expects($this->exactly(2))
            ->method('getDropdowns')
            ->willReturnOnConsecutiveCalls(
                // include/language/en_us.lang.php
                array(
                    'activity_dom' => array(
                        'Call' => 'Call',
                        'Meeting' => 'Meeting',
                        'Task' => 'Task',
                        'Email' => 'Email',
                        'Note' => 'Note',
                    ),
                    'meeting_status_dom' => array(
                        'Planned' => 'Planned',
                        'Held' => 'Held',
                        'Not Held' => 'Not Held',
                    ),
                ),
                // custom/include/language/en_us.lang.php
                array(
                    'activity_dom' => array(
                        'Call' => 'Call',
                        'Meeting' => 'Meeting',
                        'Task' => 'To Do',
                        'Email' => 'Email',
                        'Note' => 'Note',
                        'SMS' => 'Text Message',
                    ),
                )
            );

        $mockLoader = $this->getMockBuilder('SugarUpgradeLoadDropdowns')
            ->setMethods(array('getCustomFile', 'getDropdownHelper', 'geti18nFiles', 'getLanguage'))
            ->setConstructorArgs(array($this->upgrader))
            ->getMock();
        $mockLoader->expects($this->once())->method('getDropdownHelper')->willReturn($mockHelper);
        $mockLoader->expects($this->once())
            ->method('geti18nFiles')
            ->willReturn(array('include/language/en_us.lang.php'));
        $mockLoader->expects($this->exactly(2))
            ->method('getCustomFile')
            ->willReturnOnConsecutiveCalls(
                'custom/include/language/en_us.lang.php',
                null

            );
        $mockLoader->expects($this->once())->method('getLanguage')->willReturn('en_us');

        $mockLoader->run();

        $dropdownsToMerge = $this->upgrader->state['dropdowns_to_merge']['en_us'];
        $this->assertArrayHasKey('old', $dropdownsToMerge);
        $this->assertArrayHasKey('custom', $dropdownsToMerge);
        $this->assertArrayHasKey('activity_dom', $dropdownsToMerge['old']);
        $this->assertArrayHasKey('activity_dom', $dropdownsToMerge['custom']);
        $this->assertArrayNotHasKey('meeting_status_dom', $dropdownsToMerge['old']);
        $this->assertArrayNotHasKey('meeting_status_dom', $dropdownsToMerge['custom']);
    }

    /**
     * @covers SugarUpgradeLoadDropdowns::run
     */
    public function testRun_CustomFileNotFound_NothingToMerge()
    {
        $mockLoader = $this->getMockBuilder('SugarUpgradeLoadDropdowns')
            ->setMethods(array('getCustomFile', 'geti18nFiles'))
            ->setConstructorArgs(array($this->upgrader))
            ->getMock();
        $mockLoader->expects($this->once())
            ->method('geti18nFiles')
            ->willReturn(array('include/language/en_us.lang.php'));
        $mockLoader->expects($this->once())
            ->method('getCustomFile')
            ->willReturn(null);

        $mockLoader->run();

        $this->assertEmpty($this->upgrader->state['dropdowns_to_merge']);
    }

    /**
     * @covers SugarUpgradeLoadDropdowns::run
     */
    public function testRun_NoCustomizationsFoundInCustomFile_NothingToMerge()
    {
        $mockHelper = $this->createPartialMock('UpgradeDropdownsHelper', array('getDropdowns'));
        $mockHelper->expects($this->exactly(2))
            ->method('getDropdowns')
            ->willReturnOnConsecutiveCalls(
                // include/language/en_us.lang.php
                array(
                    'activity_dom' => array(
                        'Call' => 'Call',
                        'Meeting' => 'Meeting',
                        'Task' => 'Task',
                        'Email' => 'Email',
                        'Note' => 'Note',
                    ),
                ),
                // custom/include/language/en_us.lang.php
                array()
            );

        $mockLoader = $this->getMockBuilder('SugarUpgradeLoadDropdowns')
            ->setMethods(array('getCustomFile', 'getDropdownHelper', 'geti18nFiles', 'getLanguage'))
            ->setConstructorArgs(array($this->upgrader))
            ->getMock();
        $mockLoader->expects($this->once())->method('getDropdownHelper')->willReturn($mockHelper);
        $mockLoader->expects($this->once())
            ->method('geti18nFiles')
            ->willReturn(array('include/language/en_us.lang.php'));
        $mockLoader->expects($this->exactly(2))
            ->method('getCustomFile')
            ->willReturnOnConsecutiveCalls(
                'custom/include/language/en_us.lang.php',
                null
            );
        $mockLoader->expects($this->once())->method('getLanguage')->willReturn('en_us');

        $mockLoader->run();

        $dropdownsToMerge = $this->upgrader->state['dropdowns_to_merge']['en_us'];
        $this->assertEmpty($dropdownsToMerge['old']);
        $this->assertEmpty($dropdownsToMerge['custom']);
    }

    /**
     * @covers SugarUpgradeLoadDropdowns::run
     */
    public function testRun_MultipleLanguagesAreCustomized()
    {
        $firstLanguage = 'en_us';
        $secondLanguage = 'es_ES';

        $mockHelper = $this->createPartialMock('UpgradeDropdownsHelper', array('getDropdowns'));
        $mockHelper->expects($this->exactly(4))
            ->method('getDropdowns')
            ->willReturnOnConsecutiveCalls(
                // include/language/en_us.lang.php
                array(
                    'activity_dom' => array(
                        'Call' => 'Call',
                        'Meeting' => 'Meeting',
                        'Task' => 'Task',
                        'Email' => 'Email',
                        'Note' => 'Note',
                    ),
                ),
                // custom/include/language/en_us.lang.php
                array(
                    'activity_dom' => array(
                        'Call' => 'Call',
                        'Meeting' => 'Meeting',
                        'Task' => 'To Do',
                        'Email' => 'Email',
                        'Note' => 'Note',
                        'SMS' => 'Text Message',
                    ),
                ),
                // include/language/en_ES.lang.php
                array(
                    'activity_dom' => array(
                        'Call' => 'Call',
                        'Meeting' => 'Meeting',
                        'Task' => 'Task',
                        'Email' => 'Email',
                        'Note' => 'Note',
                    ),
                ),
                // custom/include/language/en_ES.lang.php
                array(
                    'activity_dom' => array(
                        'Call' => 'Call',
                        'Meeting' => 'Meeting',
                        'Task' => 'To Do',
                        'Email' => 'Email',
                        'Note' => 'Note',
                        'SMS' => 'Text Message',
                    ),
                )
            );

        $mockLoader = $this->getMockBuilder('SugarUpgradeLoadDropdowns')
            ->setMethods(array('getCustomFile', 'getDropdownHelper', 'geti18nFiles', 'getLanguage', 'getFileMTime'))
            ->setConstructorArgs(array($this->upgrader))
            ->getMock();
        $mockLoader->expects($this->once())->method('getDropdownHelper')->willReturn($mockHelper);
        $mockLoader->expects($this->once())
            ->method('geti18nFiles')
            ->willReturn(array(
                "include/lanaguage/{$firstLanguage}.lang.php",
                "include/language/{$secondLanguage}.lang.php",
            ));
        $mockLoader->expects($this->exactly(4))
            ->method('getCustomFile')
            ->willReturnOnConsecutiveCalls(
                "custom/include/lanaguage/{$firstLanguage}.lang.php",
                "custom/application/Ext/Language/{$firstLanguage}.lang.ext.php",
                "custom/include/lanaguage/{$secondLanguage}.lang.php",
                "custom/application/Ext/Language/{$secondLanguage}.lang.ext.php"
            );
        $mockLoader->expects($this->exactly(4))
            ->method('getFileMTime')
            ->willReturnOnConsecutiveCalls(
                1, 0, 1, 0
            );
        $mockLoader->expects($this->exactly(2))
            ->method('getLanguage')
            ->willReturnOnConsecutiveCalls($firstLanguage, $secondLanguage);


        $mockLoader->run();

        $this->assertArrayHasKey($firstLanguage, $this->upgrader->state['dropdowns_to_merge']);
        $this->assertArrayHasKey($secondLanguage, $this->upgrader->state['dropdowns_to_merge']);
    }

    /**
     * @covers SugarUpgradeLoadDropdowns::run
     */
    public function testRun_CustomHasDropdownsNotFoundInOld()
    {
        $mockHelper = $this->createPartialMock('UpgradeDropdownsHelper', array('getDropdowns'));
        $mockHelper->expects($this->exactly(2))
            ->method('getDropdowns')
            ->willReturnOnConsecutiveCalls(
                // include/language/en_us.lang.php
                array(
                    'activity_dom' => array(
                        'Call' => 'Call',
                        'Meeting' => 'Meeting',
                        'Task' => 'Task',
                        'Email' => 'Email',
                        'Note' => 'Note',
                    ),
                    'meeting_status_dom' => array(
                        'Planned' => 'Planned',
                        'Held' => 'Held',
                        'Not Held' => 'Not Held',
                    ),
                ),
                // custom/include/language/en_us.lang.php
                array(
                    'foo_dom' => array(
                        'Foo' => 'Foo',
                        'Biz' => 'Biz',
                        'Baz' => 'Baz',
                    ),
                )
            );

        $mockLoader = $this->getMockBuilder('SugarUpgradeLoadDropdowns')
            ->setMethods(array('getCustomFile', 'getDropdownHelper', 'geti18nFiles', 'getLanguage'))
            ->setConstructorArgs(array($this->upgrader))
            ->getMock();
        $mockLoader->expects($this->once())->method('getDropdownHelper')->willReturn($mockHelper);
        $mockLoader->expects($this->once())
            ->method('geti18nFiles')
            ->willReturn(array('include/language/en_us.lang.php'));
        $mockLoader->expects($this->exactly(2))
            ->method('getCustomFile')
            ->willReturnOnConsecutiveCalls(
                'custom/include/language/en_us.lang.php',
                null
            );
        $mockLoader->expects($this->once())->method('getLanguage')->willReturn('en_us');

        $mockLoader->run();

        $dropdownsToMerge = $this->upgrader->state['dropdowns_to_merge']['en_us'];
        $this->assertEmpty($dropdownsToMerge['old']);
        $this->assertArrayHasKey('foo_dom', $dropdownsToMerge['custom']);
    }

    /**
     * @covers SugarUpgradeLoadDropdowns::run
     */
    public function testRun_NoDropdownsAreFound()
    {
        $mockHelper = $this->createPartialMock('UpgradeDropdownsHelper', array('getDropdowns'));
        $mockHelper->expects($this->exactly(2))
            ->method('getDropdowns')
            ->willReturn(array());

        $mockLoader = $this->getMockBuilder('SugarUpgradeLoadDropdowns')
            ->setMethods(array('getCustomFile', 'getDropdownHelper', 'geti18nFiles', 'getLanguage'))
            ->setConstructorArgs(array($this->upgrader))
            ->getMock();
        $mockLoader->expects($this->once())->method('getDropdownHelper')->willReturn($mockHelper);
        $mockLoader->expects($this->once())
            ->method('geti18nFiles')
            ->willReturn(array('include/language/en_us.lang.php'));
        $mockLoader->expects($this->exactly(2))
            ->method('getCustomFile')
            ->willReturnOnConsecutiveCalls(
                'custom/include/language/en_us.lang.php',
                null
            );
        $mockLoader->expects($this->once())->method('getLanguage')->willReturn('en_us');

        $mockLoader->run();

        $this->assertEmpty($this->upgrader->state['dropdowns_to_merge']);
    }
}
