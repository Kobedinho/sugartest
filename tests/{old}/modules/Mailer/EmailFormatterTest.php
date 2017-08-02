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


class EmailFormatterTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @group email
     * @group mailer
     */
    public function testFormatTextBody_IncludeDisclosure_DisclosureIsAppendedToBody() {
        self::markTestIncomplete("Not yet implemented");
    }

    /**
     * @group email
     * @group mailer
     */
    public function testFormatTextBody_DoNotIncludeDisclosure_BodyIsNotChanged() {
        self::markTestIncomplete("Not yet implemented");
    }

    /**
     * @group email
     * @group mailer
     */
    public function testFormatHtmlBody_IncludeDisclosure_DisclosureIsAppendedToBody() {
        self::markTestIncomplete("Not yet implemented");
    }

    /**
     * @group email
     * @group mailer
     */
    public function testFormatHtmlBody_DoNotIncludeDisclosure_BodyIsNotChanged() {
        self::markTestIncomplete("Not yet implemented");
    }

    /**
     * @group email
     * @group mailer
     */
    public function testFormatHtmlBody_HasInlineImages_ConvertInlineImagesToEmbeddedImages_ReturnsModifiedBodyAndArrayOfEmbeddedImagesToAttach() {
        self::markTestIncomplete("Not yet implemented");
    }

    /**
     * @group email
     * @group mailer
     */
    public function testFormatHtmlBody_DoesNotHaveInlineImages_BodyIsNotChangedAndReturnedArrayIsEmpty() {
        self::markTestIncomplete("Not yet implemented");
    }

    /**
     * Formerly HandleBodyInHTMLformatTest::testHandleBodyInHtmlformat. This is really testing that from_html works,
     * but it's best not to lose a test and thus risk a regression.
     *
     * @group email
     * @group bug30591
     * @group mailer
     */
    public function testTranslateCharacters_HtmlEntitiesAreTranslatedToRealCharacters() {
        $body = "Check to see if &quot; &lt; &gt; &#039; was translated to \" < > '";

        $mockLocale = self::createPartialMock("Localization", array("translateCharset"));
        $mockLocale->expects(self::any())
            ->method("translateCharset")
            ->will(self::returnValue($body)); // return the exact same string

        $mockFormatter = self::createPartialMock("EmailFormatter", array("retrieveDisclosureSettings"));
        $mockFormatter->expects(self::any())
            ->method("retrieveDisclosureSettings")
            ->will(self::returnValue(false));

        $expected = "Check to see if \" < > ' was translated to \" < > '";
        $actual   = $mockFormatter->translateCharacters($body, $mockLocale, "UTF-8");
        self::assertEquals($expected, $actual, "The HTML entities were not all translated properly");
    }
}
