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


class ForecastsDefaultsTest extends Sugar_PHPUnit_Framework_TestCase
{
    // holds any current config already set up in the DB for forecasts
    private static $currentConfig;

    public static function setUpBeforeClass()
    {
        /*
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        // Save the current config to be put back later
        $admin = BeanFactory::getBean('Administration');
        self::$currentConfig = $admin->getConfigForModule('Forecasts');*/
    }

    public function setUp()
    {
        $this->markTestSkipped('Skipping by SFA');
        parent::setUp();

        //Clear config table of Forecasts values before each test, so each test can setup it's own db
        //$db = DBManagerFactory::getInstance();
        //$db->query("DELETE FROM config WHERE category = 'Forecasts' ");

        SugarTestForecastUtilities::setUpForecastConfig(
            array(
                'forecast_by' => 'Opportunities',
                'sales_stage_won' => array('won'),
                'sales_stage_lost' => array('won')
            )
        );
    }

    public function tearDown()
    {
        //SugarTestForecastUtilities::tearDownForecastConfig();
        //parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        /*
        // Clear config table of Forecasts values after the last test in case tests
        // set any values that the bean doesnt normally have
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config WHERE category = 'Forecasts' ");

        $admin = BeanFactory::getBean('Administration');
        self::saveConfig(self::$currentConfig, $admin);
        SugarTestHelper::tearDown();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        */
    }

    /**
     * Tests the setupForecastSettings for a fresh install where configs are not in the db
     *
     * @group forecasts
     */
    public function testSetupForecastSettingsFreshInstall()
    {
        ForecastsDefaults::setupForecastSettings();

        $admin = BeanFactory::newBean('Administration');
        $adminConfig = $admin->getConfigForModule('Forecasts');

        // On fresh install, is_setup should be 0 in the DB
        $this->assertEquals(0, $adminConfig['is_setup'], "On a fresh install, Forecasts config is_setup should be 0");
    }

    /**
     * Tests the setupForecastSettings for an upgrade where configs are already in the db
     * and is_setup == 0, should force defaults on the db
     *
     * @group forecasts
     */
    public function testSetupForecastSettingsUpgradeNotSetup()
    {
        // set up config table with one test value and is_setup set to 0
        // test should show that if is_setup is 0, already existing values are overwritten
        // by any new defaults used in the ForecastsDefaults class
        $timeperiodType = 'previousVersionDefaultTimePeriod1';
        $setupConfig = array(
            'is_setup' => 0,
            'timeperiod_type' => $timeperiodType
        );

        $admin = BeanFactory::newBean('Administration');
        $this->saveConfig($setupConfig, $admin);

        ForecastsDefaults::setupForecastSettings(true);

        $admin = BeanFactory::newBean('Administration');
        $adminConfig = $admin->getConfigForModule('Forecasts');

        $defaultConfig = ForecastsDefaults::getDefaults();

        // Check value from ForecastDefaults and make sure they're in the db on upgrade
        $this->assertNotEquals(
            $timeperiodType,
            $adminConfig['timeperiod_type'],
            "On an upgrade with config data existing but NOT set up, new default settings should override pre-existing settings in the config table"
        );
    }

    /**
     * Tests the setupForecastSettings for an upgrade where configs are already in the db
     * and is_setup == 1
     *
     * @group forecasts
     */
    public function testSetupForecastSettingsUpgradeAlreadySetup()
    {
        // set up config table with one test value and is_setup set to 1
        // test should show that if is_setup is 1, already existing values are preserved
        // while if the value doesnt exist, defaults are used
        $timeperiodType = 'testTimePeriod1';
        $setupConfig = array(
            'is_setup' => 1,
            'timeperiod_type' => $timeperiodType
        );

        $admin = BeanFactory::newBean('Administration');
        $this->saveConfig($setupConfig, $admin);

        ForecastsDefaults::setupForecastSettings(true);

        $adminConfig = $admin->getConfigForModule('Forecasts');

        $this->assertEquals(
            $timeperiodType,
            $adminConfig['timeperiod_type'],
            "On an upgrade with config data already set up, pre-existing settings should be preserved"
        );

        // Check value from ForecastDefaults
        $this->assertEquals(
            'Annual',
            $adminConfig['timeperiod_interval'],
            "On an upgrade with config data already set up, default settings that don't override pre-existing settings should be in the config table"
        );
    }

    /**
     * Test the getConfigDefaultByKey method that should return the default value used
     *
     * @group forecasts
     */
    public function testGetConfigDefaultByKey()
    {
        $defaultConfig = ForecastsDefaults::getDefaults();
        $key = 'timeperiod_type';

        $this->assertEquals(
            $defaultConfig[$key],
            ForecastsDefaults::getConfigDefaultByKey($key),
            "The default value returned by ForecastsDefaults::getConfigDefaultByKey was not the same as in ForecastsDefaults::getDefaults"
        );
    }

    /**
     * Local function to iterate through a config array and save those settings using the adminBean
     *
     * @param $cfg {Array} an array of key => value pairs of config values for the config table
     * @param $adminBean {SugarBean} the Administration bean from BeanFactory
     */
    protected function saveConfig($cfg, $adminBean)
    {
        foreach ($cfg as $name => $value) {
            $adminBean->saveSetting('Forecasts', $name, $value, 'base');
        }
    }

    /**
     * This tests checks to ensure that the base_rate and currency_id values for the opportunities table are correctly
     * set after running the ForecastsDefaults::upgradeColumns() function
     *
     * @group forecasts
     */
    public function testOpportunitySaves()
    {
        $db = DBManagerFactory::getInstance();
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting('Forecasts', 'is_setup', '1', 'base');
        $admin->saveSetting('Forecasts', 'sales_stage_won', '[]', 'base');
        $admin->saveSetting('Forecasts', 'timeperiod_leaf_interval', TimePeriod::QUARTER_TYPE, 'base');
        $currency = SugarTestCurrencyUtilities::createCurrency('Yen', '¥', 'YEN', 78.87);

        // base_rate should get calculated from usdollar field
        $opp1 = SugarTestOpportunityUtilities::createOpportunity();
        $opp1->currency_id = null;
        $opp1->amount = 1000;
        $opp1->base_rate = null;
        $opp1->amount_usdollar = 2000;
        $opp1->save();

        // force values to be null in db as in a possible upgrade situation.
        $db->query("update opportunities set currency_id=NULL, base_rate=NULL where id='{$opp1->id}'");

        // upgrade currency columns
        ForecastsDefaults::upgradeColumns();

        // see if upgrade took effect
        $base_rate = $db->getOne("select base_rate from opportunities where id='{$opp1->id}'");
        $this->assertEquals(2.0, $base_rate, '', 2);
        $currencyId = $db->getOne("select currency_id from opportunities where id='{$opp1->id}'");
        $this->assertEquals('-99', $currencyId, '', 2);

        // base_rate should get calculated from usdollar field, even with currency_id set
        $opp2 = SugarTestOpportunityUtilities::createOpportunity();
        $opp2->currency_id = $currency->id;
        $opp2->amount = 1000;
        $opp2->base_rate = null;
        $opp2->amount_usdollar = 2000;
        $opp2->save();

        // force values to be null in db as in a possible upgrade situation.
        $db->query("update opportunities set base_rate=NULL, amount=1000, amount_usdollar=2000 where id='{$opp2->id}'");

        // upgrade currency columns
        ForecastsDefaults::upgradeColumns();

        // see if upgrade took effect
        $base_rate = $db->getOne("select base_rate from opportunities where id='{$opp2->id}'");
        $this->assertEquals(2.0, $base_rate, '', 2);

        // base_rate should get set from currency conversion_rate
        $opp3 = SugarTestOpportunityUtilities::createOpportunity();
        $opp3->currency_id = $currency->id;
        $opp3->amount = 1000;
        $opp3->base_rate = null;
        $opp3->amount_usdollar = null;
        $opp3->save();

        // force values to be null in db as in a possible upgrade situation.
        $db->query("update opportunities set base_rate=NULL, amount_usdollar=NULL where id='{$opp3->id}'");

        // upgrade currency columns
        ForecastsDefaults::upgradeColumns();

        // see if upgrade took effect
        $base_rate = $db->getOne("select base_rate from opportunities where id='{$opp3->id}'");
        $this->assertEquals(78.87, $base_rate, '', 2);

        // base_rate should get set to 1.0 for null values
        $opp4 = SugarTestOpportunityUtilities::createOpportunity();
        $opp4->currency_id = null;
        $opp4->amount = 1000;
        $opp4->base_rate = null;
        $opp4->amount_usdollar = null;
        $opp4->save();

        // force values to be null in db as in a possible upgrade situation.
        $db->query(
            "update opportunities set currency_id=NULL, base_rate=NULL, amount_usdollar=NULL where id='{$opp4->id}'"
        );

        // upgrade currency columns
        ForecastsDefaults::upgradeColumns();

        // see if upgrade took effect
        $base_rate = $db->getOne("select base_rate from opportunities where id='{$opp4->id}'");
        $this->assertEquals(1.0, $base_rate, '', 2);

        // base_rate should get calculated from usdollar field, even with currency_id set
        $opp5 = SugarTestOpportunityUtilities::createOpportunity();
        $opp5->currency_id = $currency->id;
        $opp5->amount = 0;
        $opp5->base_rate = null;
        $opp5->amount_usdollar = 2000;
        $opp5->save();

        // force values to be null in db as in a possible upgrade situation.
        $db->query("update opportunities set base_rate=NULL, amount=0 where id='{$opp5->id}'");

        // upgrade currency columns
        ForecastsDefaults::upgradeColumns();

        // see if upgrade took effect
        $base_rate = $db->getOne("select base_rate from opportunities where id='{$opp5->id}'");
        $this->assertEquals(78.87, $base_rate, '', 2);
    }
}
