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

class Bug36460Test extends Sugar_PHPUnit_Framework_TestCase
{
    public $glueClass;
    
    public function setUp()
    {
        $this->glueClass = new WorkFlowGlue();
    }

    public function tearDown()
    {
        unset($this->glueClass);
    }
    
    public function testCorrectWorkFlowConditionIfEmpty()
    {
        $this->assertEquals('==', $this->glueClass->translateOperator('Is empty'));
        $this->assertEquals('!=', $this->glueClass->translateOperator('Is not empty'));
    }
}
?>
