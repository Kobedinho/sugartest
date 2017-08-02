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
 * @covers SugarCleaner
 */
class SugarCleanerTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider cleanHtmlProvider
     */
    public function testCleanHtml($args, $expected)
    {
        $actual = SugarCleaner::cleanHtml($args[0], $args[1]);

        $this->assertEquals($expected, $actual, 'Html did not get cleaned as expected');

    }

    public static function cleanHtmlProvider()
    {
        return array(
            /* script tags should be removed */
            array(
                array(" World &lt;script&gt;alert('Hello');&lt;/script&gt;", true),
                " World "
            ),
            /* double encoded script tags should be removed */
            array(
                array("Hello &amp;lt;script&amp;gt;alert(&#039;Hi&#039;);&amp;lt;/script&amp;gt; World", true),
                "Hello  World"
            ),
            /* Non harmful tags like bold tags should be allowed */
            array(
                array("&lt;b&gt;Hello&lt;/b&gt;", true),
                "&lt;b&gt;Hello&lt;/b&gt;"
            ),
            /* Normal text without html should pass through unchanged */
            array(
                array("Hello", true),
                "Hello"
            ),
            /* Try one test with false i.e., Not entity encoded */
            array(
                array("<b>Hello</b>", false),
                "<b>Hello</b>"
            ),
            /* Try area/map tags and img usemap attribute*/
            array(
                array(
                    '<map name="my_map">
                        <area target="_blank" href="http://www.example.org/" shape="rect" coords="8, 10, 674, 423" />
                        <area target="_blank" href="http://stores.ebay.com/MyStore/" shape="rect" coords="11, 464, 157, 746" />
                        <area target="_blank" href="http://stores.ebay.com/MyOtherStore" shape="rect" coords="175, 459, 328, 750" />
                        <area target="_blank" href="http://stores.ebay.com/MyThirdStore" shape="rect" coords="351, 466, 499, 748" />
                        <area target="_blank" href="http://www.example2.org/" shape="rect" coords="521, 463, 675, 745" />
                        <area href="test@example.org?subject=Test" shape="circle" coords="24, 790, 14" />
                    </map>
                    <img border="0" src="http://www.example.org/Newsletter.jpg" width="676" height="838" usemap="#my_map" />
                    ',
                    false
                ),
                '<map name="my_map"><area target="_blank" href="http://www.example.org/" shape="rect" coords="8, 10, 674, 423" /><area target="_blank" href="http://stores.ebay.com/MyStore/" shape="rect" coords="11, 464, 157, 746" /><area target="_blank" href="http://stores.ebay.com/MyOtherStore" shape="rect" coords="175, 459, 328, 750" /><area target="_blank" href="http://stores.ebay.com/MyThirdStore" shape="rect" coords="351, 466, 499, 748" /><area target="_blank" href="http://www.example2.org/" shape="rect" coords="521, 463, 675, 745" /><area href="test@example.org?subject=Test" shape="circle" coords="24, 790, 14" /></map><img border="0" src="http://www.example.org/Newsletter.jpg" width="676" height="838" usemap="#my_map" alt="Newsletter.jpg" />'
            ),
        );
    }
}
