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


class UsersApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $helper;
    protected $bean = null;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');

        $this->bean = BeanFactory::newBean('Users');
        $this->bean->id = create_guid();

        $this->helper = $this->getMockBuilder('UsersApiHelper')
            ->setMethods(['checkUserAccess'])
            ->setConstructorArgs([new UsersServiceMockup()])
            ->getMock();
    }

    public function tearDown()
    {
        unset($this->bean);
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testFormatForApi_HasAccessArgumentsPassed_ReturnsHasAccessResult()
    {
        $options = array(
            'args' => array(
                'has_access_module' => 'Foo',
                'has_access_record' => '123'
            ),
        );

        $this->helper->expects($this->once())
            ->method('checkUserAccess')
            ->will($this->returnValue(true));

        $data = $this->helper->formatForApi($this->bean, array(), $options);
        $this->assertEquals($data['has_access'], true, "Has Access should be true");
    }

    public function testFormatForApi_NoHasAccessArgumentsPassed_DoesNotReturnHasAccessResult()
    {
        $options = array(
            'args' => array(),
        );

        $this->helper->expects($this->never())
            ->method('checkUserAccess');

        $data = $this->helper->formatForApi($this->bean, array(), $options);
        $this->assertEquals(array_key_exists('has_access', $data), false, "Has Access data should not exist");
    }

    public function testPopulateFromApi_newBean()
    {
        $user = BeanFactory::newBean('Users');
        $user->new_with_id = true;
        $user->id = '';

        $this->setExpectedException('SugarApiExceptionMissingParameter');

        $this->helper->populateFromApi($user, array(), array());
    }

    public function testPopulateFromApi_updateBean()
    {
        $test = $this->helper->populateFromApi($GLOBALS['current_user'], array(), array());
        $this->assertTrue($test);
    }
}

class UsersServiceMockup extends ServiceBase
{
    public function __construct() {$this->user = $GLOBALS['current_user'];}
    public function execute() {}
    protected function handleException(Exception $exception) {}
}
