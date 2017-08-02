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

require_once 'tests/{old}/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/3_RemoveEmailWithPasswordInfo.php';

/**
 * Class RemoveEmailWithPasswordInfoTest to test for 3_RemoveEmailWithPasswordInfo.php upgrader script
 */
class RemoveEmailWithPasswordInfoTest extends UpgradeTestCase
{
    protected static $emailBeans = array();

    public function setUp()
    {
        parent::setUp();
        $this->upgrader->db = DBManagerFactory::getInstance();

        $pwdsettings = array('lostpasswordtmpl', 'generatepasswordtmpl');

        // create emails in old way
        foreach ($pwdsettings as $pwdsetting) {
            $removeEmailScript = new SugarUpgradeRemoveEmailWithPasswordInfo($this->upgrader);
            $subject = SugarTestReflection::callProtectedMethod(
                $removeEmailScript,
                'getEmailTemplateSubject',
                array($pwdsetting)
            );

            if (!empty($subject)) {
                // save the new email
                $email = new Email();
                $email->team_id = 1;
                $email->to_addrs = '';
                $email->type = 'archived';
                $email->deleted = '0';
                $email->name = $subject;
                $email->description = 'any thing goes';
                $email->description_html = 'any html stuff';
                $email->from_addr = '';
                $email->parent_type = 'User';
                $email->date_sent = TimeDate::getInstance()->nowDb();
                $email->modified_user_id = '1';
                $email->created_by = '1';
                $email->status = 'sent';
                $email->save();
                self::$emailBeans[] = $email;
            }
        }
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test run, make sure the email has been deleted correctly
     *
     * @dataProvider dataProviderTestRun
     *
     */
    public function testRun($fromVersion, $toVersion, $expected)
    {
        $this->upgrader->setVersions($fromVersion, 'ent', $toVersion, 'ent');
        $upgradeScript = new SugarUpgradeRemoveEmailWithPasswordInfo($this->upgrader);
        $upgradeScript->run();

        foreach (self::$emailBeans as $email) {
            BeanFactory::clearCache();
            $beanInDB = BeanFactory::getBean('Emails', $email->id);
            $this->assertEquals($expected, empty($beanInDB->id));
        }
    }

    public function dataProviderTestRun()
    {
        return array(
            // newer version, no deletion occurs
            array(
                '7.7.3.2',
                '7.8.3.2',
                false,
            ),
            // from old version to 7.7.2, deletion occurs
            array(
                '7.6.2.2',
                '7.7.2.0',
                true,
            ),
        );
    }
}
