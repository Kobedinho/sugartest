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

require_once('install/install_utils.php');
require_once('modules/TimePeriods/TimePeriod.php');

class PopulateOppSeedDataTest extends Sugar_PHPUnit_Framework_TestCase
{

private $createdOpportunities;

function setUp()
{
    SugarTestHelper::setUp('beanFiles');
    SugarTestHelper::setUp('beanList');
    SugarTestHelper::setUp('app_list_strings');
    global $current_user;
    SugarTestHelper::setUp('current_user');
    $current_user->is_admin = 1;
    $current_user->save();
    $GLOBALS['db']->query("UPDATE opportunities SET deleted = 1");
}

function tearDown()
{
    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    SugarTestAccountUtilities::removeAllCreatedAccounts();
    SugarTestProductUtilities::removeAllCreatedProducts();
    $GLOBALS['db']->query("UPDATE opportunities SET deleted = 0");
    if ( $this->createdOpportunities )
    {
        $ids = "('" . implode("','", $this->createdOpportunities) . "')";
        $GLOBALS['db']->query("DELETE FROM opportunities WHERE id IN $ids");
        $GLOBALS['db']->query("DELETE FROM products WHERE opportunity_id IN $ids");
    }
}

    public static function dataProviderMonthDelta()
    {
        $return = array();
        for ($m = 0; $m < 24; $m++) {
            $return[] = array($m);
        }

        return $return;
    }

    /**
     * @dataProvider dataProviderMonthDelta
     * @group opportunities
     */
    public function testCreatePastDate($monthDelta)
    {
        $now = new DateTime();
        $now->setTime(23, 59, 59);
        $date = OpportunitiesSeedData::createPastDate($monthDelta);
        $objDate = new DateTime($date);
        $this->assertLessThan($now->format('U'), $objDate->format('U'));
    }

    /**
     * @dataProvider dataProviderMonthDelta
     * @group opportunities
     */
    public function testCreateDate($monthDelta)
    {
        $now = new DateTime();
        $now->setTime(0, 0, 0);
        $date = OpportunitiesSeedData::createDate($monthDelta);
        $objDate = new DateTime($date);
        $this->assertGreaterThanOrEqual($now->format('U'), $objDate->format('U'));
    }

/**
 * @outputBuffering disabled
 */
function testPopulateSeedData()
{
    $this->markTestIncomplete("DB Failure on Strict Mode");
    global $app_list_strings, $current_user;
    $total = 200;
    $account = BeanFactory::newBean('Accounts');
    $product = BeanFactory::newBean('Products');
    $user = new User();
    $account->disable_row_level_security = true;
    $product->disable_row_level_security = true;
    $user->disable_row_level_security = true;

    $accounts = $account->build_related_list("SELECT id FROM accounts WHERE deleted = 0", $account, 0, $total);

    //Accounts may have been deleted by some other tests
    if(count($accounts) < $total)
    {
       $count_accounts = count($accounts);
       while($count_accounts++ < $total) {
             $accounts[] = SugarTestAccountUtilities::createAccount();
       }
    }

    $products = $account->build_related_list("SELECT id FROM products WHERE deleted = 0", $product, 0, $total);

    if(count($products) < $total)
    {
       $count_products = count($products);
       while($count_products++ < $total) {
             $products[] = SugarTestProductUtilities::createProduct();
       }
    }

    //echo count($products);
    $result = $GLOBALS['db']->limitQuery("SELECT id FROM users WHERE deleted = 0 AND status = 'Active'", 0, $total);
    $users = array();
    while(($row = $GLOBALS['db']->fetchByAssoc($result)))
    {
        if($row['id'] != $current_user->id)
        {
            $users[$row['id']] = $row['id'];
        }
    }

    $this->createdOpportunities = OpportunitiesSeedData::populateSeedData($total, $app_list_strings, $accounts, $users);
    $this->assertEquals(200, count($this->createdOpportunities));

}


}
