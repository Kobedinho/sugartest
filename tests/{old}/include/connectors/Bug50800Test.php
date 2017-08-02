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
require_once('include/connectors/utils/ConnectorUtils.php');

/*
 * This test makes sure that connectors::getConnectors() can handle a badly formed custom metadata file that is either
 * missing the connectors array or the array has been defined as a string
 * @ticket 50800
 */
class Bug50800Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $custom_path = 'custom/modules/Connectors/metadata';
    var $custom_contents;

    function setUp() {
        SugarTestHelper::setUp('app_strings');
        if(file_exists($this->custom_path.'/connectors.php'))
        {
           $this->custom_contents = file_get_contents($this->custom_path.'/connectors.php');
           SugarAutoLoader::unlink($this->custom_path.'/connectors.php');
        } else {
            mkdir_recursive($this->custom_path);
        }
    }

    function tearDown() {
        //remove connector file
        if(!empty($this->custom_contents))
        {
           SugarAutoLoader::put($this->custom_path.'/connectors.php', $this->custom_contents);
        } else {
            SugarAutoLoader::unlink($this->custom_path.'/connectors.php');
        }

        SugarTestHelper::tearDown();
    }

    function testConnectorFailsStringGracefully()
    {
        //now write a connector file with a string instead of an array for the connector var
        SugarAutoLoader::put($this->custom_path.'/connectors.php',"<?php\n \$connector = 'Connector String ';");

        //create the connector and call getConnectors
        $cu = new ConnectorUtils();
        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $cu->getConnectors(true), 'ConnectorsUtils::getConnectors() failed to return an array when $connectors is a string');
    }

    function testConnectorFailsNullGracefully()
    {
        //now write a connector file with missing array info instead of an array for the connector var
        SugarAutoLoader::put($this->custom_path.'/connectors.php',"<?php\n ");

        //create the connector and call getConnectors
        $cu = new ConnectorUtils();
        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $cu->getConnectors(true), 'ConnectorsUtils::getConnectors() failed to return an array when connectors array was missing. ');
    }
}
