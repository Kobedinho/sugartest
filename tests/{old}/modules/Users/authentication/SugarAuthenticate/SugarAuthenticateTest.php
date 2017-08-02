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


class SugarAuthenticateTest extends Sugar_PHPUnit_Framework_TestCase {

    /**
     * @var SugarAuthenticate
     */
    protected $authenticate;

    protected function setUp()
    {
        $this->authenticate = $this->getMockBuilder('SugarAuthenticate')
            ->setMethods(array('updateUserLastLogin', 'postLoginAuthenticate'))
            ->disableOriginalConstructor()
            ->getMock();

        $authUser = $this->createMock('SugarAuthenticateUser');
        $authUser->expects($this->once())
            ->method('loadUserOnLogin')
            ->will($this->returnValue(true));

        $this->authenticate->userAuthenticate = $authUser;
    }

    /**
     * @coversNothing
     * @group BR-1721
     */
    public function testLoginAuthenticateTriggersUpdateUserLastLogin()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $this->authenticate->expects($this->once())
            ->method('updateUserLastLogin');
        $this->authenticate->loginAuthenticate($user->user_name, '');
    }

    protected function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
}
