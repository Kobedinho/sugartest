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
 * Tests for KBContentsUsefulnessApi
 */
class KBContentsUsefulnessApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var RestService
     */
    protected $service = null;

    /**
     * @var KBContentsUsefulnessApi
     */
    protected $api = null;

    /**
     * @var KBContents
     */
    protected $kbcontent;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new KBContentsUsefulnessApi();

        $this->kbcontent = BeanFactory::newBean('KBContents');
        $this->kbcontent->name = 'SugarKBContent' . time();
        $this->kbcontent->save();

        DBManagerFactory::getInstance()->commit();
    }

    public function tearDown()
    {
        DBManagerFactory::getInstance()
            ->query('DELETE FROM kbcontents WHERE id = \'' . $this->kbcontent->id . '\'');

        $this->service = null;
        $this->api = null;

        SugarTestHelper::tearDown();
    }

    /**
     * Test for votes useful.
     */
    public function testVoteUseful()
    {
        $this->assertEquals(0, $this->kbcontent->useful);
        for ($i = 1; $i <= 3; $i++) {
            $result = $this->api->voteUseful(
                $this->service,
                array(
                    'module' => 'KBContents',
                    'record' => $this->kbcontent->id
                )
            );

            $this->assertNotEmpty($result);

            $this->assertArrayHasKey('id', $result);
            $this->assertArrayHasKey('useful', $result);
            $this->assertArrayHasKey('notuseful', $result);

            $this->assertEquals($this->kbcontent->id, $result['id']);
            $this->assertEquals($i, $result['useful']);
            $this->assertEquals(0, $result['notuseful']);
        }
    }

    /**
     * Test for votes not useful
     */
    public function testVoteNotUseful()
    {
        $this->assertEquals(0, $this->kbcontent->useful);
        for ($i = 1; $i <= 3; $i++) {
            $result = $this->api->voteNotUseful(
                $this->service,
                array(
                    'module' => 'KBContents',
                    'record' => $this->kbcontent->id
                )
            );

            $this->assertNotEmpty($result);

            $this->assertArrayHasKey('id', $result);
            $this->assertArrayHasKey('useful', $result);
            $this->assertArrayHasKey('notuseful', $result);

            $this->assertEquals($this->kbcontent->id, $result['id']);
            $this->assertEquals($i, $result['notuseful']);
            $this->assertEquals(0, $result['useful']);
        }
    }

    /**
     * Data provider with useful/not useful
     *
     * @return array
     */
    public function dataProviderUsefulAndNotUseful()
    {
        return array(
            array(true), // useful
            array(false), // not useful
        );
    }

    /**
     * Test for votes when not specified module
     *
     * @dataProvider dataProviderUsefulAndNotUseful
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testVoteNotSpecifiedModule($isUseful)
    {
        $args = array(
            'record' => '123'
        );
        if ($isUseful) {
            $this->api->voteUseful($this->service, $args);
        } else {
            $this->api->voteNotUseful($this->service, $args);
        }
    }

    /**
     * Test for votes when not specified module
     *
     * @dataProvider dataProviderUsefulAndNotUseful
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testVoteNotSpecifiedRecord($isUseful)
    {
        $args = array(
            'module' => 'KBContents'
        );
        if ($isUseful) {
            $this->api->voteUseful($this->service, $args);
        } else {
            $this->api->voteNotUseful($this->service, $args);
        }
    }

    /**
     * Test for votes when record not found
     *
     * @dataProvider dataProviderUsefulAndNotUseful
     * @expectedException SugarApiExceptionNotFound
     */
    public function testVoteNotFoundRecord($isUseful)
    {
        $args = array(
            'module' => 'KBContents',
            'record' => 'some_id_123'
        );
        if ($isUseful) {
            $this->api->voteUseful($this->service, $args);
        } else {
            $this->api->voteNotUseful($this->service, $args);
        }
    }

    /**
     * Test for votes when record not authorized
     *
     * @dataProvider dataProviderUsefulAndNotUseful
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testVoteNotUsefulNotAuthorized($isUseful)
    {
        $beanMock = $this->getMockBuilder('KBContents')->setMethods(array('ACLAccess'))->getMock();
        $beanMock->expects($this->once())
            ->method('ACLAccess')
            ->will($this->returnValue(false));

        $apiMock = $this->getMockBuilder('KBContentsUsefulnessApi')->setMethods(array('loadBean'))->getMock();
        $apiMock->expects($this->once())
            ->method('loadBean')
            ->will(
                $this->returnCallback(
                    function () use ($beanMock) {
                        return $beanMock;
                    }
                )
            );

        $args = array(
            'module' => 'KBContents',
            'record' => $this->kbcontent->id
        );

        if ($isUseful) {
            $apiMock->voteUseful($this->service, $args);
        } else {
            $apiMock->voteNotUseful($this->service, $args);
        }
    }
}
