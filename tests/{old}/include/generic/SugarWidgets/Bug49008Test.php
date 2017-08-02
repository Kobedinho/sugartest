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


class Bug49008Test extends PHPUnit_Framework_TestCase
{
    var $sugarWidgetField;

    public function setUp()
    {
        $layoutManager = new LayoutManager();
        $this->sugarWidgetField = new SugarWidgetFieldDateTime49008Mock($layoutManager);
        global $current_user, $timedate;
        $timedate = TimeDate::getInstance();
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->setPreference('timezone', 'America/Los_Angeles');
        $current_user->save();
        $current_user->db->commit();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     *
     */
    public function testExpandDateLosAngeles()
    {
        $start = $this->sugarWidgetField->expandDate('2011-12-17');
        $this->assertRegExp('/\:00\:00/',  $start->asDb(), 'Assert for expandDate without end set, we use 00:00:00');
        $end = $this->sugarWidgetField->expandDate('2011-12-18', true);
        $this->assertRegExp('/\:59\:59/', $end->asDb(), 'Assert for expandDate with end set to true we use 23:59:59');
    }
}

class SugarWidgetFieldDateTime49008Mock extends SugarWidgetFieldDateTime
{
     public function expandDate($date, $end=false) {
         return parent::expandDate($date, $end);
     }
}

?>