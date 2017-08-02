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

namespace Sugarcrm\SugarcrmTestsUnit\Console\Fixtures;

/**
 *
 * Unit test API object for \SugarApi
 *
 */
class UnitTestApi extends \SugarApi
{
    /**
     * Ctor override
     */
    public function __construct()
    {
    }

    /**
     * Test 1
     * @param \RestService $api
     * @param array $args
     * @return array
     */
    public function test1(\RestService $api, array $args)
    {
        return $args;
    }
}
