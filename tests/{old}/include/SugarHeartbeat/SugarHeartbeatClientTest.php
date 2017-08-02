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


/**
 * Class SugarHeartbeatClientTest
 *
 * @group BR-1722
 */
class SugarHeartbeatClientTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @covers SugarHeartbeatClient::sugarHome
     * @covers SugarHeartbeatClient::encode
     * @group unit
     */
    public function testSugarHome()
    {
        $result = array(
            'key' => '12345',
            'data' => base64_encode(serialize(array('foo' => 'bar'))),
        );
        $client = $this->getMockBuilder('SugarHeartbeatClient')
            ->disableOriginalConstructor()
            ->setMethods(array('call'))
            ->getMock();
        $client->expects($this->once())
            ->method('call')
            ->with($this->equalTo('sugarHome'), $this->equalTo($result));
        $client->sugarHome('12345', array('foo' => 'bar'));
    }

    /**
     * @covers SugarHeartbeatClient::sugarPing
     * @group unit
     */
    public function testSugarPing()
    {
        $client = $this->getMockBuilder('SugarHeartbeatClient')
            ->disableOriginalConstructor()
            ->setMethods(array('call'))
            ->getMock();
        $client->expects($this->once())
            ->method('call')
            ->with($this->equalTo('sugarPing'), array());
        $client->sugarPing();
    }
}
