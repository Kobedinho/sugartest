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

require_once 'tests/{old}/service/SOAPTestCase.php';
require_once 'modules/Currencies/Currency.php';

/**
 * Test attaching contact to acccount which is not visible to current user. Should create a new account instead.
 *
 */
class Bug55129Test extends SOAPTestCase
{
    const CURRENCY_CODE = 'EUR';

    private $currency;

    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';

        parent::setUp();

        $this->user = self::$_user = SugarTestUserUtilities::createAnonymousUser();
        $this->currency            = BeanFactory::newBean('Currencies');
        $GLOBALS['current_user']   = SOAPTestCase::$_user;

        $found = $this->currency->retrieve_by_string_fields(array(
            'iso4217' => self::CURRENCY_CODE,
        ));

        if (!$found)
        {
            $this->markTestSkipped('Currency \'' . self::CURRENCY_CODE . '\' not found.');
        }
    }

    public function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        unset($GLOBALS['current_user']);
    }

    public function dataset()
    {
        return array(
            // [create_currency, retrieve_currency, amount]
            array('US', 'US', 10000),
            array('US', 'EUR', 10000),
            array('EUR', 'US', 9000),
            array('EUR', 'EUR', 10000),
        );
    }

    /**
     * @dataProvider dataset
     */
    public function testContactAccount($createCurrency, $retrieveCurrency, $amount)
    {
        global $current_user;

        if ($createCurrency == self::CURRENCY_CODE)
        {
            $createCurrencyId = $this->currency->id;
        }
        else
        {
            $createCurrencyId = '-99';
        }

        if ($retrieveCurrency == self::CURRENCY_CODE)
        {
            $retrieveCurrencyId     = $this->currency->id;
            $retrieveCurrencyName   = $this->currency->name;
            $retrieveCurrencySymbol = $this->currency->symbol;
        }
        else
        {
            $retrieveCurrencyId     = '-99';
            $retrieveCurrencyName   = 'US Dollars';
            $retrieveCurrencySymbol = '$';
        }

        $current_user->setPreference('currency', $createCurrencyId);
        $current_user->savePreferencesToDB();
        
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $opportunity->currency_id = $createCurrencyId;
        $opportunity->amount = $amount;
        $opportunity->save();
        
        $current_user->setPreference('currency', $retrieveCurrencyId);
        $current_user->savePreferencesToDB();
        
        $this->_login();

        $client = array(
            'session'       => $this->_sessionId, 
            'module_name'   => 'Opportunities',
            'query'         => 'opportunities.id = \'' . $opportunity->id . '\'',
            'order_by'      => '',
            'offset'        => 0,
            'select_fields' => array(),
            'max_results'   => 10,
            'deleted'       => -1,
        );

        $result = $this->_soapClient->call('get_entry_list', $client);

        $this->assertEquals(0, $result['error']['number'], 'Soap failed: ' . $result['error']['description']);
        $this->assertGreaterThan(0, $result['result_count'], 'Empty result returned');

        $opportunityData = array_shift($result['entry_list']);
        $dataIndex = array();
        $dataLength = count($opportunityData['name_value_list']);

        for ($i = 0; $i < $dataLength; $i ++)
        {
            $piece = $opportunityData['name_value_list'][$i];
            $dataIndex[$piece['name']] = $piece['value'];
        }

        $this->assertEquals($retrieveCurrencySymbol, $dataIndex['currency_symbol'], 'Currency symbol is not match.');
        $this->assertEquals($retrieveCurrencyName, $dataIndex['currency_name'], 'Currency name is not match.');

        if ($createCurrencyId == $retrieveCurrencyId)
        {
            $this->assertEquals($amount, $dataIndex['amount'], 'Invalid amount.');
        }
        else if ($createCurrencyId == '-99')
        {
            $converted = $dataIndex['amount'] / $this->currency->conversion_rate;

            $this->assertEquals($amount, $converted, 'Converted amount is not valid for opportunity created in USD.');
            $this->assertEquals($amount, $dataIndex['amount_usdollar'], 'USD amount is not valid for opportunity created in USD.');
        }
        else
        {
            $converted = $dataIndex['amount_usdollar'] * $this->currency->conversion_rate;

            $this->assertEquals($amount, $dataIndex['amount'], 'EUR amount is not valid for opportunity created in EUR.');
            $this->assertEquals($amount, $converted, 'Converted amount is not valid for opportunity created in EUR.');
        }
    }

}

