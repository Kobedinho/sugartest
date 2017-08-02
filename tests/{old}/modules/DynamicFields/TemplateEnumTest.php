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

require_once('modules/DynamicFields/FieldCases.php');

class TemplateEnumTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_modulename = 'Accounts';
    private $_originaldbType = '';
    private $field;
    
    public function setUp()
    {
        $this->field = get_widget('enum');
        $this->field->id = $this->_modulename.'foofighter_c';
        $this->field->name = 'foofighter_c';
        $this->field->dependency = htmlentities('equal(strlen($name),5)');
        $this->field->ext4 = serialize(htmlentities('fred'));
    }
    
    public function tearDown()
    {
        
    }
    
    public function testPopulateDependencyFromDependencyField()
    {
       $fieldDef = $this->field->get_field_def();
       $this->assertEquals('equal(strlen($name),5)', $fieldDef['dependency'], 'The dependency was not populated correctly.');
    }

    public function testPopulateDependencyFromExt4()
    {
        unset($this->field->dependency);
       $fieldDef = $this->field->get_field_def();
       $this->assertEquals('fred', $fieldDef['dependency'], 'The dependency was not populated correctly.');
    }
}
