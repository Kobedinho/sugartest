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

require_once 'include/download_file.php';

/**
 * Test for DownloadFile class.
 *
 * Class DownloadFileTest
 */
class DownloadFileTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @param array $data
     * @param array $expected
     * @covers DownloadFile::getFileNamesForArchive
     * @dataProvider getData
     */
    public function testGetFileNamesForArchive($data, $expected)
    {
        $map = array();
        $beans = array();
        foreach ($data as $info) {
            $bean = BeanFactory::newBean('Accounts');
            $bean->name = $info['name'];
            array_push($beans, $bean);
            array_push($map, array($bean, 'somefield', $info));
        }

        $df = $this->createPartialMock('DownloadFile', array('validateBeanAndField', 'getFileInfo'));
        $df->expects($this->any())
            ->method('validateBeanAndField')
            ->willReturn(true);
        $df->expects($this->any())
            ->method('getFileInfo')
            ->will($this->returnValueMap($map));

        $result = $df->getFileNamesForArchive($beans, 'somefield');
        $this->assertEquals($expected, $result);
    }

    /**
     * Data Provider for test.
     * @return array
     */
    public function getData()
    {
        return array(
            array(
                array(
                    array(
                        'name' => 'file1',
                        'path' => 'path1',
                    ),
                    array(
                        'name' => 'file1',
                        'path' => 'path2',
                    ),
                    array(
                        'name' => 'file2.jpg',
                        'path' => 'path3',
                    ),
                    array(
                        'name' => 'file2.jpg',
                        'path' => 'path4',
                    ),
                    array(
                        'name' => 'file3.jpg',
                        'path' => 'path5',
                    ),
                    array(
                        'name' => 'file4',
                        'path' => 'path6',
                    ),
                ),
                array(
                    'file1_0' => 'path1',
                    'file1_1' => 'path2',
                    'file2_0.jpg' => 'path3',
                    'file2_1.jpg' => 'path4',
                    'file3.jpg' => 'path5',
                    'file4' => 'path6',
                ),
            )
        );
    }
}
