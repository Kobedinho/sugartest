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
 * @covers ::parseShorthandBytes
 */
class ParseShorthandBytesTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider shorthandBytesProvider
     */
    public function testParseShorthandBytes($string, $expected)
    {
        $actual = parseShorthandBytes($string);
        $this->assertSame($expected, $actual);
    }

    public static function shorthandBytesProvider()
    {
        return array(
            array('1048576', 1048576),
            array('100K', 102400),
            array('8m', 8388608),
            array('1G', 1073741824),
            array('20X', 20),
            array('-1', null),
            array('-1K', null),
        );
    }
}
