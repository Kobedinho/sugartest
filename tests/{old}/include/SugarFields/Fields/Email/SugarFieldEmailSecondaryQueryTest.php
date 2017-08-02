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
 

class SugarFieldEmailSecondaryQueryTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @ticket BR-1307
     */
    public function testAutoPrimary()
    {
        $emailsArray = array(
            array('id' => 'addr_1',
                  'bean_id' => 'bean_2',
                  'email_address' => 'addr_1@bean_2.com',
                  'invalid_email' => true,
                  'opt_out' => false,
            ),
            array('id' => 'addr_2',
                  'bean_id' => 'bean_2',
                  'email_address' => 'addr_2@bean_2.com',
                  'invalid_email' => false,
                  'opt_out' => false,
            ),
            array('id' => 'addr_1',
                  'bean_id' => 'bean_1',
                  'email_address' => 'addr_1@bean_1.com',
                  'invalid_email' => true,
                  'opt_out' => true,
            ),
            array('id' => 'addr_1',
                  'bean_id' => 'bean_3',
                  'email_address' => 'addr_1@bean_3.com',
                  'invalid_email' => false,
                  'opt_out' => false,
            ),
            array('id' => 'addr_2',
                  'bean_id' => 'bean_3',
                  'email_address' => 'addr_2@bean_3.com',
                  'invalid_email' => false,
                  'opt_out' => true,
            ),
            array('id' => 'addr_2',
                  'bean_id' => 'bean_1',
                  'email_address' => 'addr_2@bean_1.com',
                  'invalid_email' => false,
                  'opt_out' => false,
            ),
        );

        $queryMock = $this->createPartialMock('SugarQuery', array('execute'));
        $queryMock->expects($this->once())
                  ->method('execute')
                  ->will($this->returnValue($emailsArray));
        $emailMock = $this->createPartialMock('EmailAddresses', array('getEmailsQuery'));
        $emailMock->expects($this->once())
                  ->method('getEmailsQuery')
                  ->will($this->returnValue($queryMock));

        $seed = BeanFactory::newBean('Accounts');
        $seed->emailAddress = $emailMock;
        
        $beans = array();
        $beans['bean_3'] = BeanFactory::newBean('Accounts');
        $beans['bean_3']->id = 'bean_3';
        $beans['bean_1'] = BeanFactory::newBean('Accounts');
        $beans['bean_1']->id = 'bean_1';
        $beans['bean_2'] = BeanFactory::newBean('Accounts');
        $beans['bean_2']->id = 'bean_2';

        $field = SugarFieldHandler::getSugarField('email');
        $field->runSecondaryQuery('email', $seed, $beans);

        foreach ($beans as $bean) {
            foreach ($bean->emailAddress->addresses as $addr) {
                if (strpos($addr['email_address'], 'addr_1@') !== false) {
                    // Address 1 should always be primary
                    $this->assertTrue($addr['primary_address']==true, "Addr_1 is not primary for {$bean->id}");
                } else {
                    $this->assertFalse($addr['primary_address']==true, "Email {$addr['email_address']} is primary for {$bean->id}");                    
                }
            }
        }
    }
}
