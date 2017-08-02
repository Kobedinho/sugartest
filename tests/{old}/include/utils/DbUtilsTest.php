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

require_once 'include/utils/db_utils.php';

class DbUtilsTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function testReturnsSameValueOnNoneStrings()
    {
        $random = rand(100, 200);
        $this->assertEquals($random, from_html($random));
    }

    public function testWillReturnRawValueIfEncodeParameterIsFalse()
    {
        $this->assertEquals('bar&lt;foo', from_html('bar&lt;foo', false));
        $this->assertEquals('bar<foo', from_html('bar&lt;foo', true));
    }
}

