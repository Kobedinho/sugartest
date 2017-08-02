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


class Bug42727Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_opportunity;
    protected $_opportunityIds = array();

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->_opportunity = $this->getMockBuilder('Opportunity')
            ->setMethods(array('send_assignment_notifications'))
            ->getMock();
        $this->_opportunity->mailWasSent = false;
        $this->_opportunity->notify_inworkflow = true;
        $this->_opportunity->set_created_by = false;
        $this->_opportunity->date_closed = TimeDate::getInstance()->getNow()->asDbDate();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $GLOBALS['db']->query('DELETE FROM opportunities WHERE id IN (\'' . implode("', '", $this->_opportunityIds) . '\')');
        SugarTestHelper::tearDown();
    }

    
    public function testSentMail() 
    {
        $this->_opportunity->created_by = $this->_opportunity->assigned_user_id = SugarTestUserUtilities::createAnonymousUser()->id;
        $this->_opportunity->expects($this->never())
            ->method('send_assignment_notifications');
        $this->_opportunityIds[] = $this->_opportunity->save();
        $this->assertTrue($this->_opportunity->isOwner($this->_opportunity->created_by));
    }
    
    public function testNotSentMail() 
    {
        $this->_opportunity->created_by = SugarTestUserUtilities::createAnonymousUser()->id;
        $this->_opportunity->assigned_user_id = SugarTestUserUtilities::createAnonymousUser()->id;
        $this->_opportunity->expects($this->once())
            ->method('send_assignment_notifications');
        $this->_opportunityIds[] = $this->_opportunity->save(true);
        $this->assertFalse($this->_opportunity->isOwner($this->_opportunity->created_by));
    }
}
