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
 * @ticket 49983
 */
class DecodeHeaderTest extends Sugar_PHPUnit_Framework_TestCase
{
	protected $ie = null;

	public function setUp()
    {
		$this->ie = new InboundEmail();
	}

    public function getDecodingHeader()
    {
        return array(
            array('Content-Type: text/html; charset="utf-8"', 
                array(
                    "Content-Type" => array(
                        "type" => "text/html",
                        "charset" => "utf-8",
                        ),
                    ),
                ),
            array('Content-Type: text/html; charset=utf-8', 
                array(
                    "Content-Type" => array(
                        "type" => "text/html",
                        "charset" => "utf-8",
                        ),
                    ),
                ),
            array('Content-Type: text/html; charset=    utf-8', 
                array(
                    "Content-Type" => array(
                        "type" => "text/html",
                        "charset" => "utf-8",
                        ),
                    ),
                ),
            );
    }

    /**
     * @dataProvider getDecodingHeader
     * @param string $url
     */
	function testDecodingHeader($header, $res)
	{
	    $ie = new InboundEmail();
	    $this->assertEquals($res,$ie->decodeHeader($header));
	}


    public function intlTextProvider()
    {
        return array(
            // commenting out windows-1256, since PHP doesn't have an easy way to detect this encoding.
//            array(
//                '7cvU3iDI5d7L7O3TIOUg1cfU7N0g3c4g5ezR1NPd5eHU3csg287a',
//                'يثشق بهقثىيس ه صاشىف فخ هىرشسفهلشفث غخع',
//                // 'windows-1256'
//            ),
//            array(
//                '7cjT7cjU0+3IwcbExNE=',
//                'يبسيبشسيبءئؤؤر',
//                // 'windows-1256'
//            ),
            array( // params related to 45059 ticket
                'GyRCJWYhPCU2TD4bKEI=',
                'ユーザ名',
                // 'ISO-2022-JP'
            ),
            array(
                '5LiN6KaB55u06KeG6ZmM55Sf5Lq655qE55y8552b',
                '不要直视陌生人的眼睛',
                // 'utf-8'
            )
        );

    }
    /**
     * @group bug45059
     * @dataProvider intlTextProvider
     * @param string $inputText - our input from the provider, base64'ed
     * @param string $expected - what our goal is
     */
    public function testConvertToUtf8($inputText, $expected)
    {
        // the email server is down, so this test doesn't work
        if (!function_exists('mb_convert_encoding')) {
            $this->markTestSkipped('Need multibyte encoding support');
        }

        $ie = new InboundEmail();
        $inputText = base64_decode($inputText);
        $this->assertEquals($expected, $ie->convertToUtf8($inputText), 'We should be able to convert to UTF-8');
    }

}