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

require_once "tests/{old}/rest/RestTestBase.php";
require_once "tests/{old}/modules/OutboundEmailConfiguration/OutboundEmailConfigurationTestHelper.php";

/**
 * @group email
 * @group outboundemailconfiguration
 */
class OutboundEmailConfigurationApiTest extends RestTestBase
{
    public function setUp()
    {
        parent::setUp();
        OutboundEmailConfigurationTestHelper::setUp();
    }

    public function tearDown()
    {
        OutboundEmailConfigurationTestHelper::tearDown();
        parent::tearDown();
    }

    public function testList_ReturnsAllConfigurationsWithTheSystemAsDefault()
    {
        $this->markTestIncomplete('Migrate this to SOAP UI');
        $seedConfigs = OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfigurations(2);

        $response = $this->_restCall("/OutboundEmailConfiguration/list");
        $reply    = $response["reply"];

        $expected = count($seedConfigs);

        $oe  = new OutboundEmail();
        if ($oe->isAllowUserAccessToSystemDefaultOutbound()) {
            $expected++; // system config is included if snd only if Access Allowed designated by Administrator
        }

        $actual   = count($reply);
        self::assertEquals($expected, $actual, "There should be {$expected} configurations");

        foreach ($reply as $configuration) {
            $expected = false;

            if ($configuration["type"] == "system") {
                $expected = true;
            }

            $actual = $configuration["default"];
            self::assertEquals(
                $expected,
                $actual,
                "The configuration with id={$configuration["id"]} should have default=" . ($expected ? "true" : "false")
            );
        }
    }
}
