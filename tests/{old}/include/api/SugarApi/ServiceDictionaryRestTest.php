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


class ServiceDictionaryRestTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testRegisterEndpoints() {
        $sd = new ServiceDictionaryRestMainMock();
        
        $sd->preRegisterEndpoints();
        $blank = $sd->getRegisteredEndpoints();
        
        $this->assertEquals(0,count($blank));
        
        $sd->preRegisterEndpoints();
        $fakeEndpoints = array(
            array('reqType'=>'GET',
                  'path'=>array('one','two','three'),
                  'pathVars'=>array('','',''),
                  'method'=>'unittest1',
                  'shortHelp'=>'short help',
                  'longHelp'=>'some/path.html',
            ),
            array('reqType'=>'GET',
                  'path'=>array('one','two'),
                  'pathVars'=>array('',''),
                  'method'=>'unittest2',
                  'shortHelp'=>'short help',
                  'longHelp'=>'some/path.html',
            ),
            array('reqType'=>'GET',
                  'path'=>array('one','two','three'),
                  'pathVars'=>array('','',''),
                  'method'=>'unittest3',
                  'shortHelp'=>'short help',
                  'longHelp'=>'some/path.html',
                  'extraScore'=>25.5,
            ),
            array('reqType'=>'GET',
                  'path'=>array('<module>','?','three'),
                  'pathVars'=>array('','',''),
                  'method'=>'unittest4',
                  'shortHelp'=>'short help',
                  'longHelp'=>'some/path.html',
            ),
        );
        $sd->registerEndpoints(array($fakeEndpoints[0]),'fake/unittest1.php','unittest1','base',0);
        
        $oneTest = $sd->getRegisteredEndpoints();

        $this->assertTrue(isset($oneTest['3']['base']['GET']['one']['two']['three'][0]['method']));

        $sd->preRegisterEndpoints();
        $sd->registerEndpoints($fakeEndpoints,'fake/unittest1.php','unittest1','base',0);
        
        $allTest = $sd->getRegisteredEndpoints();

        $this->assertTrue(isset($allTest['3']['base']['GET']['one']['two']['three'][0]['method']));
        $this->assertTrue(isset($allTest['2']['base']['GET']['one']['two'][0]['method']));


        $sd->preRegisterEndpoints();
        $sd->registerEndpoints($fakeEndpoints,'fake/unittest1.php','unittest1','base',0);
        $portalEndpoint = $fakeEndpoints[3];
        $portalEndpoint['method'] = 'portaltest4';
        $sd->registerEndpoints(array($portalEndpoint),'portal/unittest1.php','portaltest4','portal',0);

        $portalEndpoint = $fakeEndpoints[2];
        $portalEndpoint['method'] = 'portaltest3';
        $portalEndpoint['path'][2] = 'portal';
        $sd->registerEndpoints(array($portalEndpoint),'portal/unittest1.php','portaltest3','portal',0);
        
        $allTest = $sd->getRegisteredEndpoints();
        $sd->pullDictFromBuffer();

        $this->assertTrue(isset($allTest['3']['base']['GET']['one']['two']['three'][0]['method']));
        $this->assertTrue(isset($allTest['2']['base']['GET']['one']['two'][0]['method']));
        $this->assertEquals('portaltest4',$allTest['3']['portal']['GET']['<module>']['?']['three'][0]['method']);

        // Make sure we can find a normal route
        $route = $sd->lookupRoute(array('one','two','three'),5.0,'GET','base');
        $this->assertEquals('unittest3',$route['method']);
        
        // Make sure we find a base route if there isn't a platform specific route
        $route = $sd->lookupRoute(array('one','two','three'),5.0,'GET','portal');
        $this->assertEquals('unittest3',$route['method']);

        // Make sure we find a platform specific route
        $route = $sd->lookupRoute(array('one','two','portal'),5.0,'GET','portal');
        $this->assertEquals('portaltest3',$route['method']);

    }
}

class ServiceDictionaryRestMainMock extends ServiceDictionaryRest
{
    public function pullDictFromBuffer()
    {
        $this->dict = $this->endpointBuffer;
    }
}