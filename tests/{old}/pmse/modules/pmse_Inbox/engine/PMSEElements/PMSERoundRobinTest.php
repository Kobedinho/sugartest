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
class PMSERoundRobinTest extends PHPUnit_Framework_TestCase {

    /**
     * @var PMSEElement
     */
    protected $roundRobin;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     *
     */
    public function testRun()
    {
        $this->roundRobin = $this->getMockBuilder('PMSERoundRobin')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'retrieveDefinitionData',
                    'retrieveTeamData',
                    'retrieveHistoryData',
                    'prepareResponse'
                )
            )
            ->getMock();

        $definitionMock = array(
            'id' => '12s1829s739sj8912123',
            'pro_id' => '8937298492jd823',
            'act_assign_team' => 'mi192sk09219s2',
            'act_assignment_method' => 'BALANCED',
            'act_update_record_owner' => 1
        );

        $this->roundRobin->expects($this->exactly(1))
            ->method('retrieveDefinitionData')
            ->will($this->returnValue($definitionMock));

        $teamMock = $this->getMockBuilder('Users')
            ->getMock();
        $teamMock->id = 'mi192sk09219s2';

        $currentUser = new stdClass();
        $currentUser->id = '82jes9823jd8932';

        $this->roundRobin->expects($this->exactly(1))
            ->method('retrieveTeamData')
            ->will($this->returnValue($teamMock));

        $historyMock = $this->getMockBuilder('PMSEHistoryData')
            ->setMethods(array('savePreData', 'savePostData', 'getLog'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->roundRobin->expects($this->exactly(1))
            ->method('retrieveHistoryData')
            ->will($this->returnValue($historyMock));

        $beanMock = $this->getMockBuilder('SugarBean')
            ->setMethods(array('load_relationship', 'save'))
            ->getMock();

        $dbMock = $this->getMockBuilder('DBHandler')
            ->setMethods(array('Query', 'fetchByAssoc'))
            ->getMock();

        $beanMock->db = $dbMock;
        $beanMock->team_id = '932ei0923dk0239ike023';
        $beanMock->team_set_id = 'ijdaoisdjaio892de';
        $beanMock->assigned_user_id = '278uw8912uw1';
        $beanMock->field_defs = array(
            'team_id' => '932ei0923dk0239ike023'
        );


        $flowData = array(
            'bpmn_id' => 'act89u298dj2893j',
            'cas_sugar_module' => 'Leads',
            'cas_user_id' => 'sijd8923j98d2',
            'cas_id' => 1,
            'cas_index' => 2

        );

        $caseHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('saveFormAction'))
            ->getMock();

        $userHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('getNextUserUsingRoundRobin'))
            ->getMock();

        $caseHandlerMock->expects($this->exactly(1))
            ->method('saveFormAction');

        $this->roundRobin->expects($this->exactly(1))
            ->method('prepareResponse');

        $this->roundRobin->setCurrentUser($currentUser);
        $this->roundRobin->setCaseFlowHandler($caseHandlerMock);
        $this->roundRobin->setUserAssignmentHandler($userHandlerMock);
        $this->roundRobin->run($flowData, $beanMock, '');
    }
}
