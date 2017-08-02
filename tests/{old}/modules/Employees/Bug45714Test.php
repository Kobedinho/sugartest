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


class Bug45714Test extends Sugar_PHPUnit_Framework_TestCase 
{
	public function setUp()
	{
		 $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
		 //$this->useOutputBuffering = true;
	}	
	
	public function tearDown()
	{
		 SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	}
	
	public function testViewAsAdminUser()
	{
		$GLOBALS['current_user']->is_admin = true;
		$output = $this->getEmployeeListViewOutput();
		$output = $this->getEmployeeListViewOutput();
		$this->assertRegExp('/utilsLink/', $output, 'Assert that the links are shown for admin user');		
		$output = $this->getEmployeeListViewOutput();
		$this->assertRegExp('/utilsLink/', $output, 'Assert that the links are shown for module admin user');
	}
	
	public function testViewAsNonAdminUser()
	{
		$output = $this->getEmployeeListViewOutput();
		$this->assertNotRegExp('/utilsLink/', $output, 'Assert that the links are not shown for normal user');
		$output = $this->getEmployeeDetailViewOutput();
		$this->assertNotRegExp('/utilsLink/', $output, 'Assert that the links are not shown for normal user');
	}
	
	public function testViewAsModuleAdmin()
	{
		$GLOBALS['current_user'] = new Bug45714UserMock();
		$output = $this->getEmployeeListViewOutput();
		$this->assertRegExp('/utilsLink/', $output, 'Assert that the links are shown for module admin user');
		$output = $this->getEmployeeDetailViewOutput();
		$this->assertRegExp('/utilsLink/', $output, 'Assert that the links are shown for module admin user');	
	}
	
	private function getEmployeeListViewOutput()
	{
		$employeeViewList = new EmployeesViewList();
		$employeeViewList->module = 'Employees';
		return $employeeViewList->getModuleTitle(true);
	}
	
	private function getEmployeeDetailViewOutput()
	{
		$employeeViewDetail = new EmployeesViewDetail();
		$employeeViewDetail->module = 'Employees';
		return $employeeViewDetail->getModuleTitle(true);
	}	
}

class Bug45714UserMock extends User
{
    public function isDeveloperForModule($module) {
		return true;
    }
}

?>