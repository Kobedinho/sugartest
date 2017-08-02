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

class AttachmentHeaderTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $ie = null;

    public function setUp()
    {
        $this->ie = new InboundEmail();
    }

    /**
     * @param $param -> "dparameters" | "parameters"
     * @param $a -> attribute
     * @param $v -> value
     * @return stdClass:  $obj->attribute = $a, $obj->value = $v
     */
    protected function _convertToObject($param,$a,$v)
    {
        $obj = new stdClass;
        $obj->attribute = $a;
        $obj->value = $v;

        $outer = new stdClass;
        $outer->parameters = ($param == 'parameters') ? array($obj) : array();
        $outer->isparameters = !empty($outer->parameters);
        $outer->dparameters = ($param == 'dparameters') ? array($obj) : array();
        $outer->isdparameters = !empty($outer->dparameters);

        return $outer;
    }

    public function contentParameterProvider()
    {
        return array(
            // pretty standard dparameters
            array(
                $this->_convertToObject('dparameters','filename','test.txt'),
                'test.txt'
            ),

            // how about a regular parameter set
            array(
                $this->_convertToObject('parameters','name','bonus.txt'),
                'bonus.txt'
            )
        );
    }

    /**
     * @group bug57309
     * @dataProvider contentParameterProvider
     * @param array $in - the part parameters -> will convert to object in test method
     * @param string $expected - the name digested from the parameters
     */
    public function testRetrieveAttachmentNameFromStructure($in, $expected)
    {
        $this->assertEquals($expected, $this->ie->retrieveAttachmentNameFromStructure($in),  'We did not get the attachmentName');
    }
}
