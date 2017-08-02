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
 * Bug #49964
 *
 * 	Save & Deploy of Meetings dashlet makes non-sortable columns sortable, causing SQL Error
 *
 * @ticket 49964
 * @author arymarchik@sugarcrm.com
 */
class Bug49964Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_metadata = array(
        'set_complete' => array(
            'width' => '1',
            'label'    => 'LBL_LIST_CLOSE',
            'default'  => true,
            'sortable' => false,
            'related_fields' => array(
                'status'
            )
        ),
        'join_meeting' => array(
            'width'    => '1',
            'label'    => 'LBL_LIST_JOIN_MEETING',
            'default'  => true,
            'sortable' => false,
            'noHeader' => true,
            'related_fields' => array(
                'host_url'
            )
        ),
        'name' => array(
            'width'   => '40',
            'label'   => 'LBL_SUBJECT',
            'link'    => true,
            'default' => true
        ),
        'parent_name' => array(
            'width' => '29',
            'label' => 'LBL_LIST_RELATED_TO',
            'sortable' => false,
            'dynamic_module' => 'PARENT_TYPE',
            'link' => true,
            'id' => 'PARENT_ID',
            'ACLTag' => 'PARENT',
            'related_fields' => array(
                'parent_id',
                'parent_type'
            ),
            'default' => true
        ),
        'duration' => array(
            'width'    => '15',
            'label'    => 'LBL_DURATION',
            'sortable' => false,
            'related_fields' => array(
                'duration_hours',
                'duration_minutes'
            )
        ),
        'date_start' => array(
            'width'   => '15',
            'label'   => 'LBL_DATE',
            'default' => true,
            'related_fields' => array(
                'time_start'
            )
        ),
        'set_accept_links'=> array(
            'width'    => '10',
            'label'    => 'LBL_ACCEPT_THIS',
            'sortable' => false,
            'default' => true,
            'related_fields' => array(
                'status'
            )
        ),
        'status' => array(
            'width'   => '8',
            'label'   => 'LBL_STATUS'
        ),
        'type' => array(
            'width'   => '8',
            'label'   => 'LBL_TYPE'
        ),
        'date_entered' => array(
            'width'   => '15',
            'label'   => 'LBL_DATE_ENTERED'
        ),
        'date_modified' => array(
            'width'   => '15',
            'label'   => 'LBL_DATE_MODIFIED'
        ),
        'created_by' => array(
            'width'   => '8',
            'label'   => 'LBL_CREATED'
        ),
        'assigned_user_name' => array(
            'width'   => '8',
            'label'   => 'LBL_LIST_ASSIGNED_USER'
        ),
        'team_name' => array(
            'width'   => '15',
            'label'   => 'LBL_LIST_TEAM'
        )
    );

    public function setUp()
    {
        global $beanList, $beanFiles;
        require('include/modules.php');

        $_REQUEST = array();
        $_REQUEST['view'] = 'dashlet';
        $_REQUEST['view_module'] = 'Meetings';
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
    }

    public function tearDown()
    {
        $_REQUEST = array();
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }

    /**
     * Trying to load default metadata and compare it with origin after conversion
     * @group 49964
     */
    public function testHandleSave()
    {
        $mock = $this->getMockBuilder('DeployedMetaDataImplementation')
                ->disableOriginalConstructor()
                ->setMethods(array('_loadFromFile'))
                ->getMock();
        $mock->expects($this->any())
            ->method('_loadFromFile')
            ->will($this->onConsecutiveCalls(null, null, null, null, $this->_metadata));
        $mock->__construct($_REQUEST['view'], $_REQUEST['view_module']);
        $this->assertEquals($this->_metadata, $mock->getViewdefs());
    }
}
