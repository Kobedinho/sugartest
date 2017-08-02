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
 

class SugarFieldLinkTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var Note */
    private $note;
    /** @var Lead */
    private $lead;

	public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('current_user');
        $this->note = BeanFactory::newBean('Notes');
        $this->note->field_defs['testurl_c']['gen'] = 1;
        $this->note->field_defs['testurl_c']['default'] = 'http://test/{assigned_user_id}';
        $this->note->assigned_user_id = $GLOBALS['current_user']->id;
        $this->note->testurl_c1 = "www.sugarcrm.com";
        $this->note->field_defs['testurl_c1']['type']='url';

        $this->lead = BeanFactory::newBean('Leads');
        $this->lead->field_defs['test_c'] = array(
            'gen' => 1,
            'default' => 'http://test/{name}',
        );
	}

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        unset($this->lead->field_defs['test_c']);
        unset($this->note->field_defs['testurl_c']);
        unset($this->note);
        $GLOBALS['reload_vardefs'] = true;
        new Note();
        new Lead();
        $GLOBALS['reload_vardefs'] = null;
    }
    
     /**
     * @ticket 36744
     */
	public function testLinkField() {
        $sf = SugarFieldHandler::getSugarField('url');
        $data = array();
        $service = SugarTestRestUtilities::getRestServiceMock();
        $sf->apiFormatField($data, $this->note, array(), 'testurl_c', array(), array('testurl_c'), $service);
        $this->assertEquals('http://test/'.$GLOBALS['current_user']->id, $data['testurl_c']);
    }
    /**
     * @jira task sc50 url fields not coming across on api
     */
    public function testURLField() {
        $sf = SugarFieldHandler::getSugarField('url');
        $data = array();
        $service = SugarTestRestUtilities::getRestServiceMock();
        $sf->apiFormatField($data, $this->note, array(), 'testurl_c1', array(), array('testurl_c1'), $service);
        $this->assertEquals('www.sugarcrm.com', $data['testurl_c1']);
    }

    public function testNonDbField()
    {
        $this->lead->name = 'John Doe';

        /** @var SugarFieldLink $sf */
        $sf = SugarFieldHandler::getSugarField('url');
        $data = array();
        $service = SugarTestRestUtilities::getRestServiceMock();
        $sf->apiFormatField($data, $this->lead, array(), 'test_c', array(), array('test_c'), $service);
        $this->assertEquals('http://test/John Doe', $data['test_c']);
    }
}
