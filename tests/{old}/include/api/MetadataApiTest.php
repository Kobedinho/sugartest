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


class MetadataApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var MetadataApi
     */
    protected $api;

    /**
     * @var RestService
     */
    protected $serviceMock;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("current_user");
    }

    public function setUp()
    {
        $this->api = new MetadataApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function testGetModuleTabMap()
    {
        // This used to live in the MetadataApi, hence the reason for this test
        $mm  = MetaDataManager::getManager();
        $data = $mm->getModuleTabMap();

        // Test see that the map is not empty and an array
        $this->assertInternalType('array', $data, "module_tab_map is not an array");
        $this->assertNotEmpty($data, "Module Tab Map is empty");

        // Test that a known value is in the data
        $this->assertEquals('Emails', $data['EmailTemplates'], "EmailTemplates not translated properly");
    }

    /**
     * Test asserts behavior of getAllMetadata
     */
    public function testGetAllMetadata()
    {
        $result = $this->api->getAllMetadata($this->serviceMock, array());

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('full_module_list', $result);
        $this->assertArrayHasKey('modules', $result);
        $this->assertArrayHasKey('hidden_subpanels', $result);
        $this->assertArrayHasKey('currencies', $result);
        $this->assertArrayHasKey('module_tab_map', $result);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('views', $result);
        $this->assertArrayHasKey('layouts', $result);
        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('config', $result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('jssource', $result);
        $this->assertArrayHasKey('server_info', $result);
        $this->assertArrayHasKey('logo_url', $result);
        $this->assertArrayHasKey('languages', $result);
        $this->assertArrayHasKey('_override_values', $result);
        $this->assertArrayHasKey('_hash', $result);


        $this->assertInternalType('array', $result['full_module_list']);
        $this->assertInternalType('array', $result['modules']);
        $this->assertInternalType('array', $result['hidden_subpanels']);
        $this->assertInternalType('array', $result['currencies']);
        $this->assertInternalType('array', $result['module_tab_map']);
        $this->assertInternalType('array', $result['fields']);
        $this->assertInternalType('array', $result['views']);
        $this->assertInternalType('array', $result['layouts']);
        $this->assertInternalType('array', $result['labels']);
        $this->assertInternalType('array', $result['config']);
        $this->assertInternalType('array', $result['relationships']);
        $this->assertInternalType('string', $result['jssource']);
        $this->assertInternalType('array', $result['server_info']);
        $this->assertInternalType('string', $result['logo_url']);
        $this->assertInternalType('array', $result['languages']);
        $this->assertInternalType('array', $result['_override_values']);
        $this->assertInternalType('string', $result['_hash']);
    }

    /**
     * Test asserts behavior of getPublicMetadata
     */
    public function testGetPublicMetadata()
    {
        $result = $this->api->getPublicMetadata($this->serviceMock, array());

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('modules', $result);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('views', $result);
        $this->assertArrayHasKey('layouts', $result);
        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('config', $result);
        $this->assertArrayHasKey('jssource', $result);
        $this->assertArrayHasKey('logo_url', $result);
        $this->assertArrayHasKey('_override_values', $result);
        $this->assertArrayHasKey('_hash', $result);


        $this->assertInternalType('array', $result['modules']);
        $this->assertInternalType('array', $result['fields']);
        $this->assertInternalType('array', $result['views']);
        $this->assertInternalType('array', $result['layouts']);
        $this->assertInternalType('array', $result['labels']);
        $this->assertInternalType('array', $result['config']);
        $this->assertInternalType('string', $result['jssource']);
        $this->assertInternalType('string', $result['logo_url']);
        $this->assertInternalType('array', $result['_override_values']);
        $this->assertInternalType('string', $result['_hash']);
    }

    /**
     * Test asserts behavior of getLanguage
     */
    public function testGetLanguage()
    {
        $result = $this->api->getLanguage($this->serviceMock, array('lang' => 'en'));

        $this->assertNotEmpty($result);
        $this->assertJson($result);
        $result = json_decode($result, true);

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('app_list_strings', $result);
        $this->assertArrayHasKey('app_strings', $result);
        $this->assertArrayHasKey('mod_strings', $result);
        $this->assertArrayHasKey('_hash', $result);


        $this->assertInternalType('array', $result['app_list_strings']);
        $this->assertInternalType('array', $result['app_strings']);
        $this->assertInternalType('array', $result['mod_strings']);
        $this->assertInternalType('string', $result['_hash']);
    }

    /**
     * Test asserts behavior of getPublicLanguage
     */
    public function testGetPublicLanguage()
    {
        $result = $this->api->getPublicLanguage($this->serviceMock, array('lang' => 'en'));

        $this->assertNotEmpty($result);
        $this->assertJson($result);
        $result = json_decode($result, true);

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('app_list_strings', $result);
        $this->assertArrayHasKey('app_strings', $result);
        $this->assertArrayHasKey('_hash', $result);

        $this->assertInternalType('array', $result['app_list_strings']);
        $this->assertInternalType('array', $result['app_strings']);
        $this->assertInternalType('string', $result['_hash']);
    }
}
