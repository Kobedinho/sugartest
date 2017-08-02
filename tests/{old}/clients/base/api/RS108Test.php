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
 * RS-108
 * Prepare FileTemp Api
 * @requires extension gd
 */
class RS108Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var string */
    protected $file = '';

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->service = SugarTestRestUtilities::getRestServiceMock();

        SugarAutoLoader::ensureDir(UploadStream::path("upload://tmp/"));
        $this->file = UploadStream::path("upload://tmp/") . create_guid();
        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile($this->file);
        $img = imagecreate(1, 1);
        imagecolorallocate($img, 0, 0, 0);
        imagepng($img, $this->file);
        imagedestroy($img);
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * 3rd argument of saveFilePost method should be true for Temp File
     */
    public function testSaveTempImagePost()
    {
        $api = $this->createPartialMock('FileTempApi', array('saveFilePost'));
        $api->expects($this->once())->method('saveFilePost')->with($this->anything(), $this->anything(), $this->equalTo(true));
        $api->saveTempImagePost($this->service, array());
    }

    /**
     * On success fileResponse method of RestService should be called with argument which is equal to file path
     */
    public function testGetTempImage()
    {
        $service = $this->createPartialMock('RestService', array('fileResponse'));
        $service->expects($this->once())->method('fileResponse')->with($this->equalTo($this->file));
        $api = new FileTempApi();
        $api->getTempImage($service, array(
                'module' => 'Users',
                'record' => $GLOBALS['current_user']->id,
                'field' => 'image',
                'temp_id' => basename($this->file),
            ));
    }

    /**
     * We should get exception if field isn't passed
     *
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testGetTempImageWithoutField()
    {
        $api = new FileTempApi();
        $api->getTempImage($this->service, array());
    }

    /**
     * We should get exception if file doesn't exist
     *
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testGetTempImageWithoutTempId()
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
        $api = new FileTempApi();
        $api->getTempImage($this->service, array(
                'module' => 'Users',
                'record' => $GLOBALS['current_user']->id,
                'field' => 'image',
                'temp_id' => basename($this->file),
            ));
    }
}
