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

require_once 'include/connectors/utils/ConnectorUtils.php';
require_once 'include/connectors/ConnectorsTestCase.php';
require_once 'tests/{old}/include/connectors/ZoominfoHelper.php';

class ZoominfoConnectorsTest extends Sugar_Connectors_TestCase
{
    protected $qual_module;
    protected $mock;

    public function setUp()
    {
        $this->markTestSkipped(
            'skip legacy connector test'
        );
        parent::setUp();
        $this->mock = new ZoominfoTestHelper();

        ConnectorFactory::$source_map = array();

        if (file_exists('custom/modules/Connectors/connectors/sources/ext/rest/zoominfocompany/mapping.php')) {
            unlink('custom/modules/Connectors/connectors/sources/ext/rest/zoominfocompany/mapping.php');
        }
        SugarAutoLoader::delFromMap('custom/modules/Connectors/connectors/sources/ext/rest/zoominfocompany/mapping.php', false);

        ConnectorFactory::$source_map = array();

        $_REQUEST['module'] = 'Connectors';
        $_REQUEST['from_unit_test'] = true;
        $_REQUEST['modify'] = true;
        $_REQUEST['action'] = 'SaveModifyDisplay';
        $_REQUEST['display_values'] = 'ext_rest_zoominfoperson:Leads,ext_rest_zoominfocompany:Leads';
        $_REQUEST['display_sources'] = 'ext_rest_zoominfocompany,ext_rest_zoominfoperson';

        $controller = new ConnectorsController();
        $controller->action_SaveModifyDisplay();

        $_REQUEST['action'] = 'SaveModifyMapping';
        $_REQUEST['mapping_values'] = 'ext_rest_zoominfoperson:Leads:firstname=first_name,ext_rest_zoominfoperson:Leads:lastname=last_name,ext_rest_zoominfoperson:Leads:jobtitle=title,ext_rest_zoominfoperson:Leads:companyname=account_name,ext_rest_zoominfocompany:Leads:companyname=account_name,ext_rest_zoominfocompany:Leads:companydescription=description';
        $_REQUEST['mapping_sources'] = 'ext_rest_zoominfoperson,ext_rest_zoominfocompany';
        $controller->action_SaveModifyMapping();

        $this->qual_module = 'Leads';
        $this->company_source = ConnectorFactory::getInstance('ext_rest_zoominfocompany')->getSource();
        $this->company_props = $this->company_source->getProperties();
        $this->person_source = ConnectorFactory::getInstance('ext_rest_zoominfoperson')->getSource();
        $this->person_props = $this->person_source->getProperties();
    }

    public function tearDown()
    {
        /* Commenting this out so that errors don't happen while the test is skipped.
        parent::tearDown();
        $this->company_source->setProperties($this->company_props);
        $this->person_source->setProperties($this->person_props);
        $this->mock = null;
        // reload map
        SugarAutoLoader::loadFileMap();*/
    }

    public function testZoominfoCompanyFillBeans()
    {
        $source_instance = ConnectorFactory::getInstance('ext_rest_zoominfocompany');
        $source_instance->getSource()->loadMapping();
        $props = $source_instance->getSource()->getProperties();
        $props['company_search_url'] = $this->mock->url('company_search_query');
        $source_instance->getSource()->setProperties($props);
        $leads = array();
        $leads = $source_instance->fillBeans(array('companyname'=>'Cisco Systems, Inc'), $this->qual_module, $leads);
        foreach ($leads as $count => $lead) {
            $this->assertContains('Cisco', $lead->account_name, "Assert fillBeans set account name to Cisco");
            break;
        }
    }

    public function testZoominfoCompanyFillBean()
    {
        $source_instance = ConnectorFactory::getInstance('ext_rest_zoominfocompany');
        $source_instance->getSource()->loadMapping();
        $props = $source_instance->getSource()->getProperties();
        $props['company_detail_url'] = $this->mock->url('company_detail');
        $source_instance->getSource()->setProperties($props);
        $lead = new Lead();
        $lead = $source_instance->fillBean(array('id' => '18579882'), $this->qual_module, $lead);
        $this->assertContains('International Business Machines Corporation', $lead->account_name);
    }

    public function testZoominfoPersonFillBeans()
    {
        $source_instance = SourceFactory::getSource('ext_rest_zoominfoperson');
        $props = $source_instance->getProperties();
        $props['person_search_url'] = $this->mock->url('people_search_query');
        $source_instance->setProperties($props);

        $args = array('firstname'=>'John', 'lastname'=>'Roberts');
        $data = $source_instance->getList($args, $this->qual_module);
        $this->assertNotEmpty($data);

        $leads = array();
        $source_instance = ConnectorFactory::getInstance('ext_rest_zoominfoperson');
        $props = $source_instance->getSource()->getProperties();
        $props['person_search_url'] = $this->mock->url('people_search_query2');
        $source_instance->getSource()->setProperties($props);
        $leads = $source_instance->fillBeans($args, $this->qual_module, $leads);
        foreach ($leads as $count => $lead) {
            $this->assertEquals($data[$count]['firstname'], $lead->first_name);
            $this->assertEquals($data[$count]['lastname'], $lead->last_name);
            break;
        }
    }
}
