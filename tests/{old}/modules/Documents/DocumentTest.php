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

class DocumentTest extends Sugar_PHPUnit_Framework_TestCase
{
    var $doc = null;
    
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }
    
    function testPopulateFromRow()
    {
        $this->doc = BeanFactory::newBean('Documents');

        // Make sure it prefers name if it comes from the row
        $this->doc->populateFromRow(array('name'=>'SetName','document_name'=>'NotThis'));
        $this->assertEquals('SetName',$this->doc->name);
        
        $this->doc->populateFromRow(array('document_name'=>'DocName'));
        $this->assertEquals('DocName',$this->doc->name);
    }

}
