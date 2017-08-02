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


/*
 * This tests for precision formatting from the sugarfieldcurrency object.  Prior to bug 55733, the value would get picked up from
 * the vardefs['precision'] value, instead of the currency settings.
 */

class Bug55733CurrencyTest extends Sugar_PHPUnit_Framework_TestCase
{

    private $value1 = '20000.0000';
    private $value2 = '20000';
    private $expectedValue = '20,000.00';
    private $vardef = array('precision' => '6');
    private $sfr;

    public function setUp()
    {
        global $locale, $current_user;
        SugarTestHelper::setUp('current_user', array(true));
        $current_user->setPreference('dec_sep', '.');
        $current_user->setPreference('num_grp_sep', ',');
        $current_user->setPreference('default_currency_significant_digits', 2);
        get_number_seperators(true);
        parent::setUp();
        //if locale is not defined, create new global locale object.
        if(empty($locale))
        {
            $locale = Localization::getObject();
        }

        //create a new SugarFieldCurrency object
        $this->sfr = new SugarFieldCurrency('currency');

    }

    public function testFormatPrecision()
    {
        //lets test some values with different decimals to make sure the formatting is returned correctly
        $testVal1 = $this->sfr->formatField($this->value1, $this->vardef);
        $testVal2 = $this->sfr->formatField($this->value2, $this->vardef);
        $this->assertSame($this->expectedValue, $testVal1,' The currency precision was not formatted correctly.');
        $this->assertSame($this->expectedValue, $testVal2,' The currency precision was not formatted correctly.');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        get_number_seperators(true);
    }
}