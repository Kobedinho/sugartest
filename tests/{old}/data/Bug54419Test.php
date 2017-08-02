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
 * Bug #54419
 *
 *
 * @author mgusev@sugarcrm.com
 * @ticked 54419
 */
class Bug54419Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Account
     */
    protected $accountShipping = null;

    /**
     * @var Account
     */
    protected $accountBilling = null;

    /**
     * @var Quote
     */
    protected $quote = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');

        parent::setUp();

        $this->accountShipping = SugarTestAccountUtilities::createAccount();
        $this->accountShipping->name = __CLASS__ . 'shipping';
        $this->accountShipping->save();

        $this->accountBilling = SugarTestAccountUtilities::createAccount();
        $this->accountBilling->name = __CLASS__ . 'billing';
        $this->accountBilling->save();

        $this->quote = SugarTestQuoteUtilities::createQuote();
        $this->quote->billing_account_id = $this->accountBilling->id;
        $this->quote->billing_account_name = $this->accountBilling->name;
        $this->quote->shipping_account_id = $this->accountShipping->id;
        $this->quote->shipping_account_name = $this->accountShipping->name;
        $this->quote->save();
    }

    public function tearDown()
    {
        // Restoring $GLOBALS
        parent::tearDown();
        $_REQUEST = array();
        unset($_SERVER['REQUEST_METHOD']);
        unset($GLOBALS['currentModule']);

        // Removing temp data
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test tries to assert that quote is present in shipping account
     *
     * @group 54419
     * @return void
     */
    public function testShippingAccount()
    {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        $this->quote->shipping_account_id = $this->accountShipping->id;
        $this->quote->shipping_account_name = $this->accountShipping->name;
        $this->quote->save();

        // Getting data of subpanel
        $_REQUEST['module'] = 'Accounts';
        $_REQUEST['action'] = 'DetailView';
        $_REQUEST['record'] = $this->accountShipping->id;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $GLOBALS['currentModule'] = 'Accounts';
        unset($GLOBALS['focus']);
        $subpanels = new SubPanelDefinitions($this->accountShipping, 'Accounts');
        $subpanelDef = $subpanels->load_subpanel('quotes');
        $subpanel = new SubPanel('Accounts', $this->accountShipping->id, 'quotes', $subpanelDef, 'Accounts');
        $subpanel->setTemplateFile('include/SubPanel/SubPanelDynamic.html');
        $subpanel->display();
        $actual = $this->getActualOutput();

        $this->assertContains($this->quote->name, $actual, 'Quote name is not displayed in subpanel');
    }


    /**
     * Test tries to assert that pagination is correct if billing & shipping accounts are the same
     *
     * @group 51043
     * @return void
     */
    public function testDoublePagination()
    {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        $this->quote->shipping_account_id = $this->accountBilling->id;
        $this->quote->shipping_account_name = $this->accountBilling->name;
        $this->quote->save();

        // Getting data of subpanel
        $_REQUEST['module'] = 'Accounts';
        $_REQUEST['action'] = 'DetailView';
        $_REQUEST['record'] = $this->accountBilling->id;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $GLOBALS['currentModule'] = 'Accounts';
        unset($GLOBALS['focus']);
        $subpanels = new SubPanelDefinitions($this->accountBilling, 'Accounts');
        $subpanelDef = $subpanels->load_subpanel('quotes');
        $subpanel = new SubPanel('Accounts', $this->accountBilling->id, 'quotes', $subpanelDef, 'Accounts');
        $subpanel->setTemplateFile('include/SubPanel/SubPanelDynamic.html');
        $subpanel->display();
        $actual = $this->getActualOutput();

        $this->assertContains('(1 - 1 of 1)', $actual, 'Number of quotes is incorrect in subpanel');
    }
}
