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
 * Tests vCard Rest api.
 */
class vCardApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp(){
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('ACLStatic');
    }

    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
        unset($_FILES);
    }

    protected function getApi()
    {
        $api = new RestService();
        $api->user = $GLOBALS['current_user'];
        $api->setResponse(new RestResponse(array()));
        return $api;
    }

    public function testvCardSave()
    {
        $contact = SugarTestContactUtilities::createContact();

        $api = $this->getApi();
        $args = array(
            'module' => 'Contacts',
            'id' => $contact->id,
        );

        $apiClass = new vCardApi();
        $result = $apiClass->vCardSave($api, $args);

        $this->assertContains('BEGIN:VCARD', $result, 'Failed to get contact vCard.');
    }

    /**
     * @group vcardapi_vCardImportPost
     */
    public function testvCardImportPost_NoFilePosted_ReturnsError()
    {
        unset($_FILES);
        $api = $this->getApi();

        $args = array(
            'module' => 'Contacts',
        );

        $this->setExpectedException('SugarApiExceptionMissingParameter');

        $apiClassMock = $this->createPartialMock('vCardApi', array('isUploadedFile'));

        $apiClassMock->expects($this->never())
            ->method('isUploadedFile');

        $apiClassMock->vCardImport($api, $args);
    }

    /**
     * @group vcardapi_vCardImportPost
     */
    public function testvCardImportPost_FileExists_ImportsPersonRecord()
    {
        $_FILES = array(
            'vcard_import'    =>  array(
                'name'      =>  'simplevcard.vcf',
                'tmp_name'  =>  dirname(__FILE__)."/SimpleVCard.vcf",
                'type'      =>  'text/directory',
                'size'      =>  42,
                'error'     =>  0
            )
        );

        $api = $this->getApi();

        $args = array(
            'module' => 'Contacts',
        );

        $apiClassMock = $this->createPartialMock('vCardApi', array('isUploadedFile'));

        $apiClassMock->expects($this->once())
            ->method('isUploadedFile')
            ->will($this->returnValue(true));

        $results = $apiClassMock->vCardImport($api, $args);

        $this->assertEquals(true, is_array($results), 'Incorrect number of items returned');
        $this->assertEquals(true, array_key_exists('vcard_import', $results), 'Incorrect field name returned');

        //verifying that the contact and account was created from vcard.
        $contact = BeanFactory::getBean('Contacts', $results['vcard_import']);

        SugarTestContactUtilities::setCreatedContact(array($results['vcard_import']));
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestContactUtilities::removeCreatedContactsEmailAddresses();

        if(!empty($contact->account_id)) {
            SugarTestAccountUtilities::setCreatedAccount(array($contact->account_id));
            SugarTestAccountUtilities::removeAllCreatedAccounts();
        }
    }

    /**
     * @group vcardapi_vCardImportPost
     */
    public function testvCardImportPost_FailsACLCheck_ThrowsNotAuthorizedException()
    {
        $_FILES = array(
            'vcard_import'    =>  array(
                'name'      =>  'simplevcard.vcf',
                'tmp_name'  =>  dirname(__FILE__)."/SimpleVCard.vcf",
                'type'      =>  'text/directory',
                'size'      =>  42,
                'error'     =>  0
            )
        );
        //Setting access to be denied for import and read
        $acldata = array();
        $acldata['module']['access']['aclaccess'] = ACL_ALLOW_DISABLED;
        $acldata['module']['import']['aclaccess'] = ACL_ALLOW_DISABLED;
        ACLAction::setACLData($GLOBALS['current_user']->id, 'Contacts', $acldata);
        // reset cached ACLs
        SugarACL::$acls = array();

        $api = $this->getApi();

        $args = array(
            'module' => 'Contacts',
        );

        $this->setExpectedException('SugarApiExceptionNotAuthorized');

        $apiClassMock = $this->createPartialMock('vCardApi', array('isUploadedFile'));
        $apiClassMock->vCardImport($api, $args);
    }

    /**
     * @group vcardapi_vCardImportPost2
     */
    public function testvCardImportPost_NoFileExists_ThrowsMissingParameterException()
    {
        $api = $this->getApi();

        $args = array(
            'module' => 'Contacts',
        );

        $this->setExpectedException('SugarApiExceptionMissingParameter');

        $apiClassMock = $this->createPartialMock('vCardApi', array('isUploadedFile'));
        $apiClassMock->vCardImport($api, $args);
    }
 }
