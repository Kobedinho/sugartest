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

namespace Sugarcrm\SugarcrmTestsUnit\Dbal\Logging;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Dbal\Logging\SugarLogger
 */
class SugarLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function startQueryDataProvider()
    {
        return array(
            array('Query: SELECT \'test\' FROM DUAL'),
            array(
                'Query: SELECT \'test\' FROM DUAL'
                    . PHP_EOL . 'Params: ["some-param"]',
                array('some-param'),
            ),
            array(
                'Query: SELECT \'test\' FROM DUAL'
                    . PHP_EOL . 'Params: ["some-param"]'
                    . PHP_EOL . 'Types: ["param-type"]',
                array('some-param'),
                array('param-type'),
            ),
        );
    }

    /**
     * @dataProvider startQueryDataProvider
     * @covers ::startQuery
     */
    public function testStartQuery($expectedMessage, array $params = null, array $types = null)
    {
        $loggerMock = $this->getMockBuilder('\LoggerManager')
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock->expects($this->any())
            ->method('wouldLog')
            ->will($this->returnValue(true));
        /** @var \Sugarcrm\Sugarcrm\Dbal\Logging\SugarLogger|\PHPUnit_Framework_MockObject_MockObject $sugarLogger */
        $sugarLogger = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dbal\Logging\SugarLogger')
            ->setConstructorArgs(array($loggerMock))
            ->setMethods(array('log'))
            ->getMock();
        $sugarLogger->expects($this->once())
            ->method('log')
            ->with($expectedMessage);

        $sugarLogger->startQuery('SELECT \'test\' FROM DUAL', $params, $types);
    }

    /**
     * @covers ::startQuery
     */
    public function testStartQueryNoLogging()
    {
        $loggerMock = $this->getMockBuilder('\LoggerManager')
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock->expects($this->any())
            ->method('wouldLog')
            ->will($this->returnValue(false));
        /** @var \Sugarcrm\Sugarcrm\Dbal\Logging\SugarLogger|\PHPUnit_Framework_MockObject_MockObject $sugarLogger */
        $sugarLogger = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dbal\Logging\SugarLogger')
            ->setConstructorArgs(array($loggerMock))
            ->setMethods(array('log'))
            ->getMock();
        $sugarLogger->expects($this->never())
            ->method('log');

        $sugarLogger->startQuery('SELECT \'test\' FROM DUAL');
    }
}
