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



/***
 * @covers ForecastManagerWorksheetsApi
 */
class ForecastManagerWorksheetsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @covers ForecastManagerWorksheetsApi::getBean
     */
    public function testGetBean()
    {
        $api = new ForecastManagerWorksheetsApi();

        $this->assertInstanceOf('Quota', SugarTestReflection::callProtectedMethod($api, 'getBean', array('Quotas')));
    }

    /**
     * @covers ForecastManagerWorksheet::assignQuota
     */
    public function testAssignQuota()
    {
        $api = $this->getMockBuilder('ForecastManagerWorksheetsApi')
            ->setMethods(array('getBean'))
            ->getMock();

        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods(array('save', 'assignQuota'))
            ->disableOriginalConstructor()
            ->getMock();

        $worksheet->expects($this->once())
            ->method('assignQuota')
            ->with('test-user-id', 'test-timeperiod-id')
            ->willReturn(true);

        $api->expects($this->once())
            ->method('getBean')
            ->with('ForecastManagerWorksheets')
            ->willReturn($worksheet);

        $args = array(
            'module' => 'ForecastManagerWorksheets',
            'user_id' => 'test-user-id',
            'timeperiod_id' => 'test-timeperiod-id'
        );

        $actual = $api->assignQuota(SugarTestRestUtilities::getRestServiceMock(), $args);

        $this->assertSame(array('success' => true), $actual);
    }

}
