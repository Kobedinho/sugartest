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

namespace Sugarcrm\SugarcrmTestsUnit\Console\Command\Password;

/**
 *
 * Password Command Test Case
 *
 */
abstract class AbstractPasswordCommandTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $fixturePath;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        self::$fixturePath = __DIR__ . '/../../Fixtures/Password/';
    }
}
