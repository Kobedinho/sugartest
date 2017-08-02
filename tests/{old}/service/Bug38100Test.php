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
require_once('vendor/nusoap//nusoap.php');
require_once 'tests/{old}/service/SOAPTestCase.php';

/**
 * @ticket 38100
 */
class Bug38100Test extends SOAPTestCase
{
    public $_contactId = '';

    /**
     * Create test user
     *
     */
	public function setUp()
    {
    	$this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v2_1/soap.php';

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('Reports'));
		parent::setUp();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testGetReportEntries()
    {
    	require_once('service/core/SoapHelperWebService.php');
    	require_once('modules/Reports/SavedReport.php');
    	//$savedReportId = $GLOBALS['db']->getOne("SELECT id FROM saved_reports WHERE deleted=0");

        $results = $GLOBALS['db']->query("SELECT id FROM saved_reports WHERE deleted=0 AND content IS NOT NULL");
        while(($row = $GLOBALS['db']->fetchByAssoc($results)) != null)
        {
            $savedReportId = $row['id'];
            $savedReport = new SavedReport();
            $savedReport->retrieve($savedReportId);
            $helperObject = new SoapHelperWebServices();
            $helperResult = $helperObject->get_report_value($savedReport, array());
            $this->_login();
            $result = $this->_soapClient->call('get_report_entries',array('session'=>$this->_sessionId,'ids' => array($savedReportId),'select_fields' => array()));

            $this->assertTrue(!empty($result['field_list']), "Bad result: ".var_export($result, true));
            $this->assertTrue(!empty($result['entry_list']), "Bad result: ".var_export($result, true));
        }
    } // fn
}
