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


class ActivitiesApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function dataProviderForGetDisplayModule()
    {
        $emptyAccount = BeanFactory::newBean('Accounts');
        $emptyLead = BeanFactory::newBean('Leads');
        return array(
            array('post', null, 'Accounts', '123'),
            array('post', $emptyAccount, 'Accounts', '123'),
            array('post', $emptyLead, 'Accounts', '123'),
            array('link', null, 'Accounts', '123'),
            array('link', $emptyAccount, 'Leads', '456'),
            array('link', $emptyLead, 'Accounts', '123'),
            array('unlink', null, 'Accounts', '123'),
            array('unlink', $emptyAccount, 'Leads', '456'),
            array('unlink', $emptyLead, 'Accounts', '123'),
        );
    }

    /**
     * @covers ActivitiesApiHelper::getDisplayModule
     * @dataProvider dataProviderForGetDisplayModule
     */
    public function testGetDisplayModule($activity_type, $contextBean, $expected_module, $expected_id)
    {
        $record = array(
            'parent_type' => 'Accounts',
            'parent_id' => '123',
            'activity_type' => $activity_type,
            'data' => array(
                'subject' => array(
                    'module' => 'Leads',
                    'id' => '456',
                ),
            ),
        );

        $helper = new ActivitiesApiHelper(new ActivitiesServiceMockup());
        $result = SugarTestReflection::callProtectedMethod($helper, 'getDisplayModule', array($record, $contextBean));

        $this->assertEquals($expected_module, $result['module']);
        $this->assertEquals($expected_id, $result['id']);
    }
}

class ActivitiesServiceMockup extends ServiceBase
{
    public function execute()
    {

    }
    protected function handleException(Exception $exception)
    {

    }
}

