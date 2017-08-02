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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Validator;

use Sugarcrm\Sugarcrm\Security\Validator\ConstraintBuilder;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints as AssertBasic;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\ConstraintBuilder
 *
 */
class ConstraintBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConstraintBuilder
     */
    protected $builder;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->builder = new ConstraintBuilder();
    }

    /**
     * @covers ::build
     * @covers ::buildConstraint
     * @covers ::parseOptions
     * @covers ::isAssert
     * @covers ::getAssertClass
     * @dataProvider providerTestValidBuild
     */
    public function testValidBuild($constraints, $expected)
    {
        $this->assertEquals($expected, $this->builder->build($constraints));
    }

    public function providerTestValidBuild()
    {
        return array(

            // empty/null constraints
            array(
                '',
                array(),
            ),
            array(
                null,
                array(),
            ),

            // basic constraint as string
            array(
                'Assert\NotBlank',
                array(
                    new AssertBasic\NotBlank(),
                ),
            ),

            // single constraint with options
            array(
                array(
                    'Assert\Range' => array(
                        'min' => 5,
                        'max' => 6,
                    ),
                ),
                array(
                    new AssertBasic\Range(array(
                        'min' => 5,
                        'max' => 6,
                    )),
                ),
            ),

            // sugar constraint as string
            array(
                'Assert\Mvc\ModuleName',
                array(
                    new Assert\Mvc\ModuleName(),
                ),
            ),

            // multiple constraints with/without options
            array(
                array(
                    'Assert\NotBlank',
                    'Assert\Type' => array(
                        'type' => 'string',
                    ),
                ),
                array(
                    new AssertBasic\NotBlank(),
                    new AssertBasic\Type(array('type' => 'string')),
                )
            ),

            // full mix including collection
            array(
                array(
                    'Assert\Collection' => array(
                        'fields' => array(
                            'email' => array(
                                'Assert\NotBlank',
                                'Assert\Email' => array(
                                    'message' => 'foo',
                                ),
                            ),
                            'username' => 'Assert\Mvc\ModuleName',
                            'personal' => array(
                                'Assert\Required' => array(
                                    'Assert\NotBlank',
                                    'Assert\Email' => array(
                                        'message' => 'bar',
                                    ),
                                ),
                            ),
                        ),
                        'allowMissingFields' => true,
                    ),
                ),
                array(
                    new AssertBasic\Collection(array(
                        'fields' => array(
                            'email' => array(
                                new AssertBasic\NotBlank(),
                                new AssertBasic\Email(array('message' => 'foo')),
                            ),
                            'username' => array(
                                new Assert\Mvc\ModuleName(),
                            ),
                            'personal' => array(
                                new AssertBasic\Required(array(
                                    new AssertBasic\NotBlank(),
                                    new AssertBasic\Email(array('message' => 'bar')),
                                )),
                            ),
                        ),
                        'allowMissingFields' => true,
                    )),
                ),
            ),
        );
    }

    /**
     * @covers ::build
     * @covers ::buildConstraint
     * @covers ::getAssertClass
     * @dataProvider providerTestInvalidBuild
     */
    public function testInvalidBuild($constraints, $msg)
    {
        $this->setExpectedException('\Sugarcrm\Sugarcrm\Security\Validator\Exception\ConstraintBuilderException', $msg);
        $this->builder->build($constraints);
    }

    public function providerTestInvalidBuild()
    {
        return array(
            array(
                'Assert\Foobar',
                'Cannot find class for assert "Assert\Foobar"',
            ),
            array(
                array('Assert\Foobar', 'foobar'),
                'Cannot find class for assert "Assert\Foobar"',
            ),
            array(
                'Assertxxx\Foobar',
                'Invalid constraint "Assertxxx\Foobar", should start with "Assert\"',
            ),
            array(
                array('Assertxxx\Foobar' => array()),
                'Invalid constraint "Assertxxx\Foobar", should start with "Assert\"',
            ),
            array(
                array('Assert\Range' => true),
                'Assert options expected to be an array, boolean given',
            ),
        );
    }
}
