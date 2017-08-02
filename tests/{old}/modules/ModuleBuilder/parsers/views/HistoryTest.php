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


class HistoryTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var string
     */
    private $_path;

    /**
     * @var History
     */
    private $_history;

    public function setUp()
    {
        $this->_path = sugar_cached('/history/' . time());
        sugar_mkdir($this->getHistoryDir());
        $this->_history = new History($this->_path);
    }

    public function tearDown()
    {
        // Clean all temporary files created
        $files = glob($this->getHistoryDir() . '/*');
        foreach ($files as $file) {
            @unlink($file);
        }
        @rmdir($this->getHistoryDir());
    }

    public function testConstructor()
    {
        $this->assertTrue(is_dir($this->getHistoryDir()), "History dir not created");
    }

    /**
     * Append a file to the history, check if it's properly added, restore it, and check if it's there
     */
    public function testAppendRestoreUndo()
    {
        $tempFile = tempnam($this->getHistoryDir(), 'history');

        $time = $this->_history->append($tempFile);
        $this->assertTrue(file_exists($this->_history->getFileByTimestamp($time)), "Didn't create history file");
        $this->assertEquals($this->_history->restoreByTimestamp($time), $time, 'Restore returns incorrect timestamp');

        $this->assertTrue(file_exists($this->_path), 'Preview file not created');
        $this->assertFileEquals($tempFile, $this->_path, 'Restored file incorrect');

        $this->_history->undoRestore();
        $this->assertFalse(file_exists($this->_path), 'Preview file not removed');
    }

    /**
     * Add several files to history, test getter functions for the history list
     */
    public function testPositioning()
    {

        // Pause for a second in between each append for different timestamps
        $el1 = $this->_history->append(tempnam($this->getHistoryDir(), 'history'));
        $el2 = $this->_history->append(tempnam($this->getHistoryDir(), 'history'));
        $el3 = $this->_history->append(tempnam($this->getHistoryDir(), 'history'));

        // Grab our values for testing
        $getFirst = $this->_history->getFirst();
        $getLast  = $this->_history->getLast();
        $getNth1  = $this->_history->getNth(1);
        $getNext  = $this->_history->getNext();

        // Assertions
        $this->assertEquals($el3, $getFirst, "$el3 was not the timestamp returned by getFirst() [$getFirst]");
        $this->assertEquals($el1, $getLast, "$el1 was not the timestamp returned by getLast() [$getLast]");
        $this->assertEquals($el2, $getNth1, "$el2 was not the timestamp returned by getNth(1) [$getNth1]");
        $this->assertEquals($el1, $getNext, "$el1 was not the timestamp returned by getNext() [$getNext]");

        // Last assertion
        $getNext  = $this->_history->getNext();
        $this->assertFalse($getNext, "Expected getNext() [$getNext] to return false");
    }

    private function getHistoryDir()
    {
        return dirname($this->_path);
    }
    
}
