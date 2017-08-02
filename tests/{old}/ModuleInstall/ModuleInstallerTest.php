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

class ModuleInstallerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @covers ModuleInstaller::merge_files
     */
    public function testMergeFiles()
    {
        $minst = $this->createPartialMock('ModuleInstaller', array('mergeModuleFiles'));
        $minst->expects($this->once())->method('mergeModuleFiles')
            ->with('application', 'foo', 'bar', 'baz');
        $minst->merge_files('foo', 'bar', 'baz', true);
    }

    /**
     * @covers ModuleInstaller::merge_files
     */
    public function testMergeFiles2()
    {
        $minst = $this->createPartialMock('ModuleInstaller', array('mergeModuleFiles'));
        // We add one to the count for the application extension invocation.
        $count = count($minst->modules) + 1;
        $minst->expects($this->exactly($count))->method('mergeModuleFiles')
            ->with($this->anything(), 'foo', 'bar', 'baz');
        $minst->merge_files('foo', 'bar', 'baz', false);
    }

    public function testModuleDirs()
    {
        $modules = ModuleInstaller::getModuleDirs();
        $this->assertContains("ActivityStream/Activities", $modules, "ActivityStream/Activities not found!");
    }
}
