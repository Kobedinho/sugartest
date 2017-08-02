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
class PMSELoggerTest extends PHPUnit_Framework_TestCase 
{
    /**
     * Sets up the test data, for example, 
     *     opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        
    }

    /**
     * Removes the initial test configurations for each test, for example:
     *     close a network connection. 
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }
    
    /**
     * 
     * 
     */
    public function testGetInstance()
    {
        $instance = PMSELogger::getInstance();
        $this->assertInstanceOf('PMSELogger', $instance);
    }
    
    /**
     * 
     * @expectedException PHPUnit_Framework_Error
     */
    public function testClone()
    {
        $instance = PMSELogger::getInstance();
        $clone = clone($instance);
    }
    
    public function testLog()
    {
        $loggerMock = $this->getMockBuilder('PMSELogger')
                ->disableOriginalConstructor()
                ->setMethods(array('formatMessage', 'write'))
                ->getMock();
        
           
        $loggerMock->expects($this->once())
                ->method('write');
        $loggerMock->setLogLevel(LogLevel::ALERT);
        $loggerMock->log(LogLevel::EMERGENCY, 'some message');
    }
    
    public function testLogInvalid()
    {
        $loggerMock = $this->getMockBuilder('PMSELogger')
                ->disableOriginalConstructor()
                ->setMethods(array('formatMessage', 'write'))
                ->getMock();
        $loggerMock->setLogLevel(LogLevel::EMERGENCY);
        $loggerMock->log(LogLevel::ALERT, 'some message');
    }
    
    public function testLogInvalidContext()
    {
        $loggerMock = $this->getMockBuilder('PMSELogger')
                ->disableOriginalConstructor()
                ->setMethods(array('write'))
                ->getMock();
        
        $loggerMock->expects($this->once())
                ->method('write');
        $loggerMock->setLogLevel(LogLevel::ALERT);
        $loggerMock->log(LogLevel::EMERGENCY, 'some message', array());
    }
    
    public function testWrite()
    {
        $loggerMock = $this->getMockBuilder('PMSELogger')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();
        $writerMock = $this->getMockBuilder('PMSELogWriter')
                ->disableOriginalConstructor()
                ->setMethods(array('log'))
                ->getMock();
        
        $writerMock->expects($this->once())
                ->method('log');
        
        $loggerMock->setLogWriter($writerMock);
        
        $loggerMock->write(LogLevel::EMERGENCY, 'Some Message');
    }        
    
    public function testActivity()
    {
        
    }
    //put your tests code here
}
