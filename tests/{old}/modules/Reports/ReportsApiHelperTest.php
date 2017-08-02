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



class ReportsApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{

    protected $bean =null;

    protected $helper;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');

        // ACL's are junked need to have an admin user
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['current_user']->save();

        $this->bean = BeanFactory::newBean('Reports');
        $this->bean->fetched_row['report_type'] = 'Matrix';
        $this->bean->report_type = 'summary';
        $this->bean->id = create_guid();
        $this->bean->name = 'Super Awesome Report Time';
        $this->helper = new ReportsApiHelper(new ReportsServiceMockup());
    }

    public function tearDown()
    {
        unset($this->bean);
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testFormatForApi() 
    {
        $data = $this->helper->formatForApi($this->bean);
        $this->assertEquals($data['report_type'], $this->bean->fetched_row['report_type'], "Report Type Does not match");
    }

    public function testSanitizeSubmittedData()
    {
        $submittedData = array(
            'module' => 'Reports',
            'name' => 'Report Name',
        );

        $sanitized = $this->helper->sanitizeSubmittedData($submittedData);

        $this->assertArrayNotHasKey('module', $sanitized);
    }
}

class ReportsServiceMockup extends ServiceBase
{
    public function __construct() {$this->user = $GLOBALS['current_user'];}
    public function execute() {}
    protected function handleException(Exception $exception) {}
}
