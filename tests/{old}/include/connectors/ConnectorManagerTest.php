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
 * ConnectorsManagerTest
 *
 */


require_once 'include/connectors/utils/ConnectorUtils.php';
require_once 'modules/EAPM/EAPM.php';


class SourceTest
{
    private $id;

    public function __construct($param)
    {
        $this->id = $param;
        $this->name = $param;
    }

    public function hasTestingEnabled()
    {
        if ($this->id == 'ValidTestingDisabledAuth') {
            return false;
        }
        return true;
    }

    public function test()
    {
        if ($this->id == 'ValidTestingEnabledAuth' || $this->id == 'ValidTestingEnabledUnAuth') {
            return true;
        }
        if ($this->id == 'ThrowsErrors') {
            throw new Exception('this connector has problems');
        }
        return false;
    }

    public function getMapping()
    {
        return array(
            'testfield1' => 'value',
            'testfield2' => 'value'
        );
    }
}


class ConnectorManagerTest extends ConnectorManager
{
    public function getConnectorList() {
        return array(
            'ValidTestingDisabledAuth' =>
                array('id' => 'ValidTestingDisabledAuth'),
            'ValidTestingEnabledAuth' =>
                array('id' => 'ValidTestingEnabledAuth'),
            'ValidTestingEnabledUnAuth' =>
                array('id' => 'ValidTestingEnabledUnAuth'),
            'InvalidTestFails' =>
                array('id' => 'InvalidTestFails'),
            'InvalidNoSource' =>
                array('id' => 'InvalidNoSource'),
            'ThrowsErrors' =>
                array('id' => 'ThrowsErrors'),
        );
    }
    public function getEAPMForConnector($connector)
    {
        if ($connector['id'] == 'ValidTestingDisabledAuth' || $connector['id'] == 'ValidTestingEnabledAuth') {
            $toRet = new stdClass();
            $toRet->id = 1;
            return $toRet;
        } else {
            return null;
        }
    }

    public function getSourceForConnector($connector)
    {
        if ($connector['id'] == 'InvalidNoSource') {
            return null;
        } else {
            return new SourceTest($connector['id']);
        }
    }
}

class ConnectorsValidTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        $cacheFile = sugar_cached('api/metadata/connectors.php');
        if (file_exists($cacheFile)) {
            // delete the current file because it has trash data in it
            unlink($cacheFile);
        }
    }

    /*
     * test get connectors and initial cache
     */
    public function testGetConnectors()
    {
        $expectedOut = array(
            'ValidTestingDisabledAuth' =>
                array(
                    'id' => 'ValidTestingDisabledAuth',
                    'testing_enabled' => false,
                    'test_passed' => false,
                    'eapm_bean' => false,
                    'field_mapping' => array()
                ),
            'ValidTestingEnabledAuth' =>
                array(
                    'id' => 'ValidTestingEnabledAuth',
                    'testing_enabled' => true,
                    'test_passed' => true,
                    'eapm_bean' => false,
                    'field_mapping' => array()
                ),
            'ValidTestingEnabledUnAuth' =>
                array(
                    'id' => 'ValidTestingEnabledUnAuth',
                    'testing_enabled' => true,
                    'test_passed' => true,
                    'eapm_bean' => false,
                    'field_mapping' => array()
                ),
            'InvalidTestFails' =>
                array(
                    'id' => 'InvalidTestFails',
                    'testing_enabled' => true,
                    'test_passed' => false,
                    'eapm_bean' => false,
                    'field_mapping' => array()
                ),
            'InvalidNoSource' =>
                array(
                    'id' => 'InvalidNoSource',
                    'testing_enabled' => false,
                    'test_passed' => false,
                    'eapm_bean' => false,
                    'field_mapping' => array()
                ),
            'ThrowsErrors' =>
                array(
                    'id' => 'ThrowsErrors',
                    'testing_enabled' => true,
                    'test_passed' => false,
                    'eapm_bean' => false,
                    'field_mapping' => array()
                ),
        );

        $connectorManager = new ConnectorManagerTest();
        $connectors = $connectorManager->buildConnectorsMeta();

        // should get valid connectors with a hash
        $this->assertTrue(!empty($connectors['_hash']));
        $currentHash = $connectors['_hash'];
        unset($connectors['_hash']);
        $this->assertEquals($connectors, $expectedOut);

        // should create cache file
        // Handle the cache file
        $cacheFile = sugar_cached('api/metadata/connectors.php');
        if (file_exists($cacheFile)) {
            require $cacheFile;
        }
        $this->assertEquals($currentHash, $connectors['_hash']);
    }

    /**
     * test getting current hash
     */
    public function testHashes()
    {
        $connectorManager = new ConnectorManagerTest();

        $connectors = $connectorManager->getUserConnectors();

        // should get valid connectors with a hash
        $this->assertTrue(!empty($connectors['_hash']));


        $currentUserHash = $connectors['_hash'];
        unset($connectors['_hash']);

        $this->assertTrue($connectorManager->isHashValid($currentUserHash));

        $this->assertFalse($connectorManager->isHashValid('invalidHash'));


    }
}