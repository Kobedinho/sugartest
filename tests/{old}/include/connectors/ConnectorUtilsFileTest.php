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

require_once 'include/connectors/utils/ConnectorUtils.php';

class ConnectorUtilsFileTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->timestamp = time();
        $this->test_path = 'custom/modules/Connectors/connectors/sources/test' . $this->timestamp;
    }

    public function tearDown()
    {
        rmdir_recursive($this->test_path);
    }
    public function testSetConnectorStrings()
    {
        $success = ConnectorUtils::setConnectorStrings(
            'test' . $this->timestamp,
            array('asdf' => 'jkl;'),
            'asdf'
        );

        $this->assertTrue($success);
        $this->assertFileExists($this->test_path . '/language/asdf.lang.php');
    }
}
