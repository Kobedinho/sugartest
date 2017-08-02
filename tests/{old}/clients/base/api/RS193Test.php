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


class RS193Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    public function testAddTrackerFilter()
    {
        $api = new FilterApi();
        $actual = $api->filterList(SugarTestRestUtilities::getRestServiceMock(), array(
                '__sugar_url' => 'v10/Accounts/filter',
                'filter' => array(
                    array(
                        '$tracker' => '-7 DAY'
                    ),
                ),
                'fields' => 'id,name',
                'max_num' => 3,
                'module' => 'Accounts',
            ));
        $this->assertArrayHasKey('records', $actual);
    }
}
