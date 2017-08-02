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


 

class Bug47737Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $beanList, $beanFiles;
        require('include/modules.php');
    }

    public function tearDown()
    {
        restore_error_handler();
    }

    public function providerIdData()
    {
        return array(
            //Valid ids
            array('12345','12345'),
            array('12345-6789-1258','12345-6789-1258'),
            array('aaaBBB12AA122cccD','aaaBBB12AA122cccD'),
            array('aaa-BBB-12AA122-cccD','aaa-BBB-12AA122-cccD'),
            array('aaa_BBB_12AA122_cccD','aaa_BBB_12AA122_cccD'),
            //Invalid
            array('1242','12*'),
            array('abdcd36','abdcd$'),
            array('1234-asdf3535353523','1234-asdf####23'),
            );
    }

    /**
     * @ticket 47737
     * @dataProvider providerIdData
     */
    public function testConvertID($expected, $dirty)
    {
        $c = new Contact();
        $importer = new ImporterStub('UNIT TEST',$c);
        $actual = $importer->convertID($dirty);

        $this->assertEquals($expected, $actual, "Error converting id during import process $actual , expected: $expected, before conversion: $dirty");
    }

}


class ImporterStub extends Importer
{

    public function convertID($id)
    {
        return $this->_convertId($id);
    }

    //Override this function here since we don't set the importSource member variable because we don't call constructor
    protected function getFieldSanitizer()
    {
        return new ImportFieldSanitize();
    }
}