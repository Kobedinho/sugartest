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

class DateExpressionTest extends Sugar_PHPUnit_Framework_TestCase
{
    static $createdBeans = array();

	public static function setUpBeforeClass()
	{
        parent::setUpBeforeClass();
        SugarTestHelper::setUp("current_user");
        $GLOBALS['current_user']->setPreference('timezone', "America/Los_Angeles");
	    $GLOBALS['current_user']->setPreference('datef', "m/d/Y");
		$GLOBALS['current_user']->setPreference('timef', "h.iA");
		unset($GLOBALS['disable_date_format']);
	}

	public static function tearDownAfterClass()
	{
	    foreach(self::$createdBeans as $bean)
        {
            $bean->mark_deleted($bean->id);
        }
        parent::tearDownAfterClass();
	}

	public function testAddDays()
	{
	    $task = new Task();
	    $task->date_due = '2001-01-01 11:45:00';
        $expr = 'addDays($date_due, 7)';
        $result = Parser::evaluate($expr, $task)->evaluate();
	    $this->assertInstanceOf("DateTime", $result);
	    $expect = TimeDate::getInstance()->fromDb('2001-01-01 11:45:00')->get('+ 7 days')->asDb();
        $this->assertEquals($expect, TimeDate::getInstance()->asDb($result));
	}

	public function testDayOfWeek()
	{
	    $task = new Task();
	    $task->date_due = '2011-01-10 01:00:00'; // this is Monday in GMT but Sunday in PST
	    $expr = 'dayofweek($date_due)';
	    $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals(0, $result);

        $task->date_due = '2011-01-10 21:00:00'; // this is Monday in both timezones
	    $expr = 'dayofweek($date_due)';
	    $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals(1, $result);
	}

	public function testMonthOfYear()
	{
	    $task = new Task();
	    $task->date_due = '2011-01-09 21:00:00';
	    $expr = 'monthofyear($date_due)';
	    $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals(1, $result);

        $task->date_due = '2011-03-01 01:00:00'; // this is February in PST
	    $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals(2, $result);
	}

	public function testDefineDate()
	{
	    $task = new Task();
	    $expr = 'date($name)';
	    $timedate = TimeDate::getInstance();

	    $task->name = '3/18/2011';
	    $result = Parser::evaluate($expr, $task)->evaluate();
	    $this->assertInstanceOf("DateTime", $result);
	    $this->assertEquals($timedate->asUserDate($timedate->fromUserDate('3/18/2011')), $timedate->asUserDate($result));

	}

	public function testNow()
	{
	    $task = new Task();
	    $expr = 'now()';
	    $result = Parser::evaluate($expr, $task)->evaluate();
	    $this->assertInstanceOf("DateTime", $result);
	    $this->assertEquals(TimeDate::getInstance()->getNow(true)->format('r'), $result->format('r'));
	}

	public function testToday()
	{
	    $task = new Task();
	    $expr = 'today()';
	    $result = Parser::evaluate($expr, $task)->evaluate();
	    $this->assertInstanceOf("DateTime", $result);
	    $this->assertEquals(TimeDate::getInstance()->getNow(true)->format('Y-m-d'), $result->format('Y-m-d'));
	}

    public function daysUntilGenerator()
    {
        $test_cases = array(
            array("+5 days",5),
            array("-1 day",-1),
            array("yesterday",-1), // trigger a 1 day change and set the time to be midnight yesterday
            array("yesterday -1 minute",-2), // corner case - trigger a 2 day change to be 11:59:00
            array("+1 day",1),
            array("tomorrow",1), // trigger a 1 day change and set the time to be midnight tomorrow
            array("tomorrow -1 minute",0), // corner case - trigger a day change back to today
            array("-5 days",-5),
        );

        $hours = range(0, 23, 1);

        $results = array();

        foreach($hours as $hour) {
            foreach($test_cases as $test) {
                $results[] = array_merge($test, array('hour' => $hour));
            }
        }

        return $results;
    }

    /**
     * @dataProvider daysUntilGenerator
     */
    public function testDaysUntil($input, $expected, $hour)
	{
        $task = new Task();
        $timedate = TimeDate::getInstance();

        $date = $timedate->asUser($timedate->getNow(true)->setTime($hour, 0, 0)->get($input));
        $task->date_due = $date;

        $expr = 'daysUntil($date_due)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($expected, $result);
	}


    /**
     * @dataProvider hoursUntilProvider
     */
    public function testHoursUntil($date, $now, $expected)
    {
        global $timedate;

        $now = $timedate->fromString($now);
        $timedate->setNow($now);

        // manually convert string to object in order to avoid dependency
        // on user-preferred date format
        $date = $timedate->fromString($date);
        $params = new StringLiteralExpression(array($date));
        $expr = new HoursUntilExpression(array($params));
        $actual = $expr->evaluate();

        $this->assertEquals($expected, $actual);
    }

    public static function hoursUntilProvider()
    {
        return array(
            // one hour difference
            array(
                '2014-06-10 14:17:45',
                '2014-06-10 13:17:45',
                1
            ),
            // value is rounded down
            array(
                '2014-06-10 16:17:00',
                '2014-06-10 13:17:45',
                2
            ),
            // value is rounded down by modulus
            array(
                '2014-06-10 13:17:45',
                '2014-06-10 16:17:00',
                -2
            ),
        );
    }

	public function testBeforeAfter()
	{
	    $task = new Task();
	    $task->date_start = '2011-01-01 21:00:00';
	    $task->date_due = '2011-01-09 01:00:00';

	    $expr = 'isBefore($date_start, $date_due)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($result, "true");

        $expr = 'isAfter($date_start, $date_due)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($result, "false");

        $expr = 'isBefore($date_due, $date_start)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($result, "false");

        $expr = 'isAfter($date_due, $date_start)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($result, "true");
	}

	public function testIsValidDate()
	{
	    $task = new Task();
	    $timedate = TimeDate::getInstance();
	    $task->name = $timedate->to_display_date_time('2011-01-01 21:00:00');
	    $expr = 'isValidDate($name)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($result, "true");

        $task->name = '42';
	    $expr = 'isValidDate($name)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($result, "false");

        $task->name = 'Chuck Norris';
	    $expr = 'isValidDate($name)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($result, "false");

        $task->name = '2011-01-01 21:00:00';
	    $expr = 'isValidDate($name)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertEquals($result, "false");
	}

	public function testBadDates()
	{
	    $task = new Task();
        $task->name = 'Chuck Norris';
	    $expr = 'date($name)';
        try {
            $result = Parser::evaluate($expr, $task)->evaluate();
	        $this->assertTrue(false, "Incorrecty converted '{$task->name }' to date $result");
        } catch (Exception $e){
            $this->assertContains("invalid value to date", $e->getMessage());
        }
        $task->date_due = 'Chuck Norris';
	    $expr = 'addDays($date_due, 3)';
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertFalse($result, "Incorrecty converted '{$task->date_due }' to date $result");

	    $expr = 'addDays($date_start, 3)'; // not setting the value
        $result = Parser::evaluate($expr, $task)->evaluate();
        $this->assertFalse($result, "Incorrecty converted empty string to date $result");
	}

	/**
	 * Test autoconverting strings to dates
	 */
	public function testConvert()
	{
	    $task = new Task();
	    $timedate = TimeDate::getInstance();
	    $now = $timedate->getNow();
	    $task->name = $timedate->asUser($now);
	    $expr = 'addDays($name, 3)';
	    $result = Parser::evaluate($expr, $task)->evaluate();
	    $this->assertInstanceOf("DateTime", $result);
	    $this->assertEquals($timedate->asUser($timedate->getNow(true)->get("+3 days")), $timedate->asUser($result));
	}

    /**
     * @group bug57900
     * @return array expressions to test
     */
    public function providerUserDateExpressions()
    {
        return array(
            array('$date_entered'),
            // this doesn't give the correct date at all times, due to implicit interpretation of date() as UTC, which when converted to
            // local timezone could give a date +-1 day from now.
            //array('date(subStr(toString($date_entered),0,10))')
        );
    }

    /**
     * Test Format of DateTime Field
     * @param string $expr Expression to test
     * @dataProvider providerUserDateExpressions
     * @group bug57900
     */
    public function testUserDefinedDateTimeVar($expr)
    {
        $opp = new Opportunity();
        $timedate = TimeDate::getInstance();
        $now = $timedate->asUser($timedate->getNow());
        $opp->date_entered = $now;

        try {
            $result = Parser::evaluate($expr, $opp)->evaluate();
        }
        catch (Exception $e)
        {
            $this->fail('Failed to evaluate user datetime - threw exception');
        }
        $this->assertInstanceOf("DateTime",$result,'Evaluation did not return a DateTime object');
        $this->assertEquals($now, $timedate->asUser($result), 'The time is not what expected');
    }

    /**
     * @dataProvider roundTimeProvider
     */
    public function testRoundTime($minutes, $expected)
    {
        $date = new DateTime('today 00:' . $minutes);
        $rounded = DateExpression::roundTime($date);
        $actual = $rounded->format('H:i');

        $this->assertEquals($expected, $actual);
    }

    public function roundTimeProvider()
    {
        return array(
            array(0, '00:00'),
            array(8, '00:15'),
            array(23, '00:30'),
            array(38, '00:45'),
            array(53, '01:00'),
        );
    }
}
