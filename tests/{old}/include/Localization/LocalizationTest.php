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


class LocalizationTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Localization
     */
    private $_locale;

    /**
     * @var User
     */
    protected $_user;
    /**
     * pre-class environment setup
     *
     * @access public
     */
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');

        global $app_list_strings;
        $app_list_strings['salutation_dom']['Ms.'] = 'Frau';
    }

    public function setUp()
    {
        global $current_user;
        $this->_locale = Localization::getObject();
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user = $this->_user;
        $this->_currency = SugarTestCurrencyUtilities::createCurrency('Yen','¥','YEN',78.87);

    }

    public function tearDown()
    {
        // remove test user
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->_locale);
        unset($this->_user);
        unset($this->_currency);

        // remove test currencies
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
    }

    /**
     * post-object environment teardown
     *
     * @access public
     */
    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function providerGetLocaleFormattedName()
    {
        return array(
            array(
                't s f l',
                'Mason',
                'Hu',
                'Mr.',
                'Saler',
                'Saler Mr. Mason Hu',
                ),
            array(
                'l f',
                'Mason',
                'Hu',
                '',
                '',
                'Hu Mason',
                ),

            );
    }

    /**
     * @dataProvider providerGetLocaleFormattedName
     */
    public function testGetLocaleFormattedNameUsingFormatInUserPreference($nameFormat,$firstName,$lastName,$salutation,$title,$expectedOutput)
    {
    	$this->_user->setPreference('default_locale_name_format', $nameFormat);
    	$outputName = $this->_locale->getLocaleFormattedName($firstName, $lastName, $salutation, $title, '',$this->_user);
    	$this->assertEquals($expectedOutput, $outputName);
    }

    /**
     * @dataProvider providerGetLocaleFormattedName
     */
    public function testGetLocaleFormattedNameUsingFormatSpecified($nameFormat,$firstName,$lastName,$salutation,$title,$expectedOutput)
    {
    	$outputName = $this->_locale->getLocaleFormattedName($firstName, $lastName, $salutation, $title, $nameFormat,$this->_user);
    	$this->assertEquals($expectedOutput, $outputName);
    }

    /**
     * @ticket 26803
     */
    public function testGetLocaleFormattedNameWhenNameIsEmpty()
    {
        $this->_user->setPreference('default_locale_name_format', 'l f');
        $expectedOutput = ' ';
        $outputName = $this->_locale->getLocaleFormattedName('', '', '', '', '',$this->_user);

        $this->assertEquals($expectedOutput, $outputName);
    }

    /**
     * @ticket 26803
     */
    public function testGetLocaleFormattedNameWhenNameIsEmptyAndReturningEmptyString()
    {
        $this->_user->setPreference('default_locale_name_format', 'l f');
        $expectedOutput = '';
        $outputName = $this->_locale->getLocaleFormattedName('', '', '', '', '',$this->_user,true);

        $this->assertEquals($expectedOutput, $outputName);
    }

    public function testCurrenciesLoadingCorrectly()
    {
        global $sugar_config;

        $currencies = $this->_locale->getCurrencies();

        $this->assertEquals($currencies['-99']['name'],$sugar_config['default_currency_name']);
        $this->assertEquals($currencies['-99']['symbol'],$sugar_config['default_currency_symbol']);
        $this->assertEquals($currencies['-99']['conversion_rate'],1);
    }

    public function testConvertingUnicodeStringBetweenCharsets()
    {
        $string = "アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモガギグゲゴザジズゼゾダヂヅデド";

        $convertedString = $this->_locale->translateCharset($string,'UTF-8','EUC-CN');
        $this->assertNotEquals($string,$convertedString);

        // test for this working by being able to convert back and the string match
        $convertedString = $this->_locale->translateCharset($convertedString,'EUC-CN','UTF-8');
        $this->assertEquals($string,$convertedString);
    }

    public function testConvertKS_C_56011987AsCP949()
    {
        if ( !function_exists('iconv') ) {
            $this->markTestSkipped('Requires iconv');
        }

        $string = file_get_contents(dirname(__FILE__)."/Bug49619.txt");

        $convertedString = $this->_locale->translateCharset($string,'KS_C_5601-1987','UTF-8', true);
        $this->assertNotEquals($string,$convertedString);

        // test for this working by being able to convert back and the string match
        $convertedString = $this->_locale->translateCharset($convertedString,'UTF-8','KS_C_5601-1987',true);
        $this->assertEquals($string,$convertedString);
    }

    public function testCanDetectAsciiEncoding()
    {
        $string = 'string';

        $this->assertEquals(
            $this->_locale->detectCharset($string),
            'ASCII'
            );
    }

    public function testCanDetectUtf8Encoding()
    {
        $string = 'アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモガギグゲゴザジズゼゾダヂヅデド';

        $this->assertEquals(
            $this->_locale->detectCharset($string),
            'UTF-8'
            );
    }

    public function testGetPrecedentPreferenceWithUserPreference()
    {
        $backup = $GLOBALS['sugar_config']['export_delimiter'];
        $GLOBALS['sugar_config']['export_delimiter'] = 'John is Cool';
        $this->_user->setPreference('export_delimiter','John is Really Cool');

        $this->assertEquals(
            $this->_locale->getPrecedentPreference('export_delimiter',$this->_user),
            $this->_user->getPreference('export_delimiter')
            );

        $GLOBALS['sugar_config']['export_delimiter'] = $backup;
    }

    public function testGetPrecedentPreferenceWithNoUserPreference()
    {
        $backup = $GLOBALS['sugar_config']['export_delimiter'];
        $GLOBALS['sugar_config']['export_delimiter'] = 'John is Cool';

        $this->assertEquals(
            $this->_locale->getPrecedentPreference('export_delimiter',$this->_user),
            $GLOBALS['sugar_config']['export_delimiter']
            );

        $GLOBALS['sugar_config']['export_delimiter'] = $backup;
    }

    /**
     * @ticket 33086
     */
    public function testGetPrecedentPreferenceWithUserPreferenceAndSpecifiedConfigKey()
    {
        $backup = $GLOBALS['sugar_config']['export_delimiter'];
        $GLOBALS['sugar_config']['export_delimiter'] = 'John is Cool';
        $this->_user->setPreference('export_delimiter','');
        $GLOBALS['sugar_config']['default_random_setting_for_localization_test'] = 'John is not Cool at all';

        $this->assertEquals(
            $this->_locale->getPrecedentPreference('export_delimiter',$this->_user,'default_random_setting_for_localization_test'),
            $GLOBALS['sugar_config']['default_random_setting_for_localization_test']
            );

        $backup = $GLOBALS['sugar_config']['export_delimiter'];
        unset($GLOBALS['sugar_config']['default_random_setting_for_localization_test']);
    }

    /**
     * @ticket 39171
     */
    public function testGetPrecedentPreferenceForDefaultEmailCharset()
    {
        $emailSettings = array('defaultOutboundCharset' => 'something fun');
        $this->_user->setPreference('emailSettings',$emailSettings, 0, 'Emails');

        $this->assertEquals(
            $this->_locale->getPrecedentPreference('default_email_charset',$this->_user),
            $emailSettings['defaultOutboundCharset']
            );
    }

    /**
     * @ticket 23992
     */
    public function testGetCurrencySymbol()
    {
        $this->_user->setPreference('currency',$this->_currency->id);

        $this->assertEquals(
            $this->_locale->getCurrencySymbol($this->_user),
            '¥'
            );
    }

    /**
     * @ticket 23992
     */
    public function testGetLocaleFormattedNumberWithNoCurrencySymbolSpecified()
    {
        $this->_user->setPreference('currency',$this->_currency->id);
        $this->_user->setPreference('dec_sep','.');
        $this->_user->setPreference('num_grp_sep',',');
        $this->_user->setPreference('default_currency_significant_digits',2);

        $this->assertEquals(
            $this->_locale->getLocaleFormattedNumber(20,'',true,$this->_user),
            '¥20'
            );
    }

    /**
     * @bug 60672
     */
    public function testGetNumberGroupingSeparatorIfSepIsEmpty()
    {
        $this->_user->setPreference('num_grp_sep','');
        $this->assertEmpty($this->_locale->getNumberGroupingSeparator(), "1000s separator should be ''");
    }

    /**
     * @param string|null      $macro
     * @param SugarBean|string $bean
     * @param array|null       $data
     * @param string           $expected
     *
     * @dataProvider testFormatNameProvider
     */
    public function testFormatName($macro, $bean, $data, $expected)
    {
        if ($macro) {
            $locale = $this->getMockBuilder('Localization')
                ->setMethods(array('getLocaleFormatMacro'))
                ->disableOriginalConstructor()
                ->getMock();
            $locale->expects($this->any())
                ->method('getLocaleFormatMacro')
                ->will($this->returnValue($macro));
        } else {
            $locale = $this->_locale;
        }

        $actual = $locale->formatName($bean, $data);
        $this->assertEquals($expected, $actual);
    }

    public static function testFormatNameProvider()
    {
        $user1 = new User();
        $user1->first_name = 'John';
        $user1->last_name  = 'Doe';
        $user1->user_name  = 'jdoe';
        $user1->position   = 'Engineer';
        $user1->name_format_map = array_merge(
            $user1->name_format_map,
            array(
                'p' => 'position',
                'u' => 'user_name',
                'z' => 'non_existing_field',
            )
        );

        $contact1 = new Contact();
        $contact1->salutation = 'Ms.';
        $contact1->first_name = 'Barbara';
        $contact1->last_name  = 'Schulz';

        $contact2 = new Contact();
        $contact2->salutation = 'Sir';
        $contact2->first_name = 'Aaron';
        $contact2->last_name = 'Brown';

        $contact3 = new Contact();
        $contact3->first_name = 'David';

        $contact4 = new Contact();
        $contact4->last_name = 'Livingstone';

        return array(
            'invalid-bean-type' => array(null, null, null, false),
            'invalid-module'    => array(null, 'Apples', null, false),
            'bean-as-object'    => array(null, $user1, null, 'John Doe'),
            'bean-as-string'    => array(
                null,
                'Users',
                array(
                    'first_name' => 'Judy',
                    'last_name'  => 'Smith',
                ),
                'Judy Smith',
            ),
            'non-existing-token' => array('x f', $user1, null, 'John'),
            'non-existing-field' => array('z l', $user1, null, 'Doe'),
            'empty-result'       => array('x z', $user1, null, ''),
            'custom-token'       => array('f (u) l', $user1, null, 'John (jdoe) Doe'),
            'custom-field'       => array('l, f (p)', $user1, null, 'Doe, John (Engineer)'),
            'enum-is-localized'  => array(null, $contact1, null, 'Frau Barbara Schulz'),
            'enum-not-found'     => array(null, $contact2, null, 'Sir Aaron Brown'),
            'trim-left'          => array('l, f', $contact3, null, 'David'),
            'trim-right'         => array('l, f', $contact4, null, 'Livingstone'),
        );
    }

    /**
     * Test to make sure that when num_grp_sep is passed with out a sugarDefaultConfig Name it returns null if not set
     *
     * @covers Localization::getPrecedentPreference
     */
    public function testGetPrecedentPreferenceReturnsNullForNumGrpSep()
    {
        $this->assertNull($this->_locale->getPrecedentPreference('num_grp_sep', $this->_user));
    }

    /**
     * Test to make sure that the proper value is returned from getPrecedentPreference for num_grp_sep
     * when the user has one
     *
     * @covers Localization::getPrecedentPreference
     */
    public function testGetPrecedentPreferenceReturnsValueForNumGrpSep()
    {
        $this->_user->setPreference('num_grp_sep', '!');
        $this->assertEquals('!', $this->_locale->getPrecedentPreference('num_grp_sep', $this->_user));
    }

    /**
     * Test to retrieve authenticated user's preferred language
     */
    public function testGetAuthenticatedUserLanguage()
    {
        //test from user pref
        $this->_user->preferred_language = 'fr_FR';
        $this->assertEquals('fr_FR', $this->_locale->getAuthenticatedUserLanguage());
        $this->_user->preferred_language = 'de_DE';
        $this->assertEquals('de_DE', $this->_locale->getAuthenticatedUserLanguage());
        //test from session
        if (!empty($_SESSION['authenticated_user_language'])) {
            $oSESSION = $_SESSION['authenticated_user_language'];
        }
        $this->_user->preferred_language = null;
        $_SESSION['authenticated_user_language'] = 'ja_JP';
        $this->assertEquals('ja_JP', $this->_locale->getAuthenticatedUserLanguage());
        //test from default
        unset($_SESSION['authenticated_user_language']);
        $this->assertEquals($GLOBALS['sugar_config']['default_language'], $this->_locale->getAuthenticatedUserLanguage());
        if (isset($oSESSION)) {
            $_SESSION['authenticated_user_language'] = $oSESSION;
        }
    }

    /**
     * Provider for testGetLocaleUnFormattedName.
     *
     * @return array
     */
    public function getLocaleUnFormattedNameProvider()
    {
        return array(
            array(
                'name' => 'TestMan Tester',
                'format' => 'f l',
                'expected' => array(
                    'f' => 'TestMan',
                    'l' => 'Tester',
                ),
            ),
            array(
                'name' => 'Mr. TestMan Tester',
                'format' => 's f l',
                'expected' => array(
                    's' => 'Mr.',
                    'f' => 'TestMan',
                    'l' => 'Tester',
                ),
            ),
            array(
                'name' => 'Tester, Mr. TestMan Jr.',
                'format' => 'l, s f p',
                'expected' => array(
                    'l' => 'Tester',
                    's' => 'Mr.',
                    'f' => 'TestMan',
                    'p' => 'Jr.'
                ),
            ),
            array(
                'name' => 'Tester, Mr. TestMan Jr.',
                'format' => 'l, s f, p',
                'expected' => array(
                    'l' => 'Tester',
                    's' => 'Mr.',
                    'f' => 'TestMan Jr.',
                    'p' => '',
                ),
            ),
            array(
                'name' => 'Tester, Mr. TestMan Jr., III',
                'format' => 'l, s f, p',
                'expected' => array(
                    'l' => 'Tester',
                    's' => 'Mr.',
                    'f' => 'TestMan Jr.',
                    'p' => 'III',
                ),
            ),
            array(
                'name' => 'TestMan Mr.TestMan Mr. Mr.Tester Tester',
                'format' => 'f s l',
                'expected' => array(
                    'f' => 'TestMan Mr.TestMan',
                    's' => 'Mr.',
                    'l' => 'Mr.Tester Tester',
                ),
            ),
            array(
                'name' => 'TestMan TestMan Tester Tester',
                'format' => 'f s l',
                'expected' => array(
                    'f' => 'TestMan',
                    's' => '',
                    'l' => 'TestMan Tester Tester',
                ),
            ),
            array(
                'name' => 'TestMan Mr. TestMan, Tester Mr. Tester',
                'format' => 'f, s l',
                'expected' => array(
                    'f' => 'TestMan Mr. TestMan',
                    's' => 'Mr.',
                    'l' => 'Tester',
                ),
            ),
            array(
                'name' => 'TestMan Mr. TestMan, Tester Mr., Tester',
                'format' => 'f, s, l',
                'expected' => array(
                    'f' => 'TestMan Mr. TestMan',
                    's' => 'Mr.',
                    'l' => 'Tester',
                ),
            ),
            array(
                'name' => 'Mr. TestMan Tester',
                'format' => 's f, l',
                'expected' => array(
                    's' => 'Mr.',
                    'f' => 'TestMan Tester',
                    'l' => '',
                ),
            ),
            array(
                'name' => 'Robert John Downey Jr.',
                'format' => 'f t l p',
                'expected' => array(
                    'f' => 'Robert',
                    't' => 'John',
                    'l' => 'Downey',
                    'p' => 'Jr.',
                ),
            ),
            array(
                'name' => 'Robert John Downey Jr.',
                'format' => 'f s l',
                'expected' => array(
                    'f' => 'Robert',
                    's' => '',
                    'l' => 'John Downey Jr.',
                ),
            ),
            array(
                'name' => 'Robert John Downey Jr.',
                'format' => 's l',
                'expected' => array(
                    's' => '',
                    'l' => 'Robert John Downey Jr.',
                ),
            ),
        );
    }

    /**
     * Test parse fullname
     *
     * @param $name
     * @param $format
     * @param $expected
     *
     * @covers Localization::getLocaleUnFormattedName
     * @dataProvider getLocaleUnFormattedNameProvider
     */
    public function testGetLocaleUnFormattedName($name, $format, $expected)
    {
        $result = $this->_locale->getLocaleUnFormattedName($name, $format);
        $this->assertEquals($expected, $result);
    }
}
