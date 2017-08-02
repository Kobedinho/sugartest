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

use Sugarcrm\Sugarcrm\ProcessManager;

class PMSEElementValidatorTest extends PHPUnit_Framework_TestCase
{

    /**
     * Sets up the test data, for example, 
     *     opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        
    }

    /**
     * Removes the initial test configurations for each test, for example:
     *     close a network connection. 
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    public function testValidateRequestForStartEvent()
    {
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
                ->disableOriginalConstructor()
                ->setMethods(array(
                    'processExternalAction',
                    'processCreateThread',
                    'validateStartEvent',
                    'validateIntermediateEvent')
                )
                ->getMock();

        $loggerMock = $this->getMockBuilder('PMSELogger')
                ->disableOriginalConstructor()
                ->setMethods(array('debug', 'info'))
                ->getMock();

        $requestMock = $this->getMockBuilder('PMSERequest')
                ->disableOriginalConstructor()
                ->setMethods(array('getFlowData', 'getBean', 'setExternalAction', 'setCreateThread'))
                ->getMock();

        $requestMock->expects($this->once())
                ->method('getFlowData')
                ->will($this->returnValue(array('evn_type' => 'START')));

        $elementValidatorMock->expects($this->once())
                ->method('validateStartEvent');

        $elementValidatorMock->setLogger($loggerMock);
        $elementValidatorMock->validateRequest($requestMock);
    }

    public function testValidateRequestForIntermediateEvent()
    {
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
                ->disableOriginalConstructor()
                ->setMethods(array(
                    'processExternalAction',
                    'processCreateThread',
                    'validateStartEvent',
                    'validateIntermediateEvent')
                )
                ->getMock();

        $loggerMock = $this->getMockBuilder('PMSELogger')
                ->disableOriginalConstructor()
                ->setMethods(array('debug', 'info'))
                ->getMock();

        $requestMock = $this->getMockBuilder('PMSERequest')
                ->disableOriginalConstructor()
                ->setMethods(array('getFlowData', 'getBean', 'setExternalAction', 'setCreateThread'))
                ->getMock();

        $requestMock->expects($this->once())
                ->method('getFlowData')
                ->will($this->returnValue(array('evn_type' => 'INTERMEDIATE')));

        $elementValidatorMock->expects($this->once())
                ->method('validateIntermediateEvent');

        $elementValidatorMock->setLogger($loggerMock);
        $elementValidatorMock->validateRequest($requestMock);
    }
    
    public function testValidateRequestNoValidElement()
    {
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
                ->disableOriginalConstructor()
                ->setMethods(array(
                    'processExternalAction',
                    'processCreateThread',
                    'validateStartEvent',
                    'validateIntermediateEvent')
                )
                ->getMock();

        $loggerMock = $this->getMockBuilder('PMSELogger')
                ->disableOriginalConstructor()
                ->setMethods(array('debug', 'info'))
                ->getMock();

        $requestMock = $this->getMockBuilder('PMSERequest')
                ->disableOriginalConstructor()
                ->setMethods(array('getFlowData', 'getBean', 'setExternalAction', 'setCreateThread'))
                ->getMock();

        $requestMock->expects($this->once())
                ->method('getFlowData')
                ->will($this->returnValue(array('evn_type' => 'NO_VALID')));

        $elementValidatorMock->setLogger($loggerMock);
        $result = $elementValidatorMock->validateRequest($requestMock);
        $this->assertEquals($requestMock, $result);
    }

    public function testIdentifyElementStatusIfRunning()
    {
        $mockData = array('cas_id' => 1, 'cas_index' => 2);
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();

        $result = $elementValidatorMock->identifyElementStatus($mockData);
        $this->assertEquals('RUNNING', $result);
    }

    public function testIdentifyElementStatusIfNew()
    {
        $mockData = array();
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();

        $result = $elementValidatorMock->identifyElementStatus($mockData);
        $this->assertEquals('NEW', $result);
    }

    public function testIdentifyEventActionIfNotRelated()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';
        $mockData = array('rel_process_module' => 'Leads',
            'rel_element_relationship' => 'Leads',
            'rel_element_module' => 'Leads',
        );
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();
        
        $result = $elementValidatorMock->identifyEventAction($mockData);
        
        $this->assertEquals('EVALUATE_MAIN_MODULE', $result);
    }
    
    public function testIdentifyEventActionIfRelated()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Notes';
        $mockData = array('rel_process_module' => 'Leads',
            'rel_element_relationship' => 'leads_notes',
            'rel_element_module' => 'Notes',
        );
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();
        
        $result = $elementValidatorMock->identifyEventAction($mockData);
        
        $this->assertEquals('EVALUATE_RELATED_MODULE', $result);
    }

    public function testProcessExternalActionIfRunning()
    {
        $bean = new stdClass();
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
                ->disableOriginalConstructor()
                ->setMethods(array('identifyElementStatus', 'identifyEventAction'))
                ->getMock();

        $elementValidatorMock->expects($this->once())
                ->method('identifyElementStatus')
                ->will($this->returnValue('RUNNING'));

        $mockData = array('evn_type' => 'INTERMEDIATE');
        
        $elementValidatorMock->expects($this->once())
                ->method('identifyEventAction');
        
        $elementValidatorMock->processExternalAction($mockData);

    }
    
    public function testProcessExternalActionIfNotRunning()
    {
        $bean = new stdClass();
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
                ->disableOriginalConstructor()
                ->setMethods(array('identifyElementStatus', 'identifyEventAction'))
                ->getMock();

        $elementValidatorMock->expects($this->any())
                ->method('identifyElementStatus')
                ->will($this->returnValue(false));

        $mockData = array('evn_type' => 'START');
        $result = $elementValidatorMock->processExternalAction($mockData);
        $this->assertEquals(false, $result);
    }
    
    public function testProcessCreateThreadIfNew()
    {
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
                ->disableOriginalConstructor()
                ->setMethods(array('identifyElementStatus'))
                ->getMock();
        
        $elementValidatorMock->expects($this->once())
                ->method('identifyElementStatus')
                ->will($this->returnValue('NEW'));

        $flowData = array();
        $result = $elementValidatorMock->processCreateThread($flowData);
        $this->assertEquals(true, $result);
    }
    
    public function testProcessCreateThreadIfNotNew()
    {
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
                ->disableOriginalConstructor()
                ->setMethods(array('identifyElementStatus'))
                ->getMock();
        
        $elementValidatorMock->expects($this->once())
                ->method('identifyElementStatus')
                ->will($this->returnValue('INTERMEDIATE'));

        $flowData = array();
        $result = $elementValidatorMock->processCreateThread($flowData);
        $this->assertEquals(false, $result);
    }
    
    public function testIsCaseDuplicated()
    {
        $beanMock = new stdClass();
        $beanMock->id = 'bean123';
        $beanMock->module_name = 'Leads';
        $flowData = array('pro_id' => 'pro123');
        
        $elementValidator = $this->getMockBuilder('PMSEElementValidator')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();

        $beanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        
        $sugarQueryObjectMock = $this->getMockBuilder('SugarQuery')
                ->disableOriginalConstructor()
                ->setMethods(array('from', 'distinct', 'where', 'equals', 'query', 'execute'))
                ->getMock();

        $sugarQueryObjectMock->expects($this->atLeastOnce())
            ->method('from')
            ->will($this->returnValue($sugarQueryObjectMock));

        $sugarQueryObjectMock->expects($this->atLeastOnce())
            ->method('where')
            ->will($this->returnValue($sugarQueryObjectMock));

        $sugarQueryObjectMock->expects($this->any())
            ->method('equals')
            ->will($this->returnValue($sugarQueryObjectMock));

        $arrayDupli = array('id' => '999000');

        $sugarQueryObjectMock->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($arrayDupli));

        $loggerMock = $this->getMockBuilder('PMSELogger')
                ->disableOriginalConstructor()
                ->setMethods(array('debug'))
                ->getMock();

        $elementValidator->setBeanFlow($beanMock);
        $elementValidator->setSugarQueryObject($sugarQueryObjectMock);
        $elementValidator->setLogger($loggerMock);
        
        $result = $elementValidator->isCaseDuplicated($beanMock, $flowData);
        $this->assertEquals(TRUE, $result);
        
    }
    
    public function testIsCaseDuplicatedNot()
    {
        $beanMock = new stdClass();
        $beanMock->id = 'bean123';
        $beanMock->module_name = 'Leads';
        $flowData = array('pro_id' => 'pro123');
        
        $elementValidator = $this->getMockBuilder('PMSEElementValidator')
                ->disableOriginalConstructor()
                ->setMethods(NULL)
                ->getMock();

        $beanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();

        $sugarQueryObjectMock = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('from', 'distinct', 'where', 'equals', 'query', 'execute'))
            ->getMock();

        $sugarQueryObjectMock->expects($this->atLeastOnce())
            ->method('from')
            ->will($this->returnValue($sugarQueryObjectMock));

        $sugarQueryObjectMock->expects($this->atLeastOnce())
            ->method('where')
            ->will($this->returnValue($sugarQueryObjectMock));

        $sugarQueryObjectMock->expects($this->any())
            ->method('equals')
            ->will($this->returnValue($sugarQueryObjectMock));

        $arrayDupli = false;

        $sugarQueryObjectMock->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($arrayDupli));
        
        $loggerMock = $this->getMockBuilder('PMSELogger')
                ->disableOriginalConstructor()
                ->setMethods(array('debug'))
                ->getMock();

        $elementValidator->setBeanFlow($beanMock);
        $elementValidator->setSugarQueryObject($sugarQueryObjectMock);
        $elementValidator->setLogger($loggerMock);
        
        $result = $elementValidator->isCaseDuplicated($beanMock, $flowData);
        $this->assertEquals(false, $result);
    }

    public function testValidateStartEventNewRecord()
    {
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
                ->disableOriginalConstructor()
                ->setMethods(array('isNewRecord', 'isCaseDuplicated'))
                ->getMock();
        
        $elementValidatorMock->expects($this->once())
                ->method('isNewRecord')
                ->will($this->returnValue(true));
        
        $beanMock = new stdClass();
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        
        $flowDataMock = array('evn_params'=>'new');
        $elementValidatorMock->validateStartEvent($beanMock, $flowDataMock, $request);
        $this->assertEquals(true, $request->isValid());
    }
    
    public function testValidateStartEventUpdatedRecord()
    {
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
                ->disableOriginalConstructor()
                ->setMethods(array('isNewRecord', 'isCaseDuplicated'))
                ->getMock();
        
        $elementValidatorMock->expects($this->atLeastOnce())
                ->method('isNewRecord')
                ->will($this->returnValue(false));
        
        $elementValidatorMock->expects($this->once())
                ->method('isCaseDuplicated')
                ->will($this->returnValue(false));
        
        $beanMock = new stdClass();
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        
        $flowDataMock = array('evn_params'=>'updated');
        $elementValidatorMock->validateStartEvent($beanMock, $flowDataMock, $request);
        $this->assertEquals(true, $request->isValid());
    }
    
    public function testValidateStartEventFailed()
    {
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
                ->disableOriginalConstructor()
                ->setMethods(array('isNewRecord', 'isCaseDuplicated'))
                ->getMock();

        $elementValidatorMock->expects($this->atLeastOnce())
                ->method('isNewRecord')
                ->will($this->returnValue(false));

        $elementValidatorMock->expects($this->once())
                ->method('isCaseDuplicated')
                ->will($this->returnValue(true));

        $beanMock = new stdClass();
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');

        $flowDataMock = array('evn_params'=>'updated');
        $elementValidatorMock->validateStartEvent($beanMock, $flowDataMock, $request);
        $this->assertEquals(false, $request->isValid());
    }

}
