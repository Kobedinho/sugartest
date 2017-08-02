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

namespace Sugarcrm\SugarcrmTestsUnit\Console\Command\Password;

use Symfony\Component\Console\Tester\CommandTester;
use Sugarcrm\Sugarcrm\Security\Password\Hash;
use Sugarcrm\Sugarcrm\Security\Password\Backend\Native;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\Command\Password\WeakHashesCommand
 *
 */
class WeakHashesCommandTestCase extends AbstractPasswordCommandTestCase
{
    /**
     * @covers ::configure
     * @covers ::execute
     * @covers ::isWeak
     * @dataProvider providerTestExecute
     */
    public function testExecute(array $hashes, Hash $hash, $output)
    {
        $cmd = $this->getMockBuilder('Sugarcrm\Sugarcrm\Console\Command\Password\WeakHashesCommand')
            ->setMethods(array('getHashInstance', 'getUserHashes'))
            ->getMock();

        $cmd->expects($this->once())
            ->method('getUserHashes')
            ->will($this->returnValue($hashes));

        $cmd->expects($this->once())
            ->method('getHashInstance')
            ->will($this->returnValue($hash));

        $tester = new CommandTester($cmd);
        $tester->execute(array());

        $output = self::$fixturePath . $output;
        $this->assertStringEqualsFile($output, $tester->getDisplay(true));
    }

    public function providerTestExecute()
    {
        $hash1 = new Hash(new Native());

        $hash2 = new Hash(new Native());
        $hash2->setRehash(false);

        $uHashSha2 = '$6$rounds=5000$ZR54/levaAoI4zxk$fI/akuHwdnM.m6UjLCBae8Tkd9bEP/poxTFlq3.DPcyz';
        $uHashSha2 .= 'Fs/o/Td1t7nxaAEl7VWK68sv8JT2prteiIMnV1rcC1';
        $uHashBlowFish = '$2y$10$AYpljzARMrbr30FYluHQJ.9wIu9Ou0k0Yh1MWelAMxu64qi5dHmVy';
        $uHashMd5 = 'f561aaf6ef0bf14d4208bb46a4ccb3ad';

        return array(

            // No users
            array(
                array(),
                $hash1,
                'WeakHashesCommand_0.txt',
            ),

            // Users without weak and no-rehash
            array(
                array(
                    array(
                        'id' => '123456',
                        'user_name' => 'skymeyer',
                        'user_hash' => $uHashBlowFish,
                        'first_name' => 'Jelle',
                        'last_name' => 'Vink',
                        'employee_status' => 'Active',
                    ),
                ),
                $hash1,
                'WeakHashesCommand_0.txt',
            ),

            // Weak password (rehash is implied)
            array(
                array(
                    array(
                        'id' => '123456',
                        'user_name' => 'skymeyer',
                        'user_hash' => $uHashBlowFish,
                        'first_name' => 'Jelle',
                        'last_name' => 'Vink',
                        'employee_status' => 'Active',
                    ),
                    array(
                        'id' => '456789',
                        'user_name' => 'weak',
                        'user_hash' => $uHashMd5,
                        'first_name' => 'Foo',
                        'last_name' => 'Bar',
                        'employee_status' => 'Active',
                    ),
                ),
                $hash1,
                'WeakHashesCommand_2.txt',
            ),

            // Weak password (rehash disabled)
            array(
                array(
                    array(
                        'id' => '123456',
                        'user_name' => 'skymeyer',
                        'user_hash' => $uHashBlowFish,
                        'first_name' => 'Jelle',
                        'last_name' => 'Vink',
                        'employee_status' => 'Active',
                    ),
                    array(
                        'id' => '456789',
                        'user_name' => 'weak',
                        'user_hash' => $uHashMd5,
                        'first_name' => 'Foo',
                        'last_name' => 'Bar',
                        'employee_status' => 'Active',
                    ),
                ),
                $hash2,
                'WeakHashesCommand_3.txt',
            ),

            // Rehash only
            array(
                array(
                    array(
                        'id' => '123456',
                        'user_name' => 'skymeyer',
                        'user_hash' => $uHashBlowFish,
                        'first_name' => 'Jelle',
                        'last_name' => 'Vink',
                        'employee_status' => 'Active',
                    ),
                    array(
                        'id' => '456789',
                        'user_name' => 'rehash',
                        'user_hash' => $uHashSha2,
                        'first_name' => 'Foo',
                        'last_name' => 'Bar',
                        'employee_status' => 'Active',
                    ),
                ),
                $hash1,
                'WeakHashesCommand_4.txt',
            ),

            // Rehash only, but rehash disabled
            array(
                array(
                    array(
                        'id' => '123456',
                        'user_name' => 'skymeyer',
                        'user_hash' => $uHashBlowFish,
                        'first_name' => 'Jelle',
                        'last_name' => 'Vink',
                        'employee_status' => 'Active',
                    ),
                    array(
                        'id' => '456789',
                        'user_name' => 'rehash',
                        'user_hash' => $uHashSha2,
                        'first_name' => 'Foo',
                        'last_name' => 'Bar',
                        'employee_status' => 'Active',
                    ),
                ),
                $hash2,
                'WeakHashesCommand_0.txt',
            ),
        );
    }
}
