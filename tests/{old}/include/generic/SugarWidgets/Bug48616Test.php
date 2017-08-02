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


class Bug48616Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('timedate');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Check if expandDate works properly when 'Today' macro is passed
     * instead of a date string
     */
    public function testExpandDateToday()
    {
        global $timedate;
        $layoutManager = new LayoutManager();
        $widget = new SugarWidgetFieldDateTime($layoutManager);

        $result = SugarTestReflection::callProtectedMethod($widget, 'expandDate', array('Today'));

        $this->assertContains(
            $timedate->asDbDate($timedate->getNow(true)),
            $result,
            "'Today' macro was not processed properly by expandDate()"
        );
    }
}
