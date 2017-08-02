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
 * @ticket 43343
 */
class Bug43343Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $email;
    
	public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->email = new Email();
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($_REQUEST['searchDateFrom']);
        unset($_REQUEST['searchDateTo']);
    }
    
    public function testEmptyImportSearchDateWhereClause()
    {
        unset($_REQUEST['searchDateFrom']);
        unset($_REQUEST['searchDateTo']);
        $whereClause = $this->email->_generateSearchImportWhereClause();
 
        $this->assertTrue( preg_match('/emails.date_sent/', $whereClause) == 0 );
    }
}
