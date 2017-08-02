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

namespace Sugarcrm\SugarcrmTestUnit\modules\Mailer;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \SMTPProxy
 */
class SMTPProxyTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        unset($GLOBALS['log']);
        parent::tearDown();
    }

    public function noErrorProvider()
    {
        return [
            [
                '',
                '',
                '',
                '',
            ],
            [
                null,
                'Error with no message',
                '404',
                'Error should not catch',
            ],
        ];
    }

    /**
     * @covers ::setError
     * @dataProvider noErrorProvider
     * @param $message
     * @param $detail
     * @param $smtpCode
     * @param $smtpCodeEx
     */
    public function testSetError_NothingIsLogged($message, $detail, $smtpCode, $smtpCodeEx)
    {
        $levels = \LoggerManager::getLoggerLevels();
        $levels = array_keys($levels);

        $GLOBALS['log'] = $this->createPartialMock(\stdClass::class, $levels);

        foreach ($levels as $level) {
            $GLOBALS['log']->expects($this->never())->method($level);
        }

        $proxy = new \SMTPProxy();
        TestReflection::callProtectedMethod($proxy, 'setError', [$message, $detail, $smtpCode, $smtpCodeEx]);
    }

    public function hasErrorProvider()
    {
        return [
            [
                'fatal',
                'Fatal error occurred',
                'A fatal error has occurred',
                '500',
                'Address not permitted',
            ],
            [
                'warn',
                'Error occurred',
                'A general error has occurred',
                '',
                '',
            ],
        ];
    }

    /**
     * @covers ::setError
     * @dataProvider hasErrorProvider
     * @param $level
     * @param $message
     * @param $detail
     * @param $smtpCode
     * @param $smtpCodeEx
     */
    public function testSetError_ErrorIsLogged($level, $message, $detail, $smtpCode, $smtpCodeEx)
    {
        $GLOBALS['log'] = $this->createPartialMock(\stdClass::class, [$level]);
        $GLOBALS['log']->expects($this->once())->method($level);

        $proxy = new \SMTPProxy();
        TestReflection::callProtectedMethod($proxy, 'setError', [$message, $detail, $smtpCode, $smtpCodeEx]);
    }
}
