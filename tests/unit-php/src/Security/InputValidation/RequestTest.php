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

namespace Sugarcrm\SugarcrmTestsUnit\Security\InputValidation;

use Sugarcrm\Sugarcrm\Security\Validator\Validator;
use Sugarcrm\Sugarcrm\Security\Validator\ConstraintBuilder;
use Sugarcrm\Sugarcrm\Security\InputValidation\Request;
use Sugarcrm\Sugarcrm\Security\InputValidation\Superglobals;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Psr\Log\LoggerInterface;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\InputValidation\Request
 *
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ConstraintBuilder
     */
    protected $constraintBuilder;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->validator = Validator::create();
        $this->logger = $this->createMock('Psr\Log\LoggerInterface');

        // setup ConstraintBuilder with additional fixture namespaces
        $this->constraintBuilder = $builder = new ConstraintBuilder();
        $builder->setNamespaces(array_merge(
            $builder->getNamespaces(), array('Symfony\Component\Validator\Tests\Fixtures')
        ));
    }

    /**
     * @covers ::getValidInput
     * @covers ::getValidInputGet
     * @covers ::getValidInputPost
     * @covers ::getValidInputRequest
     * @dataProvider providerTestGetValidInput
     */
    public function testGetValidInput(array $get, array $post, $key, $call, $constraint, $default, $expected)
    {
        $superglobals = new Superglobals($get, $post, $this->logger);
        $request = new Request($superglobals, $this->validator, $this->constraintBuilder, $this->logger);
        $this->assertEquals($expected, $request->$call($key, $constraint, $default));
    }

    public function providerTestGetValidInput()
    {
        return array(
            // $_GET test
            array(
                array('foo' => 'VALID'),
                array(),
                'foo',
                'getValidInputGet',
                'Assert\ConstraintA',
                null,
                'VALID',
            ),
            // $_POST test
            array(
                array(),
                array('foo' => 'VALID'),
                'foo',
                'getValidInputPost',
                'Assert\ConstraintA',
                null,
                'VALID',
            ),
            // $_REQUEST test
            array(
                array('foo' => 'INVALID'),
                array('foo' => 'VALID'),
                'foo',
                'getValidInputRequest',
                'Assert\ConstraintA',
                null,
                'VALID',
            ),
            // Test without null constraint
            array(
                array('foo' => 'VALID'),
                array(),
                'foo',
                'getValidInputGet',
                null,
                null,
                'VALID',
            ),
            // Test default
            array(
                array(),
                array(),
                'foo',
                'getValidInputGet',
                null,
                'default',
                'default',
            ),
            // Some actual tests
            array(
                array('bwcFrame' => '1'),
                array(),
                'bwcFrame',
                'getValidInputGet',
                array(
                    'Assert\Type' => array('type' => 'numeric'),
                    'Assert\Range' => array('min' => 0, 'max' => 1),
                ),
                null,
                '1',
            ),
            array(
                array('bwcFrame' => '0'),
                array(),
                'bwcFrame',
                'getValidInputGet',
                array(
                    'Assert\Type' => array('type' => 'numeric'),
                    'Assert\Range' => array('min' => 0, 'max' => 1),
                ),
                null,
                '0',
            ),
            /* Test relies on global state - skipping for now
            array(
                array('module' => 'Accounts'),
                array(),
                'module',
                'getValidInputRequest',
                'Assert\Mvc\ModuleName',
                null,
                'Accounts',
            ),*/
            array(
                array('current_page_by_query' => 'a:1:{s:3:"foo";s:3:"bar";}'),
                array(),
                'current_page_by_query',
                'getValidInputRequest',
                'Assert\PhpSerialized',
                null,
                array('foo' => 'bar'),
            ),
            array(
                array('current_page_by_query' => 'YToxOntzOjM6ImZvbyI7czozOiJiYXIiO30='),
                array(),
                'current_page_by_query',
                'getValidInputRequest',
                array('Assert\PhpSerialized' => array('base64Encoded' => true)),
                null,
                array('foo' => 'bar'),
            ),
            array(
                array('lvso' => 'DESC'),
                array(),
                'lvso',
                'getValidInputRequest',
                'Assert\Sql\OrderDirection',
                null,
                'DESC',
            ),
            array(
                array('lvso' => 'ASC'),
                array(),
                'lvso',
                'getValidInputRequest',
                'Assert\Sql\OrderDirection',
                null,
                'ASC',
            ),
            array(
                array('record' => '40a30045-2ab7-9c96-766d-563a3bb0d7ef'),
                array(),
                'record',
                'getValidInputRequest',
                'Assert\Guid',
                null,
                '40a30045-2ab7-9c96-766d-563a3bb0d7ef',
            ),
            array(
                array('column' => 'foobar'),
                array(),
                'column',
                'getValidInputRequest',
                'Assert\ComponentName',
                null,
                'foobar',
            ),
            array(
                array('ids' => '12345,67890'),
                array(),
                'ids',
                'getValidInputRequest',
                array(
                    'Assert\Delimited',
                ),
                null,
                array(
                    '12345',
                    '67890',
                ),
            ),
            array(
                array('records' => '40a30045-2ab7,9c96-766d-563a3bb0d7ef'),
                array(),
                'records',
                'getValidInputRequest',
                array(
                    'Assert\Delimited' => array(
                        'Assert\NotBlank',
                        'Assert\Guid',
                    ),
                ),
                null,
                array(
                    '40a30045-2ab7',
                    '9c96-766d-563a3bb0d7ef',
                ),
            ),
        );
    }

    /**
     * @covers ::getValidInput
     * @dataProvider providerTestGetInvalidInput
     * @expectedException \Sugarcrm\Sugarcrm\Security\InputValidation\Exception\ViolationException
     */
    public function testGetInvalidInput($data, $constraint)
    {
        $superglobals = new Superglobals(array('data' => $data), array(), $this->logger);
        $request = new Request($superglobals, $this->validator, $this->constraintBuilder, $this->logger);
        $request->getValidInput(Superglobals::GET, 'data', $constraint);
    }

    public function providerTestGetInvalidInput()
    {
        return array(
            array(
                'xxx' . chr(0),
                null
            ),
            array(
                array('xxx' . chr(0)),
                null
            ),
        );
    }

    /**
     * @covers ::getValidInput
     * @expectedException \Sugarcrm\Sugarcrm\Security\InputValidation\Exception\ViolationException
     */
    public function testGetValidInputViolationException()
    {
        $superglobals = new Superglobals(array('foo' => 'bar'), array(), $this->logger);
        $request = new Request($superglobals, $this->validator, $this->constraintBuilder, $this->logger);
        $request->getValidInput(Superglobals::GET, 'foo', 'Assert\FailingConstraint');
    }

    /**
     * @covers ::getValidInput
     * @expectedException \Sugarcrm\Sugarcrm\Security\InputValidation\Exception\SuperglobalException
     */
    public function testGetValidInputSuperglobalException()
    {
        $superglobals = new Superglobals(array(), array(), $this->logger);
        $request = new Request($superglobals, $this->validator, $this->constraintBuilder, $this->logger);
        $request->getValidInput('foo', 'bar');
    }

    /**
     * @covers ::getValidInput
     */
    public function testSoftFailNoException()
    {
        $superglobals = new Superglobals(array('foo' => 'VALID'), array(), $this->logger);
        $request = new Request($superglobals, $this->validator, $this->constraintBuilder, $this->logger);
        $request->setSoftFail(true);
        $request->getValidInput(Superglobals::GET, 'foo', array(
            'Assert\FailingConstraint',
            'Assert\ConstraintA',
            'Assert\FailingConstraint',
        ));
        $this->assertEquals(2, count($request->getViolations()));
    }
}
