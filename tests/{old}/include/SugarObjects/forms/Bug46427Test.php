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
 * Bug #46427
 * Records from other Teams shown on Potential Duplicate Contacts screen during Lead Conversion
 *
 * @ticket 46427
 */
class Bug46427Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->createPOST();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanFiles'], $GLOBALS['beanList']);
        $this->clearPOST();
    }

    private function createPOST()
    {
        $_POST['first_name'] = 'FIRST_NAME';
        $_POST['last_name'] = 'LAST_NAME';
    }

    private function clearPOST()
    {
        unset($_POST['first_name'], $_POST['last_name']);
    }

    /**
     * @group 46427
     */
    public function testGetDuplicateQueryContact()
    {
        $focus = $this->createMock('Contact');
        $focus->disable_row_level_security = false;
        $focus->expects($this->once())->method('add_team_security_where_clause');

        $form = new ContactFormBase();
        $form->getDuplicateQuery($focus);
    }

    /**
     * @group 46427
     */
    public function testGetDuplicateQueryContact2()
    {
        $focus = $this->createMock('Contact');
        $focus->disable_row_level_security = true;
        $focus->expects($this->never())->method('add_team_security_where_clause');

        $form = new ContactFormBase();
        $form->getDuplicateQuery($focus);
    }

    /**
     * @group 46427
     */
    public function testGetDuplicateQueryLead()
    {
        $focus = $this->createMock('Lead');
        $focus->disable_row_level_security = false;
        $focus->expects($this->once())->method('add_team_security_where_clause');

        $form = new LeadFormBase();
        $form->getDuplicateQuery($focus);
    }

    /**
     * @group 46427
     */
    public function testGetDuplicateQueryLead2()
    {
        $focus = $this->createMock('Lead');
        $focus->disable_row_level_security = true;
        $focus->expects($this->never())->method('add_team_security_where_clause');

        $form = new LeadFormBase();
        $form->getDuplicateQuery($focus);
    }
}
