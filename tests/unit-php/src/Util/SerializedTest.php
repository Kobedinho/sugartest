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

namespace Sugarcrm\SugarcrmTestsUnit\Util;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Util\Serialized
 *
 */
class SerializedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::unserialize
     * @dataProvider providerTestUnserialize
     */
    public function testUnserialize($value, $default, $base64, $html, $expected)
    {
        $this->assertEquals(
            $expected,
            \Sugarcrm\Sugarcrm\Util\Serialized::unserialize($value, $default, $base64, $html)
        );
    }
    public function providerTestUnserialize()
    {
        return array(
            array(
                'b:0;',
                false,
                false,
                false,
                false,
            ),
            array(
                'b:1;',
                false,
                false,
                false,
                true,
            ),
            array(
                'i:10;',
                null,
                false,
                false,
                10,
            ),
            array(
                'd:12.199999999999999;',
                null,
                false,
                false,
                12.2,
            ),
            array(
                's:6:"String";',
                null,
                false,
                false,
                'String',
            ),
            array(
                'a:1:{s:3:"foo";s:3:"bar";}',
                array(),
                false,
                false,
                array('foo' => 'bar'),
            ),
            array(
                'O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}',
                null,
                false,
                false,
                false,
            ),
            array(
                'a:2:{s:3:"foo";s:3:"bar";s:3:"baz";O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}}',
                null,
                false,
                false,
                false,
            ),
            array(
                'a:2:{s:3:"foo";s:3:"bar";s:3:"baz";O:+8:"stdClass":1:{s:3:"foo";s:3:"bar";}}',
                null,
                false,
                false,
                false,
            ),
            array(
                'a:2:{s:3:"foo";s:3:"bar";s:3:"baz";O: qwerty 8:"stdClass":1:{s:3:"foo";s:3:"bar";}}',
                null,
                false,
                false,
                false,
            ),
            array(
                'a:2:{s:3:"foo";s:3:"bar";s:3:"baz";O:\x008:"stdClass":1:{s:3:"foo";s:3:"bar";}}',
                null,
                false,
                false,
                false,
            ),
            array(
                'a:2:{s:3:"foo";s:3:"bar";s:3:"baz";O:0x8:"stdClass":1:{s:3:"foo";s:3:"bar";}}',
                null,
                false,
                false,
                false,
            ),
            array(
                'O:8:',
                null,
                false,
                false,
                null,
            ),
            array(
                'O:0x8:',
                null,
                false,
                false,
                null,
            ),
            array(
                'O:\x08:',
                null,
                false,
                false,
                null,
            ),
            array(
                'O:0b1000:',
                null,
                false,
                false,
                null,
            ),
            array(
                'YToyOntzOjE6ImYiO3M6MToibyI7czoxOiJiIjtPOjg6InN0ZENsYXNzIjoxOntzOjM6ImZvbyI7czoyOiJiYSI7fX0=',
                null,
                true,
                false,
                null,
            ),
            array(
                'YToyOntzOjE6ImYiO3M6MToibyI7czoxOiJiIjtPOjg6InN0ZENsYXNzIjoxOntzOjM6ImZvbyI7czoyOiJiYSI7fX0=',
                false,
                true,
                false,
                false,
            ),
            array(
                'YToxOntzOjM6ImZvbyI7czozOiJiYXIiO30=',
                array(),
                true,
                false,
                array('foo' => 'bar'),
            ),
            array(
                's:28:&quot;&lt;div class=&quot;link&quot;&gt;Link&lt;/div&gt;&quot;;',
                null,
                false,
                true,
                '<div class="link">Link</div>',
            ),
            array(
                'czoyODomcXVvdDsmbHQ7ZGl2IGNsYXNzPSZxdW90O2xpbmsmcXVvdDsmZ3Q7TGluayZsdDsvZGl2Jmd0OyZxdW90Ozs=',
                null,
                true,
                true,
                '<div class="link">Link</div>',
            ),
        );
    }
}
