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

class RestBug56791Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

    }

    public function tearDown()
    {
        parent::tearDown(); // This handles cleanup of created kbdocs
    }

    public function testEmptyQueryReturn()
    {
        $api = new RestService();
        $args = array('q' => '', 'moduleList' => 'Accounts');
        $options = array();

        $usm = new UnifiedSearchApiMock();
        $result = $usm->determineSugarSearchEngine($api, $args, $options);
        $this->assertEquals('SugarSearchEngine', $result, 'Did not equal SugarSearchEngine, instead was: ' . $result);
    }
}

class UnifiedSearchApiMock extends UnifiedSearchApi {
    function determineSugarSearchEngine(ServiceBase $api, array $args, array $options) {
        return parent::determineSugarSearchEngine($api, $args, $options);
    }
}