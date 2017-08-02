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


class SugarFieldPasswordTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var SugarFieldPassword */
    protected $fieldObj;
    protected $contactBean;
    protected $currentPassword;

    protected function setUp()
    {
        $this->fieldObj = new SugarFieldPassword('Password');
    }

    protected function tearDown()
    {
        unset($this->fieldObj);
    }

    /**
     * @ticket 40304
     */
    public function testImportSanitize()
    {
        $settings = new ImportFieldSanitize();

        $this->assertEquals(
            md5('test value'),
            $this->fieldObj->importSanitize('test value',array(),null,$settings)
            );
    }

    /**
     * Test formatting the apiFormatField method of a Password field
     */
    public function testApiFormatField()
    {
        $data = array(
            'id' => 'awesome',
            'user_hash' => 'this-is-my-password',
        );

        $bean = BeanFactory::newBean('Users');
        $args = array();
        $fieldName = 'user_hash';
        $properties = array();
        $fieldList = array($fieldName);
        $service = SugarTestRestUtilities::getRestServiceMock();
        // no bean password set, so it returns empty string
        $this->fieldObj->apiFormatField($data, $bean, $args, $fieldName, $properties, $fieldList, $service);
        $this->assertEquals('', $data['user_hash']);
        $this->assertEquals('awesome', $data['id']);

        $bean->user_hash = 'this-is-my-password';
        // bean password set so it returns value_setvalue_setvalue_set
        $this->fieldObj->apiFormatField($data, $bean, $args, $fieldName, $properties, $fieldList, $service);
        $this->assertEquals(true, $data['user_hash']);
        $this->assertEquals('awesome', $data['id']);
    }

    /**
     * Test the apiSave method of a Password field
     */
    public function testApiSave()
    {
        $contactBean = BeanFactory::newBean('Contacts');
        $contactBean->portal_password = User::getPasswordHash('awesome');
        $currentPassword = $contactBean->portal_password;

        // dataProvider is not working when you need to check class vars
        // test password not change
        $this->fieldObj->apiSave($contactBean, array('portal_password' => true), 'portal_password', array());
        $this->assertEquals($currentPassword, $contactBean->portal_password, "Password should not have changed");

        // test password being unset
        $this->fieldObj->apiSave($contactBean, array('portal_password' => ''), 'portal_password', array());
        $this->assertEquals(null, $contactBean->portal_password, "Password should be null");

        // test changing password
        $this->fieldObj->apiSave($contactBean, array('portal_password' => '1234'), 'portal_password', array());
        $this->assertTrue(User::checkPassword('1234', $contactBean->portal_password), "The password didn't change");
    }
}
