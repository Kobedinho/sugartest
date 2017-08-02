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

require_once 'include/SugarEmailAddress/SugarEmailAddress.php';

/**
 * @covers SugarEmailAddress
 */
class SugarEmailAddressTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var SugarEmailAddress */
    private $ea;

    private $primary1 = array(
        'primary_address' => true,
        'email_address'   => 'p1@example.com',
        'opt_out'         => true,
        'invalid_email'   => true,
    );

    private $primary2 = array(
        'primary_address' => true,
        'email_address'   => 'p2@example.com',
        'opt_out'         => false,
        'invalid_email'   => false,
    );

    private $alternate1 = array(
        'primary_address' => false,
        'email_address'   => 'a1@example.com',
        'opt_out'         => false,
        'invalid_email'   => false,
    );

    private $alternate2 = array(
        'primary_address' => false,
        'email_address'   => 'a2@example.com',
        'opt_out'         => false,
        'invalid_email'   => false,
    );

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    protected function setUp()
    {
        $this->ea = BeanFactory::newBean('EmailAddresses');
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();

        parent::tearDownAfterClass();
    }

    public function isValidEmailProvider()
    {
        return array(
            array('john@john.com', true),
            array('----!john.com', false),
            array('john', false),
            // bugs: SI40068, SI44338
            array('jo&hn@john.com', true),
            array('joh#n@john.com.br', true),
            array('&#john@john.com', true),
            // bugs: SI40068, SI39186
            // note: a dot at the beginning or end of the local part are not allowed by RFC2822
            array('atendimento-hd.@uol.com.br', false),
            // bugs: SI13765
            array('st.-annen-stift@t-online.de', true),
            // bugs: SI39186
            array('qfflats-@uol.com.br', true),
            // bugs: SI44338
            array('atendimento-hd.?uol.com.br', false),
            array('atendimento-hd.?uol.com.br;aaa@com.it', false),
            array('f.grande@pokerspa.it', true),
            array('fabio.grande@softwareontheroad.it', true),
            array('fabio$grande@softwareontheroad.it', true),
            // bugs: SI44473
            // note: with MAR-1894 the infinite loop bug is no longer a problem, so this email address can pass
            // validation
            array('ettingshallprimaryschool@wolverhampton.gov.u', true),
            // bugs: SI13018
            array('Ert.F.Suu.-PA@pumpaudio.com', true),
            // bugs: SI23202
            array('test--user@example.com', true),
            // bugs: SI42403
            array('test@t--est.com', true),
            // bugs: SI42404
            array('t.-est@test.com', true),
            // bugs: MAR-1894
            array("o'hara@email.com", true),
            array("用户@例子.广告", true),
        );
    }

    public function testAddressesAreZeroBased()
    {
        // make sure that initially there are no addresses
        $this->assertCount(0, $this->ea->addresses);

        $this->ea->addAddress('test@example.com');
        $this->ea->addAddress('test@example.com');

        // make sure duplicate address is replaced
        $this->assertCount(1, $this->ea->addresses);

        reset($this->ea->addresses);
        $this->assertEquals(0, key($this->ea->addresses), 'Email addresses is not a 0-based array');
    }

    public function testEmail1SavesWhenEmailIsEmpty()
    {
        $bean = BeanFactory::newBean('Accounts');
        $bean->email1 = 'a@a.com';
        $this->ea->handleLegacySave($bean);

        // Begin assertions
        $this->assertNotEmpty($this->ea->addresses);
        $this->assertArrayHasKey(0, $this->ea->addresses);
        $this->assertArrayHasKey('email_address', $this->ea->addresses[0]);
        $this->assertEquals('a@a.com', $this->ea->addresses[0]['email_address']);
    }
    
    public function testSavedEmailsPersistAfterSave()
    {
        $addresses = array(
            array('email_address' => 'a@a.com', 'primary_address' => true),
            array('email_address' => 'b@b.com'),
            array('email_address' => 'c@c.com'),
            array('email_address' => 'd@d.com'),
        );
        $bean = BeanFactory::newBean('Accounts');
        $bean->email = $addresses;
        $this->ea->handleLegacySave($bean);

        // Begin assertions
        $this->assertNotEmpty($this->ea->addresses);
        $this->assertEquals(4, count($this->ea->addresses));
        $this->assertArrayHasKey(0, $this->ea->addresses);
        $this->assertArrayHasKey('email_address', $this->ea->addresses[0]);
        $this->assertEquals('a@a.com', $this->ea->addresses[0]['email_address']);
        $this->assertArrayHasKey(3, $this->ea->addresses);
        $this->assertArrayHasKey('email_address', $this->ea->addresses[3]);
        $this->assertEquals('d@d.com', $this->ea->addresses[3]['email_address']);
    }
    
    public function testSaveUsesCorrectValues()
    {
        // Set values on the email address object for testing
        $test = array(
            array(
                'email_address' => 'a@a.com',
                'email_address_id' => null,
                'primary_address' => '1',
                'invalid_email' => '0',
                'opt_out' => '0',
                'reply_to_address' => '0',
            ),
            array(
                'email_address' => 'b@b.com',
                'email_address_id' => null,
                'primary_address' => '0',
                'invalid_email' => '0',
                'opt_out' => '0',
                'reply_to_address' => '0',
            ),
            array(
                'email_address' => 'c@c.com',
                'email_address_id' => null,
                'primary_address' => '0',
                'invalid_email' => '0',
                'opt_out' => '0',
                'reply_to_address' => '0',
            ),
            array(
                'email_address' => 'd@d.com',
                'email_address_id' => null,
                'primary_address' => '0',
                'invalid_email' => '0',
                'opt_out' => '0',
                'reply_to_address' => '0',
            ),
        );

        $expect = array(
            array(
                'email_address' => 'z@z.com',
                'primary_address' => '1',
                'reply_to_address' => '0',
                'invalid_email' => '0',
                'opt_out' => '0',
                'email_address_id' => null,
            ),
        );

        // Setup the test case
        $this->ea->fetchedAddresses = $this->ea->addresses = $test;
        $bean = BeanFactory::newBean('Contacts');
        $bean->email = $test;
        $bean->email1 = 'z@z.com';
        $bean->email2 = '';
        $this->ea->handleLegacySave($bean);

        // Expectation is that email1 will win
        $this->assertEquals($expect, $this->ea->addresses);
    }

    /**
     * @dataProvider isValidEmailProvider
     * @group bug40068
     */
    public function testIsValidEmail($email, $expected)
    {
        $startTime = microtime(true);
        $this->assertEquals($expected, SugarEmailAddress::isValidEmail($email));
        // Checking for elapsed time. I expect that evaluation takes less than a second.
        //$timeElapsed = microtime(true) - $startTime;
        //This is only testing the speed of Regex on this system.
        //It is failing randomly without any obvious source.
        //$this->assertLessThan(1.0, $timeElapsed);
    }

    /**
     * When primary address exists, it's used to populate email1 property
     *
     * @covers SugarEmailAddress::populateLegacyFields
     */
    public function testPrimaryAttributeConsidered()
    {
        $bean = new SugarBean();
        $this->ea->addresses = array(
            $this->alternate1,
            $this->primary1,
        );

        $this->ea->populateLegacyFields($bean);

        $this->assertEquals('p1@example.com', $bean->email1);
        $this->assertEquals(true, $bean->email_opt_out);
        $this->assertEquals(true, $bean->invalid_email);
        $this->assertEquals('a1@example.com', $bean->email2);
    }

    /**
     * When multiple primary addresses exist, the first of them is used to
     * populate email1 property
     *
     * @covers SugarEmailAddress::populateLegacyFields
     */
    public function testMultiplePrimaryAddresses()
    {
        $bean = new SugarBean();
        $this->ea->addresses = array(
            $this->primary1,
            $this->primary2,
        );

        $this->ea->populateLegacyFields($bean);

        $this->assertEquals('p1@example.com', $bean->email1);
        $this->assertEquals('p2@example.com', $bean->email2);
    }

    /**
     * When no primary address exists, the first of non-primary ones is used to
     * populate email1 property
     *
     * @covers SugarEmailAddress::populateLegacyFields
     */
    public function testNoPrimaryAddress()
    {
        $bean = new SugarBean();
        $this->ea->addresses = array(
            $this->alternate1,
            $this->alternate2,
        );

        $this->ea->populateLegacyFields($bean);

        $this->assertEquals('a1@example.com', $bean->email1);
        $this->assertEquals('a2@example.com', $bean->email2);
    }

    /**
     * All available addresses are used to populate email properties
     *
     * @covers SugarEmailAddress::populateLegacyFields
     */
    public function testAllPropertiesArePopulated()
    {
        $bean = new SugarBean();
        $this->ea->addresses = array(
            $this->primary1,
            $this->primary2,
            $this->alternate1,
            $this->alternate2,
        );

        $this->ea->populateLegacyFields($bean);

        $this->assertEquals('p1@example.com', $bean->email1);
        $this->assertEquals('p2@example.com', $bean->email2);
        $this->assertEquals('a1@example.com', $bean->email3);
        $this->assertEquals('a2@example.com', $bean->email4);
    }
}
