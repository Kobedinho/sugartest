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
require_once 'upgrade/scripts/post/3_FixEmailTemplates.php';

/**
 * Class FixEmailTemplatesTest test for SugarUpgradeFixEmailTemplates upgrader script
 */
class FixEmailTemplatesTest extends UpgradeTestCase
{
    /**
     * @var array
     */
    protected $templateIds = array();

    public function setUp()
    {
        parent::setUp();
        $this->upgrader->db = DBManagerFactory::getInstance();
    }

    public function tearDown()
    {
        parent::tearDown();

        if (!empty($this->templateIds)) {
            $db = DBManagerFactory::getInstance();
            $db->query("DELETE FROM email_templates WHERE id IN ('" . implode("', '", $this->templateIds) . "')");
        }
        $this->templateIds = array();
    }

    /**
     * Data provider for test upgrade lost password email template.
     *
     * @return array
     */
    public function dataProviderHandleLostPasswordTemplate()
    {
        return array(
            // empty email template id
            array(
                '',
                '',
                '',
            ),
            // some id with empty body html
            array(
                '3214234534141',
                '',
                '',
            ),
            // id with html without $config_site_url
            array(
                '543453453',
                'some <div>html <p>here</p> without</div> link',
                'some <div>html <p>here</p> without</div> link',
            ),
            // id with html with $config_site_url
            array(
                '454325345',
                'some <div>html <p>$config_site_url</p> link',
                'some <div>html <p><a title="$config_site_url" href="$config_site_url">$config_site_url</a></p> link',
            ),
            // id with html with $config_site_url which already updated
            array(
                '4354yt546',
                'some <div>html <p><a title="$config_site_url" href="$config_site_url">$config_site_url</a></p> link',
                'some <div>html <p><a title="$config_site_url" href="$config_site_url">$config_site_url</a></p> link',
            ),
        );
    }

    /**
     * Test upgrade lost password email template.
     *
     * @dataProvider dataProviderHandleLostPasswordTemplate
     *
     * @param string $tplId
     * @param string $bodyHtml
     * @param string $expectBodyHtml
     */
    public function testHandleLostPasswordTemplate($tplId, $bodyHtml, $expectBodyHtml)
    {
        $mockUpgrade = $this->getMockBuilder('SugarUpgradeFixEmailTemplates')->setMethods(array(
                'getEmailTemplateBodyHtml',
                'updateEmailTemplateBodyHtml'
            ))->setConstructorArgs(array($this->upgrader))->getMock();

        $this->upgrader->config['passwordsetting']['lostpasswordtmpl'] = $tplId;

        if (empty($tplId)) {
            $mockUpgrade->expects($this->never())->method('getEmailTemplateBodyHtml');
            $mockUpgrade->expects($this->never())->method('updateEmailTemplateBodyHtml');
        } else {
            $mockUpgrade
                ->expects($this->once())
                ->method('getEmailTemplateBodyHtml')
                ->with($this->equalTo($tplId))
                ->will($this->returnValue($bodyHtml));

            if (empty($bodyHtml) || $bodyHtml == $expectBodyHtml) {
                $mockUpgrade->expects($this->never())->method('updateEmailTemplateBodyHtml');
            } else {
                $mockUpgrade
                    ->expects($this->once())
                    ->method('updateEmailTemplateBodyHtml')
                    ->with($this->equalTo($tplId), $this->equalTo($expectBodyHtml))
                    ->will($this->returnValue(true));
            }
        }

        SugarTestReflection::callProtectedMethod($mockUpgrade, 'handleLostPasswordTemplate');
    }

    /**
     * Data provider for test upgrade generate password email template.
     *
     * @return array
     */
    public function dataProviderHandleGeneratePasswordTemplate()
    {
        return array(
            // empty email template id
            array(
                '',
                '',
                '',
            ),
            // some id with empty body html
            array(
                '3214234534141',
                '',
                '',
            ),
            // id with html without $config_site_url
            array(
                '543453453',
                'some <div>html <p>here</p> without</div> link',
                'some <div>html <p>here</p> without</div> link',
            ),
            // id with html with $config_site_url
            array(
                '454325345',
                'some <div>html <p> $contact_user_link_guid </p> link',
                'some <div>html <p> <a title="$contact_user_link_guid" href="$contact_user_link_guid">$contact_user_link_guid</a> </p> link',
            ),
            // id with html with $config_site_url which already updated
            array(
                '4354yt546',
                'some <div>html <p> <a title="$contact_user_link_guid" href="$contact_user_link_guid">$contact_user_link_guid</a> </p> link',
                'some <div>html <p> <a title="$contact_user_link_guid" href="$contact_user_link_guid">$contact_user_link_guid</a> </p> link',
            ),
        );
    }


    /**
     * Test upgrade generate password email template.
     *
     * @dataProvider dataProviderHandleGeneratePasswordTemplate
     *
     * @param string $tplId
     * @param string $bodyHtml
     * @param string $expectBodyHtml
     */
    public function testHandleGeneratePasswordTemplate($tplId, $bodyHtml, $expectBodyHtml)
    {
        $mockUpgrade = $this->getMockBuilder('SugarUpgradeFixEmailTemplates')->setMethods(array(
                'getEmailTemplateBodyHtml',
                'updateEmailTemplateBodyHtml'
            ))->setConstructorArgs(array($this->upgrader))->getMock();

        $this->upgrader->config['passwordsetting']['generatepasswordtmpl'] = $tplId;

        if (empty($tplId)) {
            $mockUpgrade->expects($this->never())->method('getEmailTemplateBodyHtml');
            $mockUpgrade->expects($this->never())->method('updateEmailTemplateBodyHtml');
        } else {
            $mockUpgrade
                ->expects($this->once())
                ->method('getEmailTemplateBodyHtml')
                ->with($this->equalTo($tplId))
                ->will($this->returnValue($bodyHtml));

            if (empty($bodyHtml) || $bodyHtml == $expectBodyHtml) {
                $mockUpgrade->expects($this->never())->method('updateEmailTemplateBodyHtml');
            } else {
                $mockUpgrade
                    ->expects($this->once())
                    ->method('updateEmailTemplateBodyHtml')
                    ->with($this->equalTo($tplId), $this->equalTo($expectBodyHtml))
                    ->will($this->returnValue(true));
            }
        }

        SugarTestReflection::callProtectedMethod($mockUpgrade, 'handleGeneratePasswordTemplate');
    }

    /**
     * Data provider for test get html body of email template from db.
     *
     * @return array
     */
    public function dataProviderGetEmailTemplateBodyHtml()
    {
        return array(
            // when template not exists in db
            array(
                false,
                'some <p>html</p> here',
                false
            ),
            // when template exists in db
            array(
                true,
                'some <p>html</p> here',
                htmlspecialchars('some <p>html</p> here', ENT_QUOTES),
            ),
        );
    }

    /**
     * Test get html body of email template from db.
     *
     * @dataProvider dataProviderGetEmailTemplateBodyHtml
     *
     * @param boolean $create
     * @param string $bodyHtml
     * @param string|boolean $expected
     */
    public function testGetEmailTemplateBodyHtml($create, $bodyHtml, $expected)
    {
        $id = $create? $this->createEmailTemplate($bodyHtml) : '';
        $upgradeScript = new SugarUpgradeFixEmailTemplates($this->upgrader);
        $actual = SugarTestReflection::callProtectedMethod(
            $upgradeScript,
            'getEmailTemplateBodyHtml',
            array($id)
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider for test update html body of email template in db.
     *
     * @return array
     */
    public function dataProviderUpdateEmailTemplateBodyHtml()
    {
        return array(
            // when template not exists in db
            array(
                false,
                'some <p>html</p> here',
                'some <p>html <a href="home">updated</a></p> here',
                false
            ),
            // when template exists in db
            array(
                true,
                'some <p>html</p> here',
                'some <p>html <a href="home">updated</a></p> here',
                htmlspecialchars('some <p>html <a href="home">updated</a></p> here', ENT_QUOTES),
            ),
        );
    }

    /**
     * Test update body html of email template in db.
     *
     * @dataProvider dataProviderUpdateEmailTemplateBodyHtml
     *
     * @param boolean $create
     * @param string $bodyHtml
     * @param string $bodyHtmlUpdate
     * @param string|boolean $expected
     */
    public function testUpdateEmailTemplateBodyHtml($create, $bodyHtml, $bodyHtmlUpdate, $expected)
    {
        $id = $create? $this->createEmailTemplate($bodyHtml) : '';
        $upgradeScript = new SugarUpgradeFixEmailTemplates($this->upgrader);
        $result = SugarTestReflection::callProtectedMethod(
            $upgradeScript,
            'updateEmailTemplateBodyHtml',
            array($id, $bodyHtmlUpdate)
        );

        $this->assertTrue($result);

        $actual = SugarTestReflection::callProtectedMethod(
            $upgradeScript,
            'getEmailTemplateBodyHtml',
            array($id)
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * Create the email template in db.
     *
     * @param string $bodyHtml
     * @return string
     */
    private function createEmailTemplate($bodyHtml)
    {
        global $current_user;

        $emailTpl = new EmailTemplate();
        $emailTpl->name = 'Password Email' . rand(0, PHP_INT_MAX);
        $emailTpl->assigned_user_id = $current_user->id;
        $emailTpl->body_html = $bodyHtml;
        $emailTpl->body = 'some text';
        $emailTpl->team_id = $current_user->team_id;
        $emailTpl->team_set_id = $current_user->team_id;

        $id = $emailTpl->save();
        $this->templateIds[] = $id;

        return $id;
    }

    /**
     * Test run upgrade script.
     */
    public function testRun()
    {
        $mockUpgrade = $this->getMockBuilder('SugarUpgradeFixEmailTemplates')->setMethods(array(
                'handleGeneratePasswordTemplate',
                'handleLostPasswordTemplate'
            ))->setConstructorArgs(array($this->upgrader))->getMock();

        $mockUpgrade->expects($this->once())->method('handleGeneratePasswordTemplate');
        $mockUpgrade->expects($this->once())->method('handleLostPasswordTemplate');

        $mockUpgrade->run();
    }
}
