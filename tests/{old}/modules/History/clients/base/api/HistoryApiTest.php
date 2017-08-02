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
 * @group ApiTests
 */
class HistoryApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var HistoryApi */
    protected $filterApi = null;

    /** @var RestService */
    protected $serviceMock = null;

    /** @var Account */
    protected $account = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->filterApi = new HistoryApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
        $this->account = SugarTestAccountUtilities::createAccount();
    }

    public function tearDown()
    {
        unset($this->filterApi);
        unset($this->serviceMock);
        unset($this->account);

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    public function testFilterSetup()
    {
        $return = $this->filterApi->filterModuleList(
            $this->serviceMock,
            array(
                'module' => 'Accounts',
                'record' => $this->account->id,
                'module_list' => 'Calls,Emails,Meetings,Notes,Tasks',
                'max_num' => 20,
            ),
            'list'
        );
        $this->assertNotEmpty($return, 'HistoryAPI is broken');
    }
}
