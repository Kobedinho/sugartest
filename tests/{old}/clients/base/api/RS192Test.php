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
 * RS192: Prepare Export Api.
 */
class RS192Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarApi
     */
    protected $api;

    /**
     * @var SugarApi
     */
    protected $recordList;

    /**
     * @var bool;
     */
    protected static $encode;

    /**
     * @var string
     */
    protected $listId;

    /**
     * @var array
     */
    protected $records;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        self::$encode = DBManagerFactory::getInstance()->getEncode();
        DBManagerFactory::getInstance()->setEncode(false);
        SugarTestHelper::setUp('current_user', array(true, false));
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        DBManagerFactory::getInstance()->setEncode(self::$encode);
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        parent::setUp();

        $this->api = new ExportApi();
        $this->recordList = new RecordListApi();
        $this->records = array();
        $account = SugarTestAccountUtilities::createAccount();
        array_push($this->records, $account->id);
        $account = SugarTestAccountUtilities::createAccount();
        array_push($this->records, $account->id);
        SugarTestAccountUtilities::createAccount();
    }

    protected function tearDown()
    {
        $this->recordList->recordListDelete(
            SugarTestRestUtilities::getRestServiceMock(),
            array('module' => 'Accounts', 'record_list_id' => $this->listId)
        );
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        parent::tearDown();
    }

    public function testExactExport()
    {
        $result = $this->recordList->recordListCreate(
            SugarTestRestUtilities::getRestServiceMock(),
            array('module' => 'Accounts', 'records' => $this->records)
        );
        $this->listId = $result['id'];
        $strCount = $this->getExportStringCount($this->listId);
        $this->assertEquals(3, $strCount);
    }

    public function testAllExport()
    {
        $result = $this->recordList->recordListCreate(
            SugarTestRestUtilities::getRestServiceMock(),
            array('module' => 'Accounts', 'records' => array())
        );
        $this->listId = $result['id'];
        $strCount = $this->getExportStringCount($this->listId);
        $this->assertGreaterThan(3, $strCount);
    }

    protected function getExportStringCount($listId)
    {
        $result = $this->api->export(
            SugarTestRestUtilities::getRestServiceMock(),
            array('module' => 'Accounts', 'record_list_id' => $listId)
        );
        $cnt = 0;
        foreach (explode("\r\n", $result) as $str) {
            if (!empty($str)) {
                $cnt ++;
            }
        }
        return $cnt;
    }
}
