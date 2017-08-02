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


class RS798Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var KBContentMock
     */
    protected $bean;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, true));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('KBContents'));
        $this->bean = SugarTestKBContentUtilities::createBean();
    }

    public function tearDown()
    {
        SugarTestKBContentUtilities::removeAllCreatedBeans();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * Approver for in review, assigned_user_id for rest.
     */
    public function testNotificationRecipient()
    {
        $approvedUser = SugarTestUserUtilities::createAnonymousUser();
        $assignedUser = SugarTestUserUtilities::createAnonymousUser();

        $this->bean->kbsapprover_id = $approvedUser->id;
        $this->bean->status = KBContent::ST_IN_REVIEW;
        $res = $this->bean->get_notification_recipients();
        $this->assertEquals($approvedUser->id, $res[0]->id);

        $this->bean->assigned_user_id = $assignedUser->id;
        $this->bean->status = KBContent::ST_PUBLISHED;
        $res = $this->bean->get_notification_recipients();
        $this->assertEquals($assignedUser->id, $res[0]->id);
    }

    /**
     * Send notifications according to status.
     * @dataProvider dataProviderForCheckNotifyStatus
     */
    public function testCheckNotifyStatus($data)
    {
        $user = SugarTestUserUtilities::createAnonymousUser();

        $bean = SugarTestKBContentUtilities::createBean();
        if ($data['before']) {
            $bean->status = $data['before'];
            $bean->save();
            // Fill data changes.
            $bean->retrieve();
        } else {
            $bean->new_with_id = true;
        }

        $bean->status = $data['after'];

        if (isset($data['setUser']) && $data['setUser']) {
            if ($data['after'] == KBContent::ST_IN_REVIEW) {
                $bean->kbsapprover_id = $user->id;
            } else {
                $bean->assigned_user_id = $user->id;
            }
        }

        $notify = ApiHelper::getHelper(new RestService(), $bean)->checkNotify($bean);

        $this->assertEquals($data['notify'], $notify);
    }

    /**
     * Data Provider for testCheckNotifyStatus
     *
     * @return array
     */
    public function dataProviderForCheckNotifyStatus()
    {
        return array(
            array(
                array(
                    'setUser' => false,
                    'before' => null,
                    'after' => KBContent::ST_IN_REVIEW,
                    'notify' => false,
                ),
            ),
            array(
                array(
                    'setUser' => true,
                    'before' => null,
                    'after' => KBContent::ST_IN_REVIEW,
                    'notify' => true,
                ),
            ),

            array(
                array(
                    'setUser' => false,
                    'before' => null,
                    'after' => KBContent::ST_PUBLISHED,
                    'notify' => false,
                ),
            ),
            array(
                array(
                    'setUser' => true,
                    'before' => null,
                    'after' => KBContent::ST_PUBLISHED,
                    'notify' => true,
                ),
            ),

            array(
                array(
                    'setUser' => false,
                    'before' => KBContent::ST_DRAFT,
                    'after' => KBContent::ST_IN_REVIEW,
                    'notify' => false,
                ),
            ),
            array(
                array(
                    'setUser' => true,
                    'before' => KBContent::ST_DRAFT,
                    'after' => KBContent::ST_IN_REVIEW,
                    'notify' => true,
                ),
            ),

            array(
                array(
                    'setUser' => false,
                    'before' => KBContent::ST_IN_REVIEW,
                    'after' => KBContent::ST_DRAFT,
                    'notify' => false,
                ),
            ),
            array(
                array(
                    'setUser' => true,
                    'before' => KBContent::ST_IN_REVIEW,
                    'after' => KBContent::ST_DRAFT,
                    'notify' => true,
                ),
            ),

            array(
                array(
                    'before' => KBContent::ST_IN_REVIEW,
                    'after' => KBContent::ST_IN_REVIEW,
                    'notify' => false,
                ),
            ),
            array(
                array(
                    'before' => null,
                    'after' => KBContent::ST_DRAFT,
                    'notify' => false,
                ),
            ),
        );
    }
}
