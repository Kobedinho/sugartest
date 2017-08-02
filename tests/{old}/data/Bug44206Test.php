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
 * @ticket 44206
 */
class Bug44206Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Temporary opportunity
     *
     * @var Opportunity
     */
    protected $opportunity;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * Creates a temporary opportunity
     */
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $this->opportunity->currency_id = -99;
        $this->opportunity->save();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * Removes temporary opportunity
     */
    public function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * Tests that currency-related properties are filled in at model layer
     * even when opportunity currency is the default one.
     */
    public function testDefaultCurrencyFieldsArePopulated()
    {
        $opportunity = new Opportunity();

        // disable row level security just to simplify the test
        $opportunity->disable_row_level_security = true;
        $list = $opportunity->get_list('', $where = 'opportunities.id = ' . $GLOBALS['db']->quoted($this->opportunity->id));

        $this->assertTrue(is_array($list));
        $this->assertArrayHasKey('list', $list);
        $this->assertTrue(is_array($list['list']));
        $this->assertNotEmpty($list['list']);

        /** @var Opportunity $entry */
        $entry = array_pop($list['list']);
        $this->assertNotEmpty($entry->currency_name);
        $this->assertNotEmpty($entry->currency_symbol);
    }
}
