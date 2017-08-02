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

require_once('tests/{old}/service/SOAPTestCase.php');
/**
 * @ticket 57656
 */
class Bug57656Test extends SOAPTestCase
{
    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';

        parent::setUp();
        $this->tabs = new TabController();
        $tabs = $this->orig_tabs = $this->tabs->get_system_tabs();
        if(in_array("Bugs", $tabs)) {
            unset($tabs[array_search("Bugs", $tabs)]);
        }
        $this->tabs->set_system_tabs($tabs);
    }

    public function tearDown()
    {
        if(!empty($this->bugid)) {
            $GLOBALS['db']->query("DELETE FROM bugs WHERE id='{$this->bugid}'");
        }
        $this->tabs->set_system_tabs($this->orig_tabs);
    }

    public function soapClients()
    {
        return array(
            array($GLOBALS['sugar_config']['site_url'].'/soap.php'),
            array($GLOBALS['sugar_config']['site_url'].'/service/v3_1/soap.php')
        );
    }

    /**
     * Test creates new bug report
     * @dataProvider soapClients
     * @group 57656
     */
    public function testCreateBug($url)
    {
        $this->_soapClient = new nusoapclient($url,false,false,false,false,false,600,600);
        $this->_login();
        $params = array(
        array("name" => "name", "value" => "TEST"),
        array("name" => "parent_id", "value" => "5a770071-66ca-6127-5a1a-4cb3a2c46e40"),
        array("name" => "parent_type", "value" => "Accounts"),
        array("name" => "from_addr", "value" => "test@test.com"),
        array("name" => "to_addrs", "value" => "test@test.com"),
        );
        $res = $this->_soapClient->call('set_entry', array($this->_sessionId, 'Bugs', $params));
        $this->assertNotEquals("-1", $res['id'], "Bad bug ID");

        $b = new Bug();
        $b->retrieve($res['id']);
        $this->assertNotEmpty($b->id);

        $this->bugid = $b->id;
    }
}
