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


/*
* This test check if prosect adds correctly to prospects_list
* @ticket 53288
*/


class Bug53288Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_oProspectList;
    protected $_oProspect;

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user', array(true, 1));
        $this->_oProspect = SugarTestProspectUtilities::createProspect();
        $this->createProspectList();
    }

    public function tearDown()
    {
        SugarTestProspectListsUtilities::removeProspectsListToProspectRelation($this->_oProspectList->id, $this->_oProspect->id);
        SugarTestProspectUtilities::removeAllCreatedProspects();
        SugarTestProspectListsUtilities::removeProspectLists($this->_oProspectList->id);
        $_REQUEST = array();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    public function testAddProspectsToProspectList()
    {
        $_REQUEST['prospect_list_id'] = $this->_oProspectList->id;
        $_REQUEST['prospect_id'] = $this->_oProspect->id;
        $_REQUEST['prospect_ids'] = array($this->_oProspect->id);
        $_REQUEST['return_type'] = 'addtoprospectlist';
        require('include/generic/Save2.php');
        $res = $GLOBALS['db']->query("SELECT * FROM prospect_lists_prospects WHERE prospect_list_id='{$this->_oProspectList->id}' AND related_id='{$this->_oProspect->id}'");
        $row = $GLOBALS['db']->fetchByAssoc($res);
        $this->assertInternalType('array', $row);
    }

    protected function createProspectList()
    {
        $this->_oProspectList = new ProspectList();
        $this->_oProspectList->name = "Bug53288Test_ProspectListName";
        $this->_oProspectList->save();
    }

}
