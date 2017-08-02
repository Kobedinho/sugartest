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
 * RS-126
 * Prepare ForecastManagerWorksheetsExport Api
 * @coversDefaultClass ForecastManagerWorksheetsExportApi
 */
class ForecastManagerWorksheetsExportApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var RestService */
    protected $service = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));
        $fields = array(
            'reports_to_id' => $GLOBALS['current_user']->id,
        );
        $user = SugarTestUserUtilities::createAnonymousUser(true, 0, $fields);

        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test behavior of export method
     * @covers ::export
     */
    public function testExport()
    {
        $api = $this->createPartialMock('ForecastManagerWorksheetsExportApi', array('doExport'));
        $api->expects($this->once())
            ->method('doExport')
            ->with(
                $this->equalTo($this->service),
                $this->logicalNot($this->isEmpty()),
                $this->logicalNot($this->isEmpty())
            );
        $api->export($this->service, array());
    }
}
