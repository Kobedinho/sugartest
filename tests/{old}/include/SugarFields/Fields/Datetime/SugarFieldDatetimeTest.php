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

class SugarFieldDatetimeTest extends Sugar_PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('timedate');
        $GLOBALS['timedate']->allow_cache = false;
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @group export
     */
    public function testExportSanitize()
    {
        $timedate = TimeDate::getInstance();
        $db = DBManagerFactory::getInstance();

        $now = $timedate->getNow();
        $isoDate = $timedate->asIso($now);
        $dbDatetime = $timedate->asDb($now);

        $expectedTime = $timedate->to_display_date_time($db->fromConvert($dbDatetime, 'datetime'));
        $expectedTime = preg_replace('/([pm|PM|am|AM]+)/', ' \1', $expectedTime);

        $obj = BeanFactory::newBean('Opportunities');
        $obj->date_modified = $isoDate;

        $vardef = $obj->field_defs['date_modified'];

        $field = SugarFieldHandler::getSugarField('datetime');
        $value = $field->exportSanitize($obj->date_modified, $vardef, $obj);
        $this->assertEquals($expectedTime, $value);

        $obj->date_modified = $dbDatetime;
        $value = $field->exportSanitize($obj->date_modified, $vardef, $obj);
        $this->assertEquals($expectedTime, $value);
    }

    public function unformatDataProvider()
    {
        return array(
            array('Europe/Helsinki', '2013-08-05T08:15:30+02:00', '2013-08-05 06:15:30'),
            array('America/Boise', '2013-08-05T08:15:30-07:00', '2013-08-05 15:15:30'),
            array('America/New_York','2013-08-05T08:15:30','2013-08-05 12:15:30'),
            array('Europe/Minsk','2013-08-05T08:15:30+03:00','2013-08-05 05:15:30'),
            array('Antarctica/Vostok','2013-08-05T08:15:30','2013-08-05 02:15:30'),
        );
    }

    /**
     * @dataProvider unformatDataProvider
     **/
    public function testApiUnformatField($timeZone, $isoDate, $gmtResult)
    {
        $GLOBALS['current_user']->setPreference('timezone', $timeZone);
        $GLOBALS['current_user']->savePreferencesToDB();
        $GLOBALS['current_user']->reloadPreferences();

        $field = SugarFieldHandler::getSugarField('datetime');
        $this->assertEquals($gmtResult, $field->apiUnformatField($isoDate));
    }

    public function fixForFilterDataProvider()
    {
        return array(
            array('2013-08-29', '$equals', array('2013-08-29T00:00:00', '2013-08-29T23:59:59')),
            array('2013-08-29', '$lt', '2013-08-28T23:59:59'),
            array('2013-08-29', '$gt', '2013-08-30T00:00:00'),
            array(array('2013-08-19', '2013-08-29'), '$between', array('2013-08-19T00:00:00', '2013-08-29T23:59:59')),
            array('2013-08-29', '$daterange', '2013-08-29'),
        );
    }

    /**
     * @dataProvider fixForFilterDataProvider
     */
    public function testFixForFilter($date, $op, $fixedDate)
    {
        $field = SugarFieldHandler::getSugarField('datetime');
        $q = new SugarQuery();
        $w = new SugarQuery_Builder_Andwhere($q);
        $field->fixForFilter($date, 'date_entered', BeanFactory::newBean('Accounts'), $q, $w, $op);
        $this->assertEquals($fixedDate, $date);
    }

    public function providerApiSaveDateTest()
    {
        return array(
            array('2014-05-16T13:01:00Z'),
            array('2014-05-16')
        );
    }

    /**
     * @dataProvider providerApiSaveDateTest
     */
    public function testApiSaveDateTest($date)
    {
        /* @var $bean SugarBean */
        $bean = $this->createPartialMock('Opportunity', array('save'));

        $params = array(
            'test_c' => $date
        );

        /* @var $field SugarFieldDatetime */
        $field = SugarFieldHandler::getSugarField('datetime');

        $field->apiSave($bean, $params, 'test_c', array('type' => 'date'));

        $this->assertEquals('2014-05-16', $bean->test_c);
    }
}
