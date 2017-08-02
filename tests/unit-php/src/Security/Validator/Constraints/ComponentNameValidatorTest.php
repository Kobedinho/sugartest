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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints;

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\ComponentName;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\ComponentNameValidator;
use Sugarcrm\SugarcrmTests\Security\Validator\Constraints\AbstractConstraintValidatorTest;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\ComponentNameValidator
 *
 */
class ComponentNameValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new ComponentNameValidator(array(
            'SELECT' => true,
            'UPDATE' => true,
            'DELETE' => true,
        ));
    }

    /**
     * @covers ::validate
     */
    public function testNullIsValid()
    {
        $this->validator->validate(null, new ComponentName());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testEmptyStringIsValid()
    {
        $this->validator->validate('', new ComponentName());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->validate(new \stdClass(), new ComponentName());
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestValidValues
     */
    public function testValidValues($value)
    {
        $this->validator->validate($value, new ComponentName());
        $this->assertNoViolation();
    }

    public function providerTestValidValues()
    {
        return array(
            array('id'),
            array('known_module'),
            array('this-is-it'),
            array('view8'),
        );
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValues($value, $code, $msg, $allowReservedSqlKeywords)
    {
        $constraint = new ComponentName(array(
            'message' => 'testMessage',
            'allowReservedSqlKeywords' => $allowReservedSqlKeywords,
        ));

        $this->validator->validate($value, $constraint);

        $this->buildViolation('testMessage')
            ->setParameter('%msg%', $msg)
            ->setCode($code)
            ->setInvalidValue($value)
            ->assertRaised();
    }

    public function providerTestInvalidValues()
    {
        return array(
            array(
                'invalid+chars',
                ComponentName::ERROR_INVALID_COMPONENT_NAME,
                'must start with a letter and may only consist of letters, numbers, hyphens and underscores.',
                true,
            ),
            array(
                'SELECT',
                ComponentName::ERROR_RESERVED_KEYWORD,
                'reserved SQL keyword not allowed',
                false,
            ),
        );
    }
}
