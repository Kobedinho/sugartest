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
class PMSEFieldParserTest extends PHPUnit_Framework_TestCase
{
    protected $dataParser;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
    }

    public function testParseCriteriaEqual()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(array('parseTokenValue'))
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('10/10/2013'));

        $beanList = array('Leads' => 'Lead');
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->date = '10/10/2013';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            ),
            'date' => array(
                'type' => 'date'
            )
        );
        $preCondition =  json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "date",
                "expOperator": "equals",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: == \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = array(
            0 =>
            (object)
            array(
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equals',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: == "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => '10/10/2013',
            ));
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }
    public function testParseCriteriaNotEquals()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(array('parseTokenValue' ))
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = array('Leads' => 'Lead');
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            )
        );
        $preCondition =  json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "not_equals",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: == \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = array(
            0 =>
                (object)
                array(
                    'expDirection' => 'after',
                    'expModule' => 'Leads',
                    'expField' => 'account_name',
                    'expOperator' => 'not_equals',
                    'expValue' => 'ONE',
                    'expType' => 'MODULE',
                    'expLabel' => 'Account Name: == "ONE"',
                    'expToken' => '{::future::Leads::account_name::}',
                    'currentValue' => 'ROCKSTAR',
                ));
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }
    public function testParseCriteriaMajorEqualThan()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(array('parseTokenValue'))
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = array('Leads' => 'Lead');
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            )
        );
        $preCondition =  json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "major_equals_than",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: == \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = array(
            0 =>
                (object)
                array(
                    'expDirection' => 'after',
                    'expModule' => 'Leads',
                    'expField' => 'account_name',
                    'expOperator' => 'major_equals_than',
                    'expValue' => 'ONE',
                    'expType' => 'MODULE',
                    'expLabel' => 'Account Name: == "ONE"',
                    'expToken' => '{::future::Leads::account_name::}',
                    'currentValue' => 'ROCKSTAR',
                ));
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }
    public function testParseCriteriaMinorEqualThan()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(array('parseTokenValue'))
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = array('Leads' => 'Lead');
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            )
        );
        $preCondition =  json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "minor_equals_than",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: == \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = array(
            0 =>
                (object)
                array(
                    'expDirection' => 'after',
                    'expModule' => 'Leads',
                    'expField' => 'account_name',
                    'expOperator' => 'minor_equals_than',
                    'expValue' => 'ONE',
                    'expType' => 'MODULE',
                    'expLabel' => 'Account Name: == "ONE"',
                    'expToken' => '{::future::Leads::account_name::}',
                    'currentValue' => 'ROCKSTAR',
                ));
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }
    public function testParseCriteriaMinorThan()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(array('parseTokenValue'))
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = array('Leads' => 'Lead');
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            )
        );
        $preCondition =  json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "minor_than",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: == \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = array(
            0 =>
                (object)
                array(
                    'expDirection' => 'after',
                    'expModule' => 'Leads',
                    'expField' => 'account_name',
                    'expOperator' => 'minor_than',
                    'expValue' => 'ONE',
                    'expType' => 'MODULE',
                    'expLabel' => 'Account Name: == "ONE"',
                    'expToken' => '{::future::Leads::account_name::}',
                    'currentValue' => 'ROCKSTAR',
                ));
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }
    public function testParseCriteriaMajorThan()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(array('parseTokenValue'))
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = array('Leads' => 'Lead');
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            )
        );
        $preCondition =  json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "major_than",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: == \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = array(
            0 =>
                (object)
                array(
                    'expDirection' => 'after',
                    'expModule' => 'Leads',
                    'expField' => 'account_name',
                    'expOperator' => 'major_than',
                    'expValue' => 'ONE',
                    'expType' => 'MODULE',
                    'expLabel' => 'Account Name: == "ONE"',
                    'expToken' => '{::future::Leads::account_name::}',
                    'currentValue' => 'ROCKSTAR',
                ));
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaDistinct()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(array('parseTokenValue'))
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('10/10/2013'));

        $beanList = array('Leads' => 'Lead');
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->datetime = '10/10/2013';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            ),
            'datetime' => array(
                'type' => 'datetime'
            ),
        );
        $preCondition =  json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "datetime",
                "expOperator": "not_equals",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: != \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);

        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = array(
            0 =>
            (object)
            array(
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equals',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: == "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => '10/10/2013',
            ));
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaMajorEquals()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(array('parseTokenValue'))
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = array('Leads' => 'Lead');
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            )
        );
        $preCondition =  json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "major_equals_than",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: >= \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = array(
            0 =>
            (object)
            array(
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equals',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: == "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ));
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaMajor()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(array('parseTokenValue'))
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = array('Leads' => 'Lead');
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            )
        );
        $preCondition =  json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "major_than",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: > \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = array(
            0 =>
            (object)
            array(
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equals',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: > "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ));
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaMinorEquals()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(array('parseTokenValue'))
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));


        $beanList = array('Leads' => 'Lead');
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            )
        );
        $preCondition =  json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "minor_equals_than",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: <= \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteriaToken($preCondition[0]);
        $postCondition = array(
            0 =>
            (object)
            array(
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equals',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: == "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ));
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaMinor()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(array('parseTokenValue'))
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = array('Leads' => 'Lead');
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            )
        );
        $preCondition =  json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "minor_than",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: < \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = array(
            0 =>
            (object)
            array(
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equals',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: == "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ));
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaWithin()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(array('parseTokenValue'))
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = array('Leads' => 'Lead');
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            )
        );
        $preCondition =  json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "within",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: within \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = array(
            0 =>
            (object)
            array(
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equals',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: within "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ));
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseCriteriaNotWithin()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(array('parseTokenValue'))
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = array('Leads' => 'Lead');
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            )
        );
        $preCondition =  json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "not_within",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: not_within \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = array(
            0 =>
            (object)
            array(
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equals',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: not_within "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ));
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }


    public function testParseCriteriaDefault()
    {
        $this->dataParser = $this->getMockBuilder('PMSEFieldParser')
            ->disableOriginalConstructor()
            ->setMethods(array('parseTokenValue'))
            ->getMock();

        $this->dataParser->expects($this->once())
            ->method('parseTokenValue')
            ->will($this->returnValue('ROCKSTAR'));

        $beanList = array('Leads' => 'Lead');
        $this->dataParser->setBeanList($beanList);
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            )
        );
        $preCondition =  json_decode('[{
                "expDirection": "after",
                "expModule": "Leads",
                "expField": "account_name",
                "expOperator": "equal",
                "expValue": "ONE",
                "expType": "MODULE",
                "expLabel": "Account Name: == \"ONE\""
              }]');
        $this->dataParser->setEvaluatedBean($beanObject);
        $processedCondition = $this->dataParser->parseCriteria($preCondition[0]);
        $postCondition = array(
            0 =>
            (object)
            array(
                'expDirection' => 'after',
                'expModule' => 'Leads',
                'expField' => 'account_name',
                'expOperator' => 'equal',
                'expValue' => 'ONE',
                'expType' => 'MODULE',
                'expLabel' => 'Account Name: not_within "ONE"',
                'expToken' => '{::future::Leads::account_name::}',
                'currentValue' => 'ROCKSTAR',
            ));
        $this->assertEquals($postCondition[0]->currentValue, $processedCondition->currentValue);
    }

    public function testParseTokenValue()
    {
        $preferencesArray = array();
        $beanList = array('Leads' => 'Lead');
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->parent_type = 'Opprtunities';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            )
        );

        $token = "{::future::Leads::email_addresses_primary::}";
        $expectedToken = "rock.star@gmail.com";
        $this->dataParser->setEvaluatedBean($beanObject);
        $this->dataParser->setBeanList($beanList);
        $processedToken = $this->dataParser->parseTokenValue($token);
        $this->assertEquals($expectedToken, $processedToken);
    }

    public function testParseTokenValueToken()
    {
        $preferencesArray = array();
        $beanList = array('Leads' => 'Lead','Notes' => 'Notes');
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->do_not_call = 'true';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Leads';
        $beanObject->parent_type = 'Opprtunities';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            )
        );

        $token = "{::future::Leads::do_not_call::}";
        $expectedToken = true;
        $this->dataParser->setEvaluatedBean($beanObject);
        $this->dataParser->setBeanList($beanList);
        $processedToken = $this->dataParser->parseTokenValue($token);
        $this->assertSame($expectedToken, $processedToken);
    }

    public function testParseTokenValueTokenEmptyModules()
    {
        $preferencesArray = array();
        $beanList = array('Leads' => 'Lead','Notes' => 'Notes');
        $beanObject = $this->getMockBuilder('Lead')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $beanObject->account_name = 'ROCKSTAR';
        $beanObject->email_addresses_primary = 'rock.star@gmail.com';
        $beanObject->do_not_call = 'true';
        $beanObject->phone_mobile = '7775555';
        $beanObject->module_name = 'Notes';
        $beanObject->parent_type = 'Opprtunities';
        $beanObject->field_defs = array(
            'account_name' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'email_addresses_primary' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'phone_mobile' => array(
                'type' => 'varchar',
                'dbtype' => 'char',
            ),
            'probability' => array(
                'type' => 'int',
                'dbtype' => 'double',
            ),
            'amount' => array(
                'type' => 'float',
                'dbtype' => 'double',
            ),
            'do_not_call' => array(
                'type' => 'bool'
            )
        );

        $token = "{::future::Leads::do_not_call::}";
        $expectedToken = true;
        $this->dataParser->setEvaluatedBean($beanObject);
        $this->dataParser->setBeanList($beanList);
        $processedToken = $this->dataParser->parseTokenValue($token);
        $this->assertSame($expectedToken, $processedToken);
    }

    public function testDecomposeToken()
    {
        $token = "{::future::Leads::email_addresses_primary::}";
        $expectedToken = array('future', 'Leads', 'email_addresses_primary');
        $processedToken = $this->dataParser->decomposeToken($token);
        $this->assertEquals($expectedToken, $processedToken);
    }

    public function testDecomposeTokenEmpty()
    {
        $token = "";
        $expectedToken = array();
        $processedToken = $this->dataParser->decomposeToken($token);
        $this->assertEquals($expectedToken, $processedToken);
    }
}
