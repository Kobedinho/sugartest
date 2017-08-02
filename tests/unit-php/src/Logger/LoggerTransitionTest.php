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

namespace Sugarcrm\SugarcrmTestsUnit\Logger;

use Sugarcrm\Sugarcrm\Logger\LoggerTransition;
use Psr\Log\LogLevel;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Logger\LoggerTransition
 *
 */
class LoggerTransitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::<public>
     * @covers ::getSugarLevel
     * @dataProvider dataProviderTestLog
     *
     * @param string $psrLevel
     * @param string $sugarLevel
     * @param string $message
     */
    public function testLog($psrLevel, $sugarLevel, $message)
    {
        $logMan = $this->getMockBuilder('LoggerManager')
            ->disableOriginalConstructor()
            ->setMethods(array($sugarLevel))
            ->getMock();

        $logMan->expects($this->once())
            ->method($sugarLevel)
            ->with($this->equalTo($message));

        $logger = new LoggerTransition($logMan);
        call_user_func_array(array($logger, 'log'), array($psrLevel, $message));
    }

    public function dataProviderTestLog()
    {
        return array(
            array(LogLevel::EMERGENCY, "fatal", "hello world 1"),
            array(LogLevel::ALERT, "fatal", "hello world 2"),
            array(LogLevel::CRITICAL, "fatal", "hello world 3"),
            array(LogLevel::ERROR, "error", "hello world 4"),
            array(LogLevel::WARNING, "warn", "hello world 5"),
            array(LogLevel::NOTICE, "info", "hello world 6"),
            array(LogLevel::INFO, "info", "hello world 7"),
            array(LogLevel::DEBUG, "debug", "hello world 8"),
        );
    }
}
