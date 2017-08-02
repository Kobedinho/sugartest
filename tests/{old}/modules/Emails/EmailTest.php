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

require_once "tests/{old}/modules/OutboundEmailConfiguration/OutboundEmailConfigurationTestHelper.php";

/**
 * Test cases for Bug 30591
 */
class EmailTest extends Sugar_PHPUnit_Framework_TestCase
{
	private $email;

	public function setUp()
	{
	    global $current_user;

	    $current_user = BeanFactory::newBean("Users");
        $current_user->getSystemUser();
	    $this->email = new Email();
	    $this->email->email2init();
	}

	public function tearDown()
	{
		unset($this->email);
		// SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
		unset($GLOBALS['current_user']);
	}

	public function testSafeAttachmentName ()
	{
		$extArray[] = '0.py';
		$extArray[] = '1.php';
		$extArray[] = '2.php3';
		$extArray[] = '3.php4';
		$extArray[] = '4.php5';
		$extArray[] = '5.js';
		$extArray[] = '6.htm';
		$extArray[] = '7.html';
		$extArray[] = '8.txt';
		$extArray[] = '9.doc';
		$extArray[] = '10.xls';
		$extArray[] = '11.pdf';
		$extArray[] = '12';

		for ($i = 0; $i < count($extArray); $i++) {
			$result = $this->email->safeAttachmentName($extArray[$i]);
			if ($i < 8) {
				$this->assertEquals($result, true);
			} else {
				$this->assertEquals($result, false);
			}
		}
	}

	public function testEmail2ParseAddresses()
	{
		$emailDisplayName[] = '';
		$emailDisplayName[] = 'Shine Ye';
		$emailDisplayName[] = 'Roger,Smith';
		$emailAddress[] = 'masonhu@sugarcrm.com';
		$emailAddress[] = 'xye@sugarcrm.com';
		$emailAddress[] = 'roger@sugarcrm.com';
		for ($j = 0; $j < count($emailDisplayName); $j++)
		{
			if ($j < 1)
				$emailString[] = $emailDisplayName[$j].$emailAddress[$j];
			else
				$emailString[] = $emailDisplayName[$j].'<'.$emailAddress[$j].'>';

		}
		$emailAddressString = implode(', ', $emailString);
		$result = $this->email->email2ParseAddresses($emailAddressString);
		$onlyEmailResult = $this->email->email2ParseAddressesForAddressesOnly($emailAddressString);
		for ($v = 0; $v < count($result); $v++)
		{
			$this->assertEquals($result[$v]['display'], $emailDisplayName[$v]);
			$this->assertEquals($result[$v]['email'], $emailAddress[$v]);
			$this->asserteQuals($onlyEmailResult[$v], $emailAddress[$v]);
		}
	}

    public function testEmail2ParseAddresses_ParameterIsEmpty_EmptyArrayIsReturned()
    {
        $actual = $this->email->email2ParseAddresses('');
        $this->assertCount(0, $actual, 'An empty array should have been returned.');
    }

	public function testDecodeDuringSend()
	{
		$testString = 'Replace sugarLessThan and sugarGreaterThan with &lt; and &gt;';
		$expectedResult = 'Replace &lt; and &gt; with &lt; and &gt;';
		$resultString = $this->email->decodeDuringSend($testString);
		$this->asserteQuals($resultString, $expectedResult);
	}

    public function configParamProvider()
    {
        $address_array =  array(
            'id1' => 'test1@example.com',
            'id2' => 'test2@example.com',
            'id3' => 'test3@example.com'
        );

        return array(
            array(',',$address_array,'test1@example.com,test2@example.com,test3@example.com'), // default and correct delimiter for email addresses
            array(';',$address_array,'test1@example.com;test2@example.com;test3@example.com'), // outlook's delimiter for email addresses
        );
    }

    /**
     * @group email
     * @group mailer
     */
    public function testEmailSend_Success()
    {
        OutboundEmailConfigurationTestHelper::setUp();
        $config = OutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS['current_user']);
        $mockMailer = new MockMailer($config);
        MockMailerFactory::setMailer($mockMailer);

        $em = new Email();
        $em->email2init();
        $em->_setMailerFactoryClassName('MockMailerFactory');

        $em->name = "This is the Subject";
        $em->description_html = "This is the HTML Description";
        $em->description      = "This is the Text Description";

        $from       = new EmailIdentity("twolf@sugarcrm.com" , "Tim Wolf");
        $replyto    = $from;
        $to         = new EmailIdentity("twolf@sugarcrm.com" , "Tim Wolf");
        $cc         = new EmailIdentity("twolf@sugarcrm.com" , "Tim Wolf");

        $em->from_addr = $from->getEmail();
        $em->from_name = $from->getName();

        $em->reply_to_addr = $replyto->getEmail();
        $em->reply_to_name = $replyto->getName();

        $em->to_addrs_arr = array(
            array(
                'email'     => $to->getEmail(),
                'display'   => $to->getName(),
            )
        );
        $em->cc_addrs_arr = array(
            array(
                'email'     => $cc->getEmail(),
                'display'   => $cc->getName(),
            )
        );

        $em->send();

        $data = $mockMailer->toArray();
        $this->assertEquals($em->description_html, $data['htmlBody']);
        $this->assertEquals($em->description, $data['textBody']);

        $headers = $mockMailer->getHeaders();
        $this->assertEquals($em->name, $headers['Subject']);
        $this->assertEquals($from->getEmail(), $headers['From'][0]);
        $this->assertEquals($from->getName(),  $headers['From'][1]);
        $this->assertEquals($replyto->getEmail(), $headers['Reply-To'][0]);
        $this->assertEquals($replyto->getName(),  $headers['Reply-To'][1]);

        $recipients = $mockMailer->getRecipients();

        $actual_to=array_values($recipients['to']);
        $this->assertEquals($to->getEmail(), $actual_to[0]->getEmail(), "TO Email Address Incorrect");
        $this->assertEquals($to->getName(),  $actual_to[0]->getName(),  "TO Name Incorrect");

        $actual_cc=array_values($recipients['cc']);
        $this->assertEquals($to->getEmail(), $actual_cc[0]->getEmail(), "CC Email Address Incorrect");
        $this->assertEquals($to->getName(),  $actual_cc[0]->getName(),  "CC Name Incorrect");

        $this->assertEquals(true,$mockMailer->wasSent());
        OutboundEmailConfigurationTestHelper::tearDown();
    }

    /**
     * @group bug51804
     * @dataProvider configParamProvider
     * @param string $config_param
     * @param array $address_array
     * @param string $expected
     */
    public function testArrayToDelimitedString($config_param, $address_array, $expected)
    {
        $GLOBALS['sugar_config']['email_address_separator'] = $config_param;

        $this->assertEquals($expected,$this->email->_arrayToDelimitedString($address_array), 'Array should be delimited with correct delimiter');

    }
}


class MockMailer extends SmtpMailer
{
    var $_sent;

    function __construct(OutboundEmailConfiguration $config) {
        $this->_sent = false;
        $this->config = $config;
        $headers = new EmailHeaders();
        $headers->setHeader(EmailHeaders::From,   $config->getFrom());
        $headers->setHeader(EmailHeaders::Sender, $config->getFrom());
        $this->headers = $headers;
        $this->recipients = new RecipientsCollection();
    }

    public function getHeaders() {
        return($this->headers->packageHeaders());
    }

    public function getRecipients() {
        return $this->recipients->getAll();
    }

    public function send() {
        $this->_sent = true;
    }

    public function wasSent() {
        return $this->_sent;
    }

    public function toArray() {
        return $this->asArray($this);
    }

    private function asArray($d) {
        if (is_object($d)) {
            $d = get_object_vars($d);
        }
        if (is_array($d)) {
            return array_map(__METHOD__, $d);
        }
        return $d;
    }
}

class MockMailerFactory extends MailerFactory
{
    private static $mailer;

    public static function setMailer(BaseMailer $mailer)
    {
        static::$mailer = $mailer;
    }

    public static function getMailer(OutboundEmailConfiguration $config)
    {
        return static::$mailer;
    }
}
