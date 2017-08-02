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
 * Created: Aug 22, 2011
 */
class SugarFieldRadioenumTest extends Sugar_PHPUnit_Framework_TestCase {
	protected $_testingArray = array (
		"key" => "value"
	);
	protected $_testKey = "key";
	protected $_testValue = "value";
	protected $_testingArrayName = "new_radio_list";
	protected $_testingFieldType = "Radioenum";
	
	public function setUp()
	{
		global $app_list_strings;
		$app_list_strings[$this->_testingArrayName] = $this->_testingArray;
	}
    
	public function tearDown()
	{
		if (!empty($app_list_strings[$this->_testingArrayName])) {
			unset($app_list_strings[$this->_testingArrayName]);
		}
	}
    
	public function testEmailTemplateFormat()
	{
		$radioEnumClass = new SugarFieldRadioenum($this->_testingFieldType);
		$actualResult = $radioEnumClass->getEmailTemplateValue($this->_testKey, array ("options" => $this->_testingArrayName));
		$this->assertEquals($this->_testValue, $actualResult);
	}
}