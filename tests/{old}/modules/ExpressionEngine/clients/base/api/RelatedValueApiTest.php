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


/**
 * Tests RelatedValueApi.
 */
class RelatedValueApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarApi
     */
    protected $api;

    /**
     * @var SugarBean
     */
    protected $account;

    /**
     * @var SugarBean
     */
    protected $contact;

    protected function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
        $this->api = new RelatedValueApi();
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->contact = SugarTestContactUtilities::createContact('', array('first_name' => 'RS148Test_FName'));
        $this->account->load_relationship('contacts');
        $this->account->contacts->add($this->contact);
        $this->account->save();
    }

    protected function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @param array $fields
     * @param mixed $expected
     * @param bool $setRelatedId
     * @dataProvider relatedProvider
     */
    public function testRelatedValue($fields, $expected, $setRelatedId)
    {
        if ($setRelatedId) {
            $fields['contacts']['relId'] = $this->contact->id;
            $expected['contacts']['relId'] = $this->contact->id;
        }
        $encoded_fields = json_encode($fields);
        $result = $this->api->getRelatedValues(
            SugarTestRestUtilities::getRestServiceMock(),
            array('module' => 'Accounts', 'fields' => $encoded_fields, 'record' => $this->account->id)
        );
        $this->assertEquals($expected, $result);
    }

    public function relatedProvider()
    {
        return array(
            'empty' => array(
                array(),
                array(),
                false,
            ),
            'related' => array(
                array(
                    'contacts' => array(
                        'link' => 'contacts',
                        'type' => 'related',
                        'relate' => 'first_name',
                    ),
                ),
                array(
                    'contacts' => array(
                        'related' => array(
                            'first_name' => 'RS148Test_FName',
                        ),
                    ),
                ),
                true,
            ),
            'count' => array(
                array(
                    'contacts' => array(
                        'link' => 'contacts',
                        'type' => 'count',
                        'relate' => 'first_name',
                    ),
                ),
                array(
                    'contacts' => array(
                        'count' => 1,
                    ),
                ),
                false,
            ),
            'rollupMin' => array(
                array(
                    'contacts' => array(
                        'link' => 'contacts',
                        'type' => 'rollupMin',
                        'relate' => 'first_name',
                    ),
                ),
                array(
                    'contacts' => array(
                        'rollupMin' => array(
                            'first_name' => '',
                            'first_name_values' => array(),
                        ),
                    ),
                ),
                false,
            ),
            'rollupSum' => array(
                array(
                    'contacts' => array(
                        'link' => 'contacts',
                        'type' => 'rollupSum',
                        'relate' => 'first_name',
                    ),
                ),
                array(
                    'contacts' => array(
                        'rollupSum' => array(
                            'first_name' => 0,
                            'first_name_values' => array(),
                        ),
                    ),
                ),
                false,
            ),
        );
    }

    public function testRelatedValueWithCurrencyWillConvertToBase()
    {
        $mock_opp = $this->getMockBuilder('Opportunity')
            ->disableOriginalConstructor()
            ->setMethods(array('getFieldDefinition'))
            ->getMock();

        $mock_opp->expects($this->once())
            ->method('getFieldDefinition')
            ->willReturn(array('type' => 'currency'));

        $mock_opp->base_rate = '0.9';
        $mock_opp->amount = '90.000000';

        $mock_opp->field_defs = array(
            'amount' => array(
                'type' => 'currency',
            ),
        );

        $mock_acc = $this->getMockBuilder('Account')
            ->disableOriginalConstructor()
            ->setMethods(array('load_relationship'))
            ->getMock();

        $mock_acc->expects($this->once())
            ->method('load_relationship')
            ->willReturn(true);

        $mock_acc->id = 'test';


        $mock_acc_link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeans'))
            ->getMock();

        $mock_acc_link2->expects($this->once())
            ->method('getBeans')
            ->willReturn(array($mock_opp));

        $mock_acc->opportunities = $mock_acc_link2;

        $mock_api = $this->getMockBuilder('RelatedValueApi')
            ->disableOriginalConstructor()
            ->setMethods(array('loadBean'))
            ->getMock();

        $mock_api->expects($this->once())
            ->method('loadBean')
            ->willReturn($mock_acc);

        $fields = array(
            'opportunities' => array(
                'link' => 'opportunities',
                'type' => 'rollupSum',
                'relate' => 'amount',
            ),
        );

        $actual = $mock_api->getRelatedValues(
            SugarTestRestUtilities::getRestServiceMock(),
            array('module' => 'Accounts', 'fields' =>  json_encode($fields), 'record' => $mock_acc->id)
        );

        $expected = array(
            'opportunities' =>
                array(
                    'rollupSum' =>
                        array(
                            'amount' => '100.000000',
                            'amount_values' =>
                                array(
                                    '' => '100.000000',
                                ),
                        ),
                ),
        );

        $this->assertEquals($expected, $actual);
    }
}
