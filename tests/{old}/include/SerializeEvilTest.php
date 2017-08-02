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

require_once('include/Sugarpdf/sugarpdf_config.php');
require_once('vendor/tcpdf/tcpdf.php');
require_once('vendor/Zend/Http/Response.php');
require_once('vendor/Zend/Http/Response/Stream.php');

class SerializeEvilTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function testSugarCacheFile()
    {
        if(file_exists(sugar_cached("testevil.php"))) @unlink(sugar_cached("testevil.php"));
        $obj = 'test';
        try {
            $obj = unserialize('O:14:"SugarCacheFile":3:{s:13:"_cacheChanged";b:1;s:14:"_cacheFileName";s:12:"testevil.php";s:11:"_localStore";b:1;}');
        } catch(Exception $e) {
            $obj = null;
        }
        $this->assertNull($obj);
        unset($obj); // call dtor if object created
        $this->assertFileNotExists(sugar_cached("testevil.php"));
    }

    public function getDestructors()
    {
        return array(
            array("SugarCacheFile"),
            array("SugarTheme"),
            array("tcpdf"),
            array("ImportFile"),
            array("Zend_Http_Response_Stream"),
        );
    }

    /**
     * @dataProvider getDestructors
     *
     */
    public function testUnserializeExcept($name)
    {
        $len = strlen($name);
        $obj = 'test';
        try {
            $obj = unserialize("O:$len:\"$name\":1:{s:4:\"test\";b:1;}");
        } catch(Exception $e) {
             $obj = null;
        }
        $this->assertEmpty($obj);
    }
}
