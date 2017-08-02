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
 *  RS-44: Prepare RegisterLead Api.
 */
class RS44Test extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function testCreateLead()
    {
        $api = new RegisterLeadApi();
        $rest = SugarTestRestUtilities::getRestServiceMock();

        $result = $api->createLeadRecord($rest, array('last_name' => 'RS44Test'));
        $this->assertNotEmpty($result);

        $bean = BeanFactory::getBean('Leads', $result);
        $this->assertEquals('RS44Test', $bean->last_name);
    }
}
