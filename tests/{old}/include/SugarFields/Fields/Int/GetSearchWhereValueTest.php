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


class GetSearchWhereValueTest extends Sugar_PHPUnit_Framework_TestCase {

    var $intField;

    public function setUp() {
        $this->intField = SugarFieldHandler::getSugarField('int');
    }

    public function tearDown() {
        unset($this->intField);
    }

    /**
     * testGetSearchWhereValue
     *
     * tests SugarFieldInt::getSearchWhereValue() function
     *
     * @dataProvider  getSearchWhereProvider
     */
    public function testGetSearchWhereValue($exp, $val) {
        $this->assertSame($exp, $this->intField->getSearchWhereValue($val));
    }

    /**
     * getSearchWhereProvider
     *
     * provides values for testing SugarFieldInt::getSearchWhereValue
     *
     * @return Array values for testing
     */
    public function getSearchWhereProvider() {
        return array(
            array(123, 123),
            array(-1, 'test'),
            array('12,14,16', '12,14,16'),
            array('12,-1,16', '12,junk,16'),
            array('-1,12,-1,16,34,124,-1', 'stuff,12,junk,16,34,124,morejunk'),
            array(-1, ''),
        );
    }

}
