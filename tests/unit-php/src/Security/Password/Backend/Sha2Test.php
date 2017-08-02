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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Password\Backend;

use Sugarcrm\Sugarcrm\Security\Password\Salt;
use Sugarcrm\Sugarcrm\Security\Password\Backend\Sha2;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Password\Backend\Sha2
 *
 */
class Sha2Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Hash testing with predictable salt
     *
     * @covers ::hash
     * @dataProvider providerTestHashPredictable
     */
    public function testHashPredictable($algo, array $options, $salt, $size, $password, $expected)
    {
        $crypt = new Sha2();
        $crypt->setAlgo($algo);
        $crypt->setOptions($options);

        // mock salt generator
        $saltMock = $this->getSaltMock(array('generate'));

        $saltMock->expects($this->once())
            ->method('generate')
            ->with($this->equalTo($size))
            ->will($this->returnValue($salt));

        $crypt->setSalt($saltMock);
        $this->assertEquals($expected, $crypt->hash($password));
    }

    public function providerTestHashPredictable()
    {
        return array(
            array(
                'CRYPT_SHA256',
                array(),
                '1234567890123456',
                16,
                'password3',
                '$5$rounds=5000$1234567890123456$P55jK.CUi8upfSiEdRr9iaThMhV/ay/L0XI3r/IZf.1',
            ),
            array(
                'CRYPT_SHA256',
                array('rounds' => 4000),
                '1234567890123456',
                16,
                'password3',
                '$5$rounds=4000$1234567890123456$c8hKbhADxFiVymele2/EEUOXpJtg6ieQRVwsCbvNm40',
            ),
            array(
                'CRYPT_SHA512',
                array(),
                '1234567890123456',
                16,
                'password4',
                '$6$rounds=5000$1234567890123456$Z7ph2lhVPUfxzW4XtWJdISuEHTxMlaqYTJiK8FMxnG0Sa14NEMJaGJIEEJZB5R32bCqgKNtBugLr466CxtkTg/',
            ),
            array(
                'CRYPT_SHA512',
                array('rounds' => '4000'),
                '1234567890123456',
                16,
                'password4',
                '$6$rounds=4000$1234567890123456$uRd4b1Im0ng8PbPHQnSYn/EcY/1W4X5RqxRUriv/orzo20tHcjgTchhEfEbJA.sY7823DMT.quElNWKTh./qH0',
            ),
        );
    }

    /**
     * Hash testing with real salt using regex matching on the hash result
     *
     * @covers ::hash
     * @covers ::generateSalt
     * @covers ::getAlgoNumber
     * @covers ::getRounds
     * @dataProvider providerTestHash
     */
    public function testHash($algo, array $options, $password, $pattern)
    {
        $crypt = new Sha2();
        $crypt->setSalt(new Salt());
        $crypt->setAlgo($algo);
        $crypt->setOptions($options);
        $this->assertRegExp($pattern, $crypt->hash($password));
    }

    public function providerTestHash()
    {
        return array(
            array(
                'CRYPT_SHA256',
                array(),
                'password3',
                '#^\$5\$rounds=5000+\$[A-Za-z0-9+/]{16}\$[./A-Za-z0-9]{43}$#D',
            ),
            array(
                'CRYPT_SHA256',
                array('rounds' => 4000),
                'password3',
                '#^\$5\$rounds=4000+\$[A-Za-z0-9+/]{16}\$[./A-Za-z0-9]{43}$#D',
            ),
            array(
                'CRYPT_SHA512',
                array(),
                'password4',
                '#^\$6\$rounds=5000+\$[A-Za-z0-9+/]{16}\$[./A-Za-z0-9]{86}$#D',
            ),
            array(
                'CRYPT_SHA512',
                array('rounds' => '4000'),
                'password4',
                '#^\$6\$rounds=4000+\$[A-Za-z0-9+/]{16}\$[./A-Za-z0-9]{86}$#D',
            ),
        );
    }

    /**
     * @covers ::verify
     * @dataProvider providerTestVerify
     */
    public function testVerify($algo, $password, $hash, $expected)
    {
        $crypt = new Sha2();
        $this->assertSame($expected, $crypt->verify($password, $hash));
    }

    public function providerTestVerify()
    {
        return array(
            array(
                'CRYPT_SHA256',
                '31435008693ce6976f45dedc5532e2c1',
                '$5$rounds=5000$1234567890123456$c5PoOfE/uqUoVcX5JnakJmrcR2VFEHZmQ.KaLEtUlR4',
                true,
            ),
            array(
                'CRYPT_SHA256',
                'invalid',
                '$5$rounds=5000$1234567890123456$c5PoOfE/uqUoVcX5JnakJmrcR2VFEHZmQ.KaLEtUlR4',
                false,
            ),
            array(
                'CRYPT_SHA512',
                '31435008693ce6976f45dedc5532e2c1',
                '$6$rounds=5000$1234567890123456$QX1ndnRVi1/AxK0fPVQ4ZIQO.ThxS5VmQptu8AgQcjMCkETlLRDh4geJNhMtGvTWdQc.pFQ3l.TCeG/yvbukG.',
                true,
            ),
            array(
                'CRYPT_SHA512',
                'invalid',
                '$6$rounds=5000$1234567890123456$QX1ndnRVi1/AxK0fPVQ4ZIQO.ThxS5VmQptu8AgQcjMCkETlLRDh4geJNhMtGvTWdQc.pFQ3l.TCeG/yvbukG.',
                false,
            ),
        );
    }

    /**
     * @covers ::needsRehash
     * @dataProvider providerTestNeedsRehash
     */
    public function testNeedsRehash($algo, array $options, $hash, $expected)
    {
        $crypt = new Sha2();
        $crypt->setAlgo($algo);
        $crypt->setOptions($options);
        $this->assertEquals($expected, $crypt->needsRehash($hash));
    }

    public function providerTestNeedsRehash()
    {
        return array(

            // BOGUS source
            array(
                'CRYPT_SHA256',
                array(),
                'foobar',
                true,
            ),

            // EMPTY source
            array(
                'CRYPT_SHA256',
                array(),
                '',
                true,
            ),

            // PASSWORD_BCRYPT source
            array(
                'CRYPT_SHA256',
                array(),
                '$2y$10$duE5hc9IAC7JMBKxIZqXHu95QDpLtp1zk2SXjwZb9Sp2p0WDMCoSW',
                true,
            ),

            // CRYPT_SHA256 source - same rounds
            array(
                'CRYPT_SHA256',
                array('rounds' => 5000),
                '$5$rounds=5000$1234567890123456$c5PoOfE/uqUoVcX5JnakJmrcR2VFEHZmQ.KaLEtUlR4',
                false,
            ),

            // CRYPT_SHA256 source - diff rounds
            array(
                'CRYPT_SHA256',
                array('rounds' => 4000),
                '$5$rounds=5000$1234567890123456$c5PoOfE/uqUoVcX5JnakJmrcR2VFEHZmQ.KaLEtUlR4',
                true,
            ),

            // CRYPT_SHA512 source
            array(
                'CRYPT_SHA512',
                array('rounds' => 5000),
                '$6$rounds=5000$1234567890123456$QX1ndnRVi1/AxK0fPVQ4ZIQO.ThxS5VmQptu8AgQcjMCkETlLRDh4geJNhMtGvTWdQc.pFQ3l.TCeG/yvbukG.',
                false,
            ),

            // CRYPT_SHA512 source
            array(
                'CRYPT_SHA512',
                array('rounds' => 4000),
                '$6$rounds=5000$1234567890123456$QX1ndnRVi1/AxK0fPVQ4ZIQO.ThxS5VmQptu8AgQcjMCkETlLRDh4geJNhMtGvTWdQc.pFQ3l.TCeG/yvbukG.',
                true,
            ),
        );
    }

    /**
     * @return Salt
     */
    protected function getSaltMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Security\Password\Salt')
            ->setMethods($methods)
            ->getMock();
    }
}
