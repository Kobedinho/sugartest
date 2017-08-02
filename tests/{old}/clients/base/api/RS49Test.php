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
 * RS-49
 * Prepare File Api
 */
class RS49Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var FileApi */
    protected $api = null;

    /** @var string */
    protected $file = '';

    /** @var Note */
    protected $note = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));
        SugarTestHelper::setUP('app_list_strings');

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new FileApi();

        $this->file = tempnam(sys_get_temp_dir(), __CLASS__);
        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile($this->file);
        file_put_contents($this->file, create_guid());

        $this->note = new Note();
        $this->note->title = 'Note ' . __CLASS__;
        $this->note->save();

        $_FILES = array();
    }

    public function tearDown()
    {
        $_FILES = array();

        if ($this->note instanceof Note) {
            $this->note->mark_deleted($this->note->id);
        }
        SugarTestHelper::tearDown();
    }

    /**
     * On correct info saveFilePut should call saveFilePost and to return its result.
     */
    public function testSaveFilePut()
    {
        $parameters = array(
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'filename',
        );
        $_FILES[$parameters['field']] = array();
        $api = $this->createPartialMock('FileApi', array('saveFilePost'));
        $api->expects($this->once())->method('saveFilePost')->will($this->returnValue('saveFilePostReturnString'))->with($this->equalTo($this->service), $this->equalTo($parameters));
        $actual = $api->saveFilePut($this->service, $parameters, $this->file);
        $this->assertEquals('saveFilePostReturnString', $actual);
        $this->assertNotEmpty($_FILES[$parameters['field']]);
    }

    /**
     * On empty size of file we should get exception
     *
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testSaveFilePutFileSize()
    {
        file_put_contents($this->file, '');
        $api = $this->createPartialMock('FileApi', array('saveFilePost'));
        $api->expects($this->never())->method('saveFilePost');
        $api->saveFilePut($this->service, array(), $this->file);
    }

    /**
     * We should get record & file info on success upload
     */
    public function testSaveFilePost()
    {
        $_FILES = array(
            'filename' => array(
                'name' => 'test.txt',
                'size' => filesize($this->file),
                'tmp_name' => $this->file,
                'error' => 0,
                '_SUGAR_API_UPLOAD' => true,
            ),
        );

        $parameters = array(
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'filename',
        );
        $actual = $this->api->saveFilePost($this->service, $parameters);
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey($parameters['field'], $actual);
        $this->assertArrayHasKey('record', $actual);
        $this->assertEquals($this->note->id, $actual['record']['id']);
        $this->assertArrayHasKey($parameters['field'] . '_file', $_FILES);
        $this->assertEquals($_FILES[$parameters['field'] . '_file']['name'], $actual['record'][$parameters['field']]);
    }

    /**
     * We should get exception if ACLAccess returns false
     *
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testSaveFilePostBeanACLAccessView()
    {
        $bean = $this->createPartialMock('Note', array('ACLAccess'));
        $bean->id = $this->note->id;
        $bean->expects($this->once())->method('ACLAccess')->will($this->returnValue(false));

        $api = $this->createPartialMock('FileApi', array('loadBean'));
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($bean));

        $parameters = array(
            'module' => $bean->module_dir,
            'record' => $bean->id,
            'field' => 'filename',
        );
        $api->saveFilePost($this->service, $parameters);
    }

    /**
     * We should get exception if $_FILES isn't set
     *
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testSaveFilePostFilesAreNotSet()
    {
        $_FILES = null;

        $parameters = array(
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'filename',
        );

        $this->api->saveFilePost($this->service, $parameters);
    }

    /**
     * We should get exception if $_FILES is empty
     *
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testSaveFilePostFilesAreSetAndEmpty()
    {
        $_FILES = array();

        $parameters = array(
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'filename',
        );

        $this->api->saveFilePost($this->service, $parameters);
    }

    /**
     * We should get exception if $_FILES is present but doesn't contain current file
     *
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testSaveFilePostFilesAreSetButWithoutCurrentFile()
    {
        $_FILES = array(
            'name' => array(
                'name' => 'name',
            ),
        );

        $parameters = array(
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'filename',
        );

        $this->api->saveFilePost($this->service, $parameters);
    }

    /**
     * @expectedException SugarApiExceptionError
     */
    public function testSaveFilePostIncorrectFieldType()
    {
        $_FILES = array(
            'name' => array(
                'name' => 'name',
            )
        );

        $parameters = array(
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'name',
        );

        $this->api->saveFilePost($this->service, $parameters);
    }

    /**
     * We should get exception if $_FILES is empty
     * Also mark_deleted method should be called if delete_if_fails parameter is present
     *
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testDeleteIfFailsWithParameter()
    {
        $_FILES = array();

        $parameters = array(
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'filename',
            'delete_if_fails' => true,
        );

        $bean = $this->createPartialMock('Note', array('mark_deleted'));
        $bean->id = $this->note->id;
        $bean->created_by = $GLOBALS['current_user']->id;
        $bean->expects($this->once())->method('mark_deleted')->with($this->equalTo($bean->id))->will($this->returnValue(true));

        $api = $this->createPartialMock('FileApi', array('loadBean'));
        $api->expects($this->any())->method('loadBean')->will($this->returnValue($bean));

        $api->saveFilePost($this->service, $parameters);
    }

    /**
     * We should get exception if $_FILES is empty
     * Also mark_deleted method shouldn't be called if delete_if_fails parameter isn't present
     *
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testDeleteIfFailsWithoutParameter()
    {
        $_FILES = array();

        $parameters = array(
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'filename',
        );

        $bean = $this->createPartialMock('Note', array('mark_deleted'));
        $bean->id = $this->note->id;
        $bean->created_by = $GLOBALS['current_user']->id;
        $bean->expects($this->never())->method('mark_deleted');

        $api = $this->createPartialMock('FileApi', array('loadBean'));
        $api->expects($this->any())->method('loadBean')->will($this->returnValue($bean));

        $api->saveFilePost($this->service, $parameters);
    }

    /**
     * We should get list of file/image fields
     */
    public function testGetFileList()
    {
        $parameters = array(
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
        );
        $actual = $this->api->getFileList($this->service, $parameters);
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('filename', $actual);
    }

    /**
     * We should get exception if ACLAccess returns false
     *
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testGetFileListACLAccessView()
    {
        $bean = $this->getMockBuilder('Note')->setMethods(array('ACLAccess'))->getMock();
        $bean->id = $this->note->id;
        $bean->expects($this->once())->method('ACLAccess')->will($this->returnValue(false));

        $api = $this->createPartialMock('FileApi', array('loadBean'));
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($bean));

        $parameters = array(
            'module' => $bean->module_dir,
            'record' => $bean->id,
        );
        $api->getFileList($this->service, $parameters);
    }

    /**
     * We should get exception about incorrect file, it means success of getFile method.
     *
     * @expectedException        SugarApiExceptionNotFound
     * @expectedExceptionMessage File information could not be retrieved for this record
     */
    public function testGetFile()
    {
        $bean = $this->getMockBuilder('Note')->setMethods(array('ACLAccess'))->getMock();
        $bean->id = $this->note->id;
        $bean->filename = $this->file;
        $bean->expects($this->once())->method('ACLAccess')->will($this->returnValue(true));

        $api = $this->createPartialMock('FileApi', array('loadBean'));
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($bean));

        $parameters = array(
            'module' => $bean->module_dir,
            'record' => $bean->id,
            'field' => 'filename',
        );
        $api->getFile($this->service, $parameters);
    }

    /**
     * We should get exception if field is not present in $parameters
     *
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testGetFileWithoutField()
    {
        $parameters = array(
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
        );
        $this->api->getFile($this->service, $parameters);
    }

    /**
     * We should get exception if ACLAccess returns false
     *
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testGetFileACLAccessView()
    {
        $bean = $this->getMockBuilder('Note')->setMethods(array('ACLAccess'))->getMock();
        $bean->id = $this->note->id;
        $bean->expects($this->once())->method('ACLAccess')->will($this->returnValue(false));

        $api = $this->createPartialMock('FileApi', array('loadBean'));
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($bean));

        $parameters = array(
            'module' => $bean->module_dir,
            'record' => $bean->id,
            'field' => 'filename',
        );
        $api->getFile($this->service, $parameters);
    }

    /**
     * We should get exception if field is empty
     *
     * @expectedException SugarApiExceptionNotFound
     */
    public function testGetFileACLEmptyField()
    {
        $bean = $this->getMockBuilder('Note')->setMethods(array('ACLAccess'))->getMock();
        $bean->id = $this->note->id;
        $bean->filename = '';
        $bean->expects($this->once())->method('ACLAccess')->will($this->returnValue(true));

        $api = $this->createPartialMock('FileApi', array('loadBean'));
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($bean));

        $parameters = array(
            'module' => $bean->module_dir,
            'record' => $bean->id,
            'field' => 'filename',
        );
        $api->getFile($this->service, $parameters);
    }

    /**
     * getFileList method should be called in the end of removeFile method
     * If field is present then deleteAttachment method should be called on bean
     */
    public function testRemoveFile()
    {
        $bean = $this->getMockBuilder('Note')->setMethods(array('deleteAttachment'))->getMock();
        $bean->id = $this->note->id;
        $bean->filename = $this->file;
        $bean->expects($this->once())->method('deleteAttachment')->will($this->returnValue(true));

        $api = $this->createPartialMock('FileApi', array('loadBean', 'getFileList'));
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($bean));
        $api->expects($this->once())->method('getFileList')->will($this->returnValue(array('getFileListReturnString')));

        $parameters = array(
            'module' => $bean->module_dir,
            'record' => $bean->id,
            'field' => 'filename',
        );
        $actual = $api->removeFile($this->service, $parameters);
        $this->assertContains('getFileListReturnString', $actual);
    }

    /**
     * getFileList method should be called in the end of removeFile method
     * If field isn't present then deleteAttachment method shouldn't be called on bean
     */
    public function testRemoveFileEmptyField()
    {
        $bean = $this->getMockBuilder('Note')->setMethods(array('deleteAttachment'))->getMock();
        $bean->id = $this->note->id;
        $bean->filename = '';
        $bean->expects($this->never())->method('ACLAccess');

        $api = $this->createPartialMock('FileApi', array('loadBean', 'getFileList'));
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($bean));
        $api->expects($this->once())->method('getFileList')->will($this->returnValue(array('getFileListReturnString')));

        $parameters = array(
            'module' => $bean->module_dir,
            'record' => $bean->id,
            'field' => 'filename',
        );
        $actual = $api->removeFile($this->service, $parameters);
        $this->assertContains('getFileListReturnString', $actual);
    }

    /**
     * We should get exception if field isn't file or image
     *
     * @expectedException SugarApiExceptionError
     */
    public function testRemoveFileIncorrectFieldType()
    {
        $api = $this->createPartialMock('FileApi', array('loadBean', 'getFileList'));
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($this->note));
        $api->expects($this->never())->method('getFileList')->will($this->returnValue('getFileListReturnString'));

        $parameters = array(
            'module' => $this->note->module_dir,
            'record' => $this->note->id,
            'field' => 'id',
        );
        $api->removeFile($this->service, $parameters);
    }

    /**
     * We should get exception if deleteAttachment fails
     *
     * @expectedException SugarApiExceptionRequestMethodFailure
     */
    public function testRemoveFileDeleteAttachmentFails()
    {
        $bean = $this->getMockBuilder('Note')->setMethods(array('deleteAttachment'))->getMock();
        $bean->id = $this->note->id;
        $bean->filename = $this->file;
        $bean->expects($this->once())->method('deleteAttachment')->will($this->returnValue(false));

        $api = $this->createPartialMock('FileApi', array('loadBean', 'getFileList'));
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($bean));
        $api->expects($this->never())->method('getFileList')->will($this->returnValue('getFileListReturnString'));

        $parameters = array(
            'module' => $bean->module_dir,
            'record' => $bean->id,
            'field' => 'filename',
        );
        $api->removeFile($this->service, $parameters);
    }
}
