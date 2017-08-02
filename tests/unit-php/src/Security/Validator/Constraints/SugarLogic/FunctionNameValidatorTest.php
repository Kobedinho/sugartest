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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints\SugarLogic;

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\SugarLogic\FunctionName;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\SugarLogic\FunctionNameValidator;
use Sugarcrm\SugarcrmTests\Security\Validator\Constraints\AbstractConstraintValidatorTest;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\SugarLogic\FunctionNameValidator
 *
 */
class FunctionNameValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new FunctionNameValidator();
    }

    /**
     * @covers ::validate
     */
    public function testNullIsValid()
    {
        $constraint = new FunctionName();
        $this->validator->validate(null, $constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testEmptyStringIsValid()
    {
        $constraint = new FunctionName();
        $this->validator->validate('', $constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $constraint = new FunctionName();
        $this->validator->validate(new \stdClass(), $constraint);
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestValidValues
     */
    public function testValidValues($value)
    {
        $this->validator->validate($value, new FunctionName());
        $this->assertNoViolation();
    }

    public function providerTestValidValues()
    {
        return array(
            array('functionName'),
            array('function-Name'),
            array('function-Name9'),
        );
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValues($value, $code)
    {
        $constraint = new FunctionName(array(
            'message' => 'testMessage',
        ));

        $this->validator->validate($value, $constraint);

        $this->buildViolation('testMessage')
            ->setInvalidValue($value)
            ->setParameter('%msg%', 'must only use word characters and -')
            ->setCode($code)
            ->assertRaised();
    }

    public function providerTestInvalidValues()
    {
        return array(
            array(
                'Function%Name',
                FunctionName::ERROR_INVALID_FUNCTION_NAME,
            ),
        );
    }
}
