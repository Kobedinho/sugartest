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

require_once 'tests/{old}/include/SugarCache/SugarCacheAbstractTest.php';

/**
 * @covers SugarCacheAPC
 * @uses SugarCacheAbstract
 */
class SugarCacheApcTest extends SugarCacheAbstractTest
{
    protected function newInstance()
    {
        return new SugarCacheAPC();
    }

    public function testExpiration()
    {
        $this->markTestSkipped('Cannot test APC expiration since the value is cleaned on the next request.');
    }
}
