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


class PMSEElementExceptionTest extends PHPUnit_Framework_TestCase 
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
    
    public function testToString()
    {
        $message = 'Exception message';
        $flowData = array('sample_flow_data');
        $element = new stdClass();
        $code = 1;
        $exception = new PMSEElementException($message, $flowData, $element, $code);
        $result = $exception->__toString();
        $this->assertEquals("PMSEElementException: [1]: Exception message\n", $result);
        $this->assertEquals($element, $exception->getElement());
        $this->assertEquals(array(0 => 'sample_flow_data', 'cas_flow_status'=>'ERROR'), $exception->getFlowData());
    }
    //put your tests code here
}
