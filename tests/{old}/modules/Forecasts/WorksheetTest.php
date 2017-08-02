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

class WorksheetTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        SugarTestCurrencyUtilities::createCurrency('MonkeyDollars', '$', 'MOD', 2.0);
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
    }

    /**
     * Test that the base_rate field is populated with rate
     * of currency_id
     *
     * @group forecasts
     * @group worksheet
     */
    public function testWorksheetRate()
    {
        $worksheet = SugarTestWorksheetUtilities::createWorksheet();
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        $worksheet->currency_id = $currency->id;
        $worksheet->save();
        $this->assertEquals($worksheet->base_rate,$currency->conversion_rate,'',2);
    }
}
