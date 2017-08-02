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

require_once 'tests/{old}/SugarTestDatabaseMock.php';

class ForecastWorksheetsApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarTestDatabaseMock
     */
    protected $db;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->db = SugarTestHelper::setUp('mock_db');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function dataProviderFormatForApiSetsParentDeleted()
    {
        return array(
            array(0),
            array(1)
        );
    }

    /**
     *
     * @dataProvider dataProviderFormatForApiSetsParentDeleted
     *
     * @param $parent_deleted
     */
    public function testFormatForApiSetsParentDeleted($parent_deleted)
    {
        $this->markTestIncomplete('[BR-3362] Query Spy doesn\'t capture parameters in prepared statements');

        $product_name = 'My Test Product';
        $product_id = 'my_test_id';
        if ($parent_deleted === 0) {
            $this->db->addQuerySpy(
                'product_delete',
                array('/products.deleted = 0/', '/products.id = \'' . $product_id . '\'/'),
                array(
                    array(
                        'id' => $product_id,
                        'name' => $product_name,
                        'deleted' => $parent_deleted
                    ),
                )
            );
        }

        /* @var $forecast_worksheet ForecastWorksheet */
        $forecast_worksheet = BeanFactory::newBean('ForecastWorksheets');
        $forecast_worksheet->parent_type = 'Products';
        $forecast_worksheet->parent_id = $product_id;
        $forecast_worksheet->name = 'Test Product';

        $api_helper = new ForecastWorksheetsApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $bean = $api_helper->formatForApi($forecast_worksheet);

        $this->assertEquals($parent_deleted, $bean['parent_deleted']);

        unset($api_helper);
        unset($forecast_worksheet);
    }
}
