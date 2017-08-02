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




class TemplateDateTimeTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        global $timedate;
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        //Set the now on timedate correctly for consistent testing
        $now = $timedate->getNow(true)->setDate(2012,10,8)->setTime(16, 10);
        $timedate->setNow($now);

    }

    public function tearDown()
    {
        global $timedate;
        $timedate->setNow(new SugarDateTime());
        SugarTestHelper::tearDown();
    }

    public function testDefaultValues()
    {
        global $timedate;
        $tdt = new TemplateDatetimecombo();

        $fakeBean = new TemplateDateTimeMockBean();
        //Verify that each of the default values for TemplateDateTime modify the date correctly
        $expected = clone $timedate->getNow();
        //We have to make sure to run through parseDateDefault and set a time as on some versions of php,
        //setting the day will reset the time to midnight.
        //ex. in php 5.3.2 'next monday' will not change the time. In php 5.3.6 it will set the time to midnight
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['today'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);

        $expected->setDate(2012,10,7);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['yesterday'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);

        $expected->setDate(2012,10,9);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['tomorrow'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);


        $expected->setDate(2012,10,15);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['next week'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);


        $expected->setDate(2012,10,15);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['next monday'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);

        $expected->setDate(2012,10,12);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['next friday'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);

        $expected->setDate(2012,10,22);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['two weeks'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);

        $expected->setDate(2012,11,8);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['next month'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);


        $expected->setDate(2012,11,01);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['first day of next month'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);

        $expected->setDate(2013,01,8);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['three months'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);

        $expected->setDate(2013,04,8);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['six months'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);

        $expected->setDate(2013,10,8);
        $result = $fakeBean->parseDateDefault($tdt->dateStrings['next year'] . "&04:10pm", true);
        $this->assertEquals($timedate->asUser($expected), $result);
    }
}

class TemplateDateTimeMockBean extends SugarBean {


    public function parseDateDefault($value, $time = false) {
        return parent::parseDateDefault($value, $time);
    }
}


?>