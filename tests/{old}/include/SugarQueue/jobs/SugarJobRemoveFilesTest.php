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


/**
 * @covers SugarJobRemoveFiles
 */
class SugarJobRemoveFilesTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testRemoveFiles()
    {
        $dir = UploadStream::getDir() . '/tmp/clean-up-test';
        $freshFile = $dir . '/fresh-file.txt';
        $staleFile = $dir . '/stale-file.txt';

        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile(array($freshFile, $staleFile));

        SugarTestHelper::ensureDir($dir);
        touch($freshFile);
        touch($staleFile, time() - 10);

        /** @var SugarJobRemoveFiles|PHPUnit_Framework_MockObject_MockObject $job */
        $job = $this->getMockForAbstractClass('SugarJobRemoveFiles');
        $job->expects($this->once())
            ->method('getDirectory')
            ->willReturn($dir);
        $job->expects($this->once())
            ->method('getMaxLifetime')
            ->willReturn(5);

        /** @var SchedulersJob|PHPUnit_Framework_MockObject_MockObject $schedulerJob */
        $schedulerJob = $this->createMock('SchedulersJob');
        $schedulerJob->expects($this->once())
            ->method('succeedJob');

        $job->setJob($schedulerJob);
        $job->run(null);

        $this->assertFileExists($freshFile, 'Fresh file should not have been removed');
        $this->assertFileNotExists($staleFile, 'Stale file should have been removed');
    }
}
