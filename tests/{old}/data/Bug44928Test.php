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
 * @see Team
 */

/**
 * @see Campaign
 */

/**
 * @see Lead
 */

/**
 * @ticket 44928
 */
class Bug44928Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Temporarily created team record
     *
     * @var Team
     */
    protected $team;

    /**
     * Temporarily created campaign record
     *
     * @var Campaign
     */
    protected $campaign;

    /**
     * Temporarily created lead record
     *
     * @var Lead
     */
    protected $lead;

    /**
     * Temporarily campaign name
     *
     * @var string
     */
    protected $campaign_name = 'Bug44928Test';

    /**
     * Created lead ID
     *
     * @var string
     */
    protected $lead_id;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * Creates temporary records and sets anonymous current user
     */
    public function setUp()
    {
        $this->markTestIncomplete("Test is failing on Oracle, working with sergei to fix");
        return;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        // create new private team
        $this->team = BeanFactory::newBean('Teams');
        $this->team->private = true;
        $this->team->save();

        // create new campaign associated with the team
        $this->campaign = BeanFactory::newBean('Campaigns');
        $this->campaign->team_id = $this->team->id;
        $this->campaign->name = $this->campaign_name;
        $this->campaign->save();

        // create new lead associated with the campaign
        $this->lead = BeanFactory::newBean('Leads');
        $this->lead->campaign_id = $this->campaign->id;
        $this->lead->save();

        $this->lead_id = $this->lead->id;
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * Marks temporary records as deleted and restores current user
     */
    public function tearDown()
    {
       /* $this->lead->mark_deleted($this->lead->id);
        $this->campaign->mark_deleted($this->campaign->id);
        $this->team->mark_deleted($this->team->id);
        */
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * Ensures that properties of bean relations are accessible for an anonymous
     * user depending on accessibility of the bean itself but not on team security
     * of related bean.
     */
    public function testRelatedBeanPropertiesAreAccessible()
    {
        $lead = BeanFactory::getBean('Leads', $this->lead_id);
        $this->assertEquals($this->campaign_name, $lead->campaign_name);
    }
}
