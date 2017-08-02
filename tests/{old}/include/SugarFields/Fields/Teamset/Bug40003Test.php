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
 * Bug #40003
 * Teams revert to self when Previewing a report
 * @ticket 40003
 */
class Bug40003Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function provider()
    {
        return array(
            array('Global', '1', 'Team_1', '123', '1'),
            array('Global', '1', 'Team_2', '111', '0')
        );
    }

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $_REQUEST['record'] = '';
        $_REQUEST['module'] = 'Reports';
        $this->fields = array('team_name' => array('name' => 'team_name'));
        $this->sft = new SugarFieldTeamset('Teamset');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    
    public function tearDown()
    {
        $_REQUEST = array();
        $_POST = array();
        SugarTestHelper::tearDown();
    }


    /**
     * @dataProvider provider
     * @group 40003
     */
    public function testGetTeamsFromPostWhilePreview($global_name, $global_id, $other_team_name, $other_team_name_id, $primary_collection)
    {
        $_POST['team_name_collection_0'] = $global_name;
        $_POST['id_team_name_collection_0'] = $global_id;
        $_POST['team_name_collection_1'] = $other_team_name;
        $_POST['id_team_name_collection_1'] = $other_team_name_id;
        $_POST['primary_team_name_collection'] = $primary_collection;
        $this->sft->initClassicView($this->fields);
        $this->assertEquals($this->sft->getPrimaryTeamIdFromRequest($this->sft->field_name, $_POST),
                            $this->sft->view->bean->team_set_id_values['primary']['id']);
    }
}
?>
