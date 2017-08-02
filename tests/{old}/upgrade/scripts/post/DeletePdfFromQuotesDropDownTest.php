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

require_once "tests/{old}/upgrade/UpgradeTestCase.php";
require_once "upgrade/scripts/post/7_DeletePdfFromQuotesDropDown.php";

/**
 * Class DeletePdfFromQuotesDropDownTest test for DeletePdfFromQuotesDropDownDetailView upgrader script
 * CRYS-441 - https://sugarcrm.atlassian.net/browse/CRYS-441
 */
class DeletePdfFromQuotesDropDownTest extends UpgradeTestCase
{
    protected $file;

    public function setUp()
    {
        parent::setUp();
        SugarAutoLoader::ensureDir('custom/modules/Quotes/metadata');
        $this->file = 'custom/modules/Quotes/metadata/CRYS-441test.php';
    }

    /**
     * Test removing Print as PDF from dropdown menu
     * @param array $data
     * @param array $expected
     *
     * @dataProvider provider
     */
    public function testRun($data, $expected)
    {
        $dataContents = array();
        $dataContents['Quotes']['DetailView'] = array(
            'templateMeta' => array(
                'form' => $data
            ),
        );
        $expectedContents = array();
        $expectedContents['Quotes']['DetailView'] = array(
            'templateMeta' => array(
                'form' => $expected
            ),
        );

        $mockObject = $this->getMockBuilder('SugarUpgradeDeletePdfFromQuotesDropDown')
            ->setMethods(['backupFile', 'getFilesToProcess'])
            ->setConstructorArgs([$this->upgrader])
            ->getMock();

        $mockObject->from_version = '6.5.17';

        if ($data == $expected) {
            $mockObject->expects($this->never())->method('backupFile');
        } else {
            $mockObject->expects($this->atLeastOnce())->method('backupFile')->with($this->file);
        }

        $mockObject->expects($this->once())->method('getFilesToProcess')->will(
            $this->returnValue(array($this->file))
        );

        SugarTestHelper::saveFile($this->file);
        write_array_to_file("viewdefs", $dataContents, $this->file);

        $mockObject->run();

        require($this->file);
        $this->assertEquals($viewdefs, $expectedContents);
    }

    /**
     * Data provider.
     * @return array
     */
    public function provider()
    {
        return array(
            array(
                array(
                    'buttons' => array(
                        'EDIT',
                        'DUPLICATE',
                        'DELETE',
                        array(
                            'customCode' => '<form action="index.php" method="{$PDFMETHOD}" name="ViewPDF" id="form">
                                                <input type="hidden" name="module" value="Quotes">
                                                <input type="hidden" name="record" value="{$fields.id.value}">
                                                <input type="hidden" name="action" value="sugarpdf">
                                                <input type="hidden" name="email_action">
                                                <input title="{$APP.LBL_EMAIL_PDF_BUTTON_TITLE}" class="button" type="submit"
                                                    name="button" value="{$APP.LBL_EMAIL_PDF_BUTTON_LABEL}"
                                                    onclick="this.form.email_action.value=\'EmailLayout\';">
                                                <input title="{$APP.LBL_VIEW_PDF_BUTTON_TITLE}" class="button"
                                                    type="submit" name="button" value="{$APP.LBL_VIEW_PDF_BUTTON_LABEL}">'
                        ),
                        array(
                            'customCode' => '<form action="index.php" method="POST" name="Quote2Opp" id="form">
                                                <input type="hidden" name="module" value="Quotes">
                                                <input type="hidden" name="record" value="{$fields.id.value}">
                                                <input type="hidden" name="user_id" value="{$current_user->id}">
                                                <input type="hidden" name="team_id" value="{$fields.team_id.value}">
                                                <input type="hidden" name="user_name" value="{$current_user->user_name}">
                                                <input type="hidden" name="action" value="QuoteToOpportunity">
                                                <input type="hidden" name="opportunity_subject" value="{$fields.name.value}">
                                                <input type="hidden" name="opportunity_name" value="{$fields.name.value}">
                                                <input type="hidden" name="opportunity_id" value="{$fields.billing_account_id.value}">
                                                <input type="hidden" name="amount" value="{$fields.total.value}">
                                                <input type="hidden" name="valid_until" value="{$fields.date_quote_expected_closed.value}">
                                                <input type="hidden" name="currency_id" value="{$fields.currency_id.value}">
                                                <input title="{$APP.LBL_QUOTE_TO_OPPORTUNITY_TITLE}" class="button" type="submit"
                                                    name="opp_to_quote_button" value="{$APP.LBL_QUOTE_TO_OPPORTUNITY_LABEL}"></form>'
                        )
                    )
                ),
                array(
                    'buttons' => array(
                        'EDIT',
                        'DUPLICATE',
                        'DELETE',
                        array(
                            'customCode' => '<form action="index.php" method="POST" name="Quote2Opp" id="form">
                                                <input type="hidden" name="module" value="Quotes">
                                                <input type="hidden" name="record" value="{$fields.id.value}">
                                                <input type="hidden" name="user_id" value="{$current_user->id}">
                                                <input type="hidden" name="team_id" value="{$fields.team_id.value}">
                                                <input type="hidden" name="user_name" value="{$current_user->user_name}">
                                                <input type="hidden" name="action" value="QuoteToOpportunity">
                                                <input type="hidden" name="opportunity_subject" value="{$fields.name.value}">
                                                <input type="hidden" name="opportunity_name" value="{$fields.name.value}">
                                                <input type="hidden" name="opportunity_id" value="{$fields.billing_account_id.value}">
                                                <input type="hidden" name="amount" value="{$fields.total.value}">
                                                <input type="hidden" name="valid_until" value="{$fields.date_quote_expected_closed.value}">
                                                <input type="hidden" name="currency_id" value="{$fields.currency_id.value}">
                                                <input title="{$APP.LBL_QUOTE_TO_OPPORTUNITY_TITLE}" class="button" type="submit"
                                                    name="opp_to_quote_button" value="{$APP.LBL_QUOTE_TO_OPPORTUNITY_LABEL}"></form>'
                        )
                    )
                ),
            ),
            array(
                array(
                    'buttons' => array(
                        'EDIT',
                        'DUPLICATE',
                        'DELETE',
                        array(
                            'customCode' => '<form action="index.php" method="POST" name="Quote2Opp" id="form">
                                                <input type="hidden" name="module" value="Quotes">
                                                <input type="hidden" name="record" value="{$fields.id.value}">
                                                <input type="hidden" name="user_id" value="{$current_user->id}">
                                                <input type="hidden" name="team_id" value="{$fields.team_id.value}">
                                                <input type="hidden" name="user_name" value="{$current_user->user_name}">
                                                <input type="hidden" name="action" value="QuoteToOpportunity">
                                                <input type="hidden" name="opportunity_subject" value="{$fields.name.value}">
                                                <input type="hidden" name="opportunity_name" value="{$fields.name.value}">
                                                <input type="hidden" name="opportunity_id" value="{$fields.billing_account_id.value}">
                                                <input type="hidden" name="amount" value="{$fields.total.value}">
                                                <input type="hidden" name="valid_until" value="{$fields.date_quote_expected_closed.value}">
                                                <input type="hidden" name="currency_id" value="{$fields.currency_id.value}">
                                                <input title="{$APP.LBL_QUOTE_TO_OPPORTUNITY_TITLE}" class="button" type="submit"
                                                    name="opp_to_quote_button" value="{$APP.LBL_QUOTE_TO_OPPORTUNITY_LABEL}"></form>'
                        )
                    )
                ),
                array(
                    'buttons' => array(
                        'EDIT',
                        'DUPLICATE',
                        'DELETE',
                        array(
                            'customCode' => '<form action="index.php" method="POST" name="Quote2Opp" id="form">
                                                <input type="hidden" name="module" value="Quotes">
                                                <input type="hidden" name="record" value="{$fields.id.value}">
                                                <input type="hidden" name="user_id" value="{$current_user->id}">
                                                <input type="hidden" name="team_id" value="{$fields.team_id.value}">
                                                <input type="hidden" name="user_name" value="{$current_user->user_name}">
                                                <input type="hidden" name="action" value="QuoteToOpportunity">
                                                <input type="hidden" name="opportunity_subject" value="{$fields.name.value}">
                                                <input type="hidden" name="opportunity_name" value="{$fields.name.value}">
                                                <input type="hidden" name="opportunity_id" value="{$fields.billing_account_id.value}">
                                                <input type="hidden" name="amount" value="{$fields.total.value}">
                                                <input type="hidden" name="valid_until" value="{$fields.date_quote_expected_closed.value}">
                                                <input type="hidden" name="currency_id" value="{$fields.currency_id.value}">
                                                <input title="{$APP.LBL_QUOTE_TO_OPPORTUNITY_TITLE}" class="button" type="submit"
                                                    name="opp_to_quote_button" value="{$APP.LBL_QUOTE_TO_OPPORTUNITY_LABEL}"></form>'
                        )
                    )
                ),
            ),
            array(
                array(
                    'buttons' => array(
                        'EDIT',
                        'DUPLICATE',
                        array(
                            'customCode' => '<form action="index.php" method="{$PDFMETHOD}" name="ViewPDF" id="form">
                                                <input type="hidden" name="module" value="Quotes">
                                                <input type="hidden" name="record" value="{$fields.id.value}">
                                                <input type="hidden" name="action" value="sugarpdf">
                                                <input type="hidden" name="email_action">
                                                <input title="{$APP.LBL_EMAIL_PDF_BUTTON_TITLE}" class="button" type="submit"
                                                    name="button" value="{$APP.LBL_EMAIL_PDF_BUTTON_LABEL}"
                                                    onclick="this.form.email_action.value=\'EmailLayout\';">
                                                <input title="{$APP.LBL_VIEW_PDF_BUTTON_TITLE}" class="button"
                                                    type="submit" name="button" value="{$APP.LBL_VIEW_PDF_BUTTON_LABEL}">'
                        ),
                        'DELETE',
                        array(
                            'customCode' => '<form action="index.php" method="{$PDFMETHOD}" name="ViewPDF" id="form">
                                                <input type="hidden" name="module" value="Quotes">
                                                <input type="hidden" name="record" value="{$fields.id.value}">
                                                <input type="hidden" name="action" value="sugarpdf">
                                                <input type="hidden" name="email_action">
                                                <input title="{$APP.LBL_EMAIL_PDF_BUTTON_TITLE}" class="button" type="submit"
                                                    name="button" value="{$APP.LBL_EMAIL_PDF_BUTTON_LABEL}"
                                                    onclick="this.form.email_action.value=\'EmailLayout\';">
                                                <input title="{$APP.LBL_VIEW_PDF_BUTTON_TITLE}" class="button"
                                                    type="submit" name="button" value="{$APP.LBL_VIEW_PDF_BUTTON_LABEL}">'
                        ),
                        array(
                            'customCode' => '<form action="index.php" method="POST" name="Quote2Opp" id="form">
                                                <input type="hidden" name="module" value="Quotes">
                                                <input type="hidden" name="record" value="{$fields.id.value}">
                                                <input type="hidden" name="user_id" value="{$current_user->id}">
                                                <input type="hidden" name="team_id" value="{$fields.team_id.value}">
                                                <input type="hidden" name="user_name" value="{$current_user->user_name}">
                                                <input type="hidden" name="action" value="QuoteToOpportunity">
                                                <input type="hidden" name="opportunity_subject" value="{$fields.name.value}">
                                                <input type="hidden" name="opportunity_name" value="{$fields.name.value}">
                                                <input type="hidden" name="opportunity_id" value="{$fields.billing_account_id.value}">
                                                <input type="hidden" name="amount" value="{$fields.total.value}">
                                                <input type="hidden" name="valid_until" value="{$fields.date_quote_expected_closed.value}">
                                                <input type="hidden" name="currency_id" value="{$fields.currency_id.value}">
                                                <input title="{$APP.LBL_QUOTE_TO_OPPORTUNITY_TITLE}" class="button" type="submit"
                                                    name="opp_to_quote_button" value="{$APP.LBL_QUOTE_TO_OPPORTUNITY_LABEL}"></form>'
                        )
                    )
                ),
                array(
                    'buttons' => array(
                        'EDIT',
                        'DUPLICATE',
                        'DELETE',
                        array(
                            'customCode' => '<form action="index.php" method="POST" name="Quote2Opp" id="form">
                                                <input type="hidden" name="module" value="Quotes">
                                                <input type="hidden" name="record" value="{$fields.id.value}">
                                                <input type="hidden" name="user_id" value="{$current_user->id}">
                                                <input type="hidden" name="team_id" value="{$fields.team_id.value}">
                                                <input type="hidden" name="user_name" value="{$current_user->user_name}">
                                                <input type="hidden" name="action" value="QuoteToOpportunity">
                                                <input type="hidden" name="opportunity_subject" value="{$fields.name.value}">
                                                <input type="hidden" name="opportunity_name" value="{$fields.name.value}">
                                                <input type="hidden" name="opportunity_id" value="{$fields.billing_account_id.value}">
                                                <input type="hidden" name="amount" value="{$fields.total.value}">
                                                <input type="hidden" name="valid_until" value="{$fields.date_quote_expected_closed.value}">
                                                <input type="hidden" name="currency_id" value="{$fields.currency_id.value}">
                                                <input title="{$APP.LBL_QUOTE_TO_OPPORTUNITY_TITLE}" class="button" type="submit"
                                                    name="opp_to_quote_button" value="{$APP.LBL_QUOTE_TO_OPPORTUNITY_LABEL}"></form>'
                        )
                    )
                ),
            ),
            array(
                array(
                    'buttons' => array(
                        array(
                            'customCode' => '<form action="index.php" method="{$PDFMETHOD}" name="ViewPDF" id="form">
                                                <input type="hidden" name="module" value="Quotes">
                                                <input type="hidden" name="record" value="{$fields.id.value}">
                                                <input type="hidden" name="action" value="sugarpdf">
                                                <input type="hidden" name="email_action">
                                                <input title="{$APP.LBL_EMAIL_PDF_BUTTON_TITLE}" class="button" type="submit"
                                                    name="button" value="{$APP.LBL_EMAIL_PDF_BUTTON_LABEL}"
                                                    onclick="this.form.email_action.value=\'EmailLayout\';">
                                                <input title="{$APP.LBL_VIEW_PDF_BUTTON_TITLE}" class="button"
                                                    type="submit" name="button" value="{$APP.LBL_VIEW_PDF_BUTTON_LABEL}">'
                        ),
                        'EDIT',
                        'DUPLICATE',
                        'DELETE',
                        array(
                            'customCode' => '<form action="index.php" method="POST" name="Quote2Opp" id="form">
                                                <input type="hidden" name="module" value="Quotes">
                                                <input type="hidden" name="record" value="{$fields.id.value}">
                                                <input type="hidden" name="user_id" value="{$current_user->id}">
                                                <input type="hidden" name="team_id" value="{$fields.team_id.value}">
                                                <input type="hidden" name="user_name" value="{$current_user->user_name}">
                                                <input type="hidden" name="action" value="QuoteToOpportunity">
                                                <input type="hidden" name="opportunity_subject" value="{$fields.name.value}">
                                                <input type="hidden" name="opportunity_name" value="{$fields.name.value}">
                                                <input type="hidden" name="opportunity_id" value="{$fields.billing_account_id.value}">
                                                <input type="hidden" name="amount" value="{$fields.total.value}">
                                                <input type="hidden" name="valid_until" value="{$fields.date_quote_expected_closed.value}">
                                                <input type="hidden" name="currency_id" value="{$fields.currency_id.value}">
                                                <input title="{$APP.LBL_QUOTE_TO_OPPORTUNITY_TITLE}" class="button" type="submit"
                                                    name="opp_to_quote_button" value="{$APP.LBL_QUOTE_TO_OPPORTUNITY_LABEL}"></form>'
                        )
                    )
                ),
                array(
                    'buttons' => array(
                        'EDIT',
                        'DUPLICATE',
                        'DELETE',
                        array(
                            'customCode' => '<form action="index.php" method="POST" name="Quote2Opp" id="form">
                                                <input type="hidden" name="module" value="Quotes">
                                                <input type="hidden" name="record" value="{$fields.id.value}">
                                                <input type="hidden" name="user_id" value="{$current_user->id}">
                                                <input type="hidden" name="team_id" value="{$fields.team_id.value}">
                                                <input type="hidden" name="user_name" value="{$current_user->user_name}">
                                                <input type="hidden" name="action" value="QuoteToOpportunity">
                                                <input type="hidden" name="opportunity_subject" value="{$fields.name.value}">
                                                <input type="hidden" name="opportunity_name" value="{$fields.name.value}">
                                                <input type="hidden" name="opportunity_id" value="{$fields.billing_account_id.value}">
                                                <input type="hidden" name="amount" value="{$fields.total.value}">
                                                <input type="hidden" name="valid_until" value="{$fields.date_quote_expected_closed.value}">
                                                <input type="hidden" name="currency_id" value="{$fields.currency_id.value}">
                                                <input title="{$APP.LBL_QUOTE_TO_OPPORTUNITY_TITLE}" class="button" type="submit"
                                                    name="opp_to_quote_button" value="{$APP.LBL_QUOTE_TO_OPPORTUNITY_LABEL}"></form>'
                        )
                    )
                )
            ),
            array(
                array(
                    'buttons' => array(
                        array(
                            'customCode' => '<form action="index.php" method="{$PDFMETHOD}" name="ViewPDF" id="form">
                                                <input type="hidden" name="module" value="Quotes">
                                                <input type="hidden" name="record" value="{$fields.id.value}">
                                                <input type="hidden" name="action" value="sugarpdf">
                                                <input type="hidden" name="email_action">
                                                <input title="{$APP.LBL_EMAIL_PDF_BUTTON_TITLE}" class="button" type="submit"
                                                    name="button" value="{$APP.LBL_EMAIL_PDF_BUTTON_LABEL}"
                                                    onclick="this.form.email_action.value=\'EmailLayout\';">
                                                <input title="{$APP.LBL_VIEW_PDF_BUTTON_TITLE}" class="button"
                                                    type="submit" name="button" value="{$APP.LBL_VIEW_PDF_BUTTON_LABEL}">'
                        ),
                        array(
                            'customCode' => '<form action="index.php" method="{$PDFMETHOD}" name="ViewPDF" id="form">
                                                <input type="hidden" name="module" value="Quotes">
                                                <input type="hidden" name="record" value="{$fields.id.value}">
                                                <input type="hidden" name="action" value="sugarpdf">
                                                <input type="hidden" name="email_action">
                                                <input title="{$APP.LBL_EMAIL_PDF_BUTTON_TITLE}" class="button" type="submit"
                                                    name="button" value="{$APP.LBL_EMAIL_PDF_BUTTON_LABEL}"
                                                    onclick="this.form.email_action.value=\'EmailLayout\';">
                                                <input title="{$APP.LBL_VIEW_PDF_BUTTON_TITLE}" class="button"
                                                    type="submit" name="button" value="{$APP.LBL_VIEW_PDF_BUTTON_LABEL}">'
                        ),
                    )
                ),
                array()
            ),
            array(
                array(
                    'buttons' => array(
                        array(
                            'customCode' => '<form action="index.php" method="{$PDFMETHOD}" name="ViewPDF" id="form">
                                                <input type="hidden" name="module" value="Quotes">
                                                <input type="hidden" name="record" value="{$fields.id.value}">
                                                <input type="hidden" name="action" value="sugarpdf">
                                                <input type="hidden" name="email_action">
                                                <input title="{$APP.LBL_EMAIL_PDF_BUTTON_TITLE}" class="button" type="submit"
                                                    name="button" value="{$APP.LBL_EMAIL_PDF_BUTTON_LABEL}"
                                                    onclick="this.form.email_action.value=\'EmailLayout\';">
                                                <input title="{$APP.LBL_VIEW_PDF_BUTTON_TITLE}" class="button"
                                                    type="submit" name="button" value="{$APP.LBL_VIEW_PDF_BUTTON_LABEL}">'
                        )
                    )
                ),
                array()
            )
        );
    }
}
