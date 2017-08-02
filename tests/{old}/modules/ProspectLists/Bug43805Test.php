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

require_once "include/export_utils.php";
require_once "SugarTestProspectUtilities.php";

class Bug43805Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Contains created prospect lists' ids
     * @var Array
     */
    protected static $_createdProspectListsIds = array();

    /**
     * Instance of ProspectList
     * @var ProspectList
     */
    protected $_prospectList;

    /**
     * prospects array
     * @var Array
     */
    protected $_prospects = array();

    /**
     * Create prospect instance (with account)
     */
    public static function createProspect()
    {

		$prospect = SugarTestProspectUtilities::createProspect();
		
        $prospect->save();
        return $prospect;
       
    }

    /**
     * Create ProspectList instance
     * @param prospect instance to attach to prospect list
     */
    public static function createProspectList($prospect = null)
    {
        $prospectList = new ProspectList();
        $prospectList->name = "TargetList_code";
        $prospectList->save();
        self::$_createdProspectListsIds[] = $prospectList->id;

        if ($prospect instanceof Prospect) {
            self::attachProspectToProspectList($prospectList, $prospect);
        }

        return $prospectList;
    }

    /**
     *
     * Attach Prospect to prospect list
     * @param ProspectList $prospectList prospect list instance
     * @param prospect $prospect prospect instance
     */
    public static function attachProspectToProspectList($prospectList, $prospect)
    {
        $prospectList->load_relationship('prospects');
        $prospectList->prospects->add($prospect->id,array());
    }

    /**
     * Set up - create prospect list with 1 prospect
     */
    public function setUp()
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();;

        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $this->_prospects[] = self::createProspect();
        $this->_prospectList = self::createProspectList($this->_prospects[0]);
        self::attachProspectToProspectList($this->_prospectList, $this->_prospects[0]);
    }

    /**
     * Clear all created data
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    public function tearDown()
    {
        SugarTestProspectUtilities::removeAllCreatedProspects();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->_clearProspects();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }

    /**
     * Test if Title exists within report
     */
    public function testTitleExistsExportList()
    {
        $content = export("ProspectLists", $this->_prospectList->id, true);
				
        $this->assertContains($this->_prospects[0]->title, $content, "Report should contain title of created Prospect");

    }

    private function _clearProspects()
    {
        $ids = implode("', '", self::$_createdProspectListsIds);
        $GLOBALS['db']->query('DELETE FROM prospect_list_campaigns WHERE prospect_list_id IN (\'' . $ids . '\')');
        $GLOBALS['db']->query('DELETE FROM prospect_lists_prospects WHERE prospect_list_id IN (\'' . $ids . '\')');
        $GLOBALS['db']->query('DELETE FROM prospect_lists WHERE id IN (\'' . $ids . '\')');
    }
}
