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


class PersonTemplateTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_bean;

    public function setUp()
    {
        // Can't use Person since Localization needs actual bean
        $this->_bean = $this->getMockBuilder('Contact')
            ->setMethods(array('getVCalData'))
            ->getMock();
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('files');
    }

    public function tearDown()
    {
        BeanFactory::setBeanClass('vCals');
        unset($this->_bean);
        SugarTestHelper::tearDown();
    }

    public function testNameIsReturnedAsSummaryText()
    {
        $GLOBALS['current_user']->setPreference('default_locale_name_format', 'l f');

        $this->_bean->first_name = 'Test';
        $this->_bean->last_name = 'Contact';
        $this->_bean->title = '';
        $this->_bean->salutation = '';
        $this->assertEquals('Contact Test', $this->_bean->get_summary_text());
    }

    /**
     * @ticket 38648
     */
    public function testNameIsReturnedAsSummaryTextWhenSalutationIsInvalid()
    {
        $GLOBALS['current_user']->setPreference('default_locale_name_format', 's l f');

        $this->_bean->salutation = 'Tester';
        $this->_bean->first_name = 'Test';
        $this->_bean->last_name = 'Contact';
        $this->_bean->title = '';
        $this->assertEquals('Tester Contact Test', $this->_bean->get_summary_text());
    }

    public function testCustomPersonTemplateFound()
    {
        // write out a custom Person File
        mkdir_recursive("custom/include/SugarObjects/templates/person/");
        SugarTestHelper::saveFile("custom/include/SugarObjects/templates/person/vardefs.php");
        SugarAutoLoader::put("custom/include/SugarObjects/templates/person/vardefs.php", file_get_contents("tests/{old}/include/SugarObjects/templates/test-vardefs/person-vardef.php"));
        VardefManager::addTemplate('Contacts', 'Contact', 'person', false);
        $this->assertArrayHasKey('customField', $GLOBALS['dictionary']['Contact']['fields']);

    }

    public function testPerson_GetFreeBusySchedule_ReturnsStartEndTimesArray()
    {
        global $timedate;
        $vcalFormat = 'Ymd\THis\Z';

        $expectedStartTime = '2014-12-25T13:30:00+00:00';
        $expectedEndTime = '2014-12-25T14:30:00+00:00';

        $sugarDateTime = $timedate->fromIso($expectedStartTime);
        $vcalStartTime = $sugarDateTime->format($vcalFormat);
        $sugarDateTime = $timedate->fromIso($expectedEndTime);
        $vcalEndTime   = $sugarDateTime->format($vcalFormat);

        $vcalData = array(
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//SugarCRM//SugarCRM Calendar//EN',
            'BEGIN:VFREEBUSY',
            'ORGANIZER;CN=Sally Bronsen:sally@example.com',
            'DTSTART:2014-08-11 00:00:00',
            'DTEND:2014-10-11 00:00:00',
            "FREEBUSY:{$vcalStartTime}/{$vcalEndTime}",
            'DTSTAMP:2014-08-12 20:34:26',
            'END:VFREEBUSY',
            'END:VCALENDAR'
        );

        $this->_bean->expects($this->once())
            ->method('getVCalData')
            ->will($this->returnValue($vcalData));

        $result = $this->_bean->getFreeBusySchedule();

        $this->assertEquals(1, count($result), 'Unexpected number of Start/End times from getFreeBusySchedule()');
        $this->assertEquals($expectedStartTime, $result[0]['start'], 'Unexpected Start time from getFreeBusySchedule()');
        $this->assertEquals($expectedEndTime, $result[0]['end'], 'Unexpected Start time from getFreeBusySchedule()');
    }
}
