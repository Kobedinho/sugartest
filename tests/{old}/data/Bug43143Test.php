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
 
class Bug43143Test extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

	public static function tearDownAfterClass()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}

	public function setUp()
	{
	    $this->bean = new Opportunity();
	    $this->defs = $this->bean->field_defs;
	    $this->timedate = $GLOBALS['timedate'];
	}

	public function tearDown()
	{
	    $this->bean->field_defs = $this->defs;
        $GLOBALS['timedate']->clearCache();
	}

	public function defaultDates()
	{
	    return array(
	        array('-1 day', '2010-12-31'),
	        array('now', '2011-01-01'),
	        array('+1 day', '2011-01-02'),
	        array('+1 week', '2011-01-08'),
	        array('next monday', '2011-01-03'),
	        array('next friday', '2011-01-07'),
	        array('+2 weeks', '2011-01-15'),
	        array('+1 month', '2011-02-01'),
	        array('first day of next month', '2011-02-01'),
	        array('+3 months', '2011-04-01'),
	        array('+6 months', '2011-07-01'),
	        array('+1 year', '2012-01-01'),
            array('first day of this month', '2011-01-01'),
            array('last day of this month', '2011-01-31'),
            array('last day of next month', '2011-02-28'),
	        );
	}

	/**
	 * @dataProvider defaultDates
	 * @param string $default
	 * @param string $value
	 */
	public function testDefaults($default, $value)
	{
        $this->timedate->allow_cache = true;
        $this->timedate->setNow($this->timedate->fromDb('2011-01-01 00:00:00'));
	    $this->bean->field_defs['date_closed']['display_default'] = $default;
	    $this->bean->populateDefaultValues(true);
	    $this->assertEquals($value, $this->timedate->to_db_date($this->bean->date_closed));
	}

    /*
     * @group bug43143
     */
    public function testUnpopulateData()
    {
        $this->bean->field_defs['date_closed']['display_default'] = 'next friday';
	    $this->bean->populateDefaultValues(true);
        $this->assertNotNull($this->bean->date_closed);
        $this->bean->unPopulateDefaultValues();
        $this->assertNull($this->bean->name);
        $this->assertNull($this->bean->date_closed);
    }
}
