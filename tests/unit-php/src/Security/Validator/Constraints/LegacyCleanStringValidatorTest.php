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

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\LegacyCleanStringValidator;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\LegacyCleanString;
use Sugarcrm\SugarcrmTests\Security\Validator\Constraints\AbstractConstraintValidatorTest;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\LegacyCleanStringValidator
 *
 */
class LegacyCleanStringValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new LegacyCleanStringValidator();
    }

    /**
     * @coversNothing
     * @expectedException \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testExpectValidFilter()
    {
        $constraint = new LegacyCleanString(array(
            'filter' => 'foobar',
        ));

        $this->validator->validate('xyz', $constraint);
    }

    /**
     * @covers ::validate
     * @covers ::validateRecursive
     * @dataProvider providerTestValidValues
     */
    public function testValidValues($filter, $value)
    {
        $constraint = new LegacyCleanString(array(
            'filter' => $filter,
        ));

        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     * @covers ::validateRecursive
     * @dataProvider providerTestValidValues
     */
    public function testValidValuesRecursive($filter, $value)
    {
        $recursiveValue = array($value, array($value, $value));

        $constraint = new LegacyCleanString(array(
            'filter' => $filter,
        ));

        $this->validator->validate($recursiveValue, $constraint);
        $this->assertNoViolation();
    }

    public function providerTestValidValues()
    {
        return array(
            array(LegacyCleanString::STANDARD, 'teststring'),
            array(LegacyCleanString::STANDARD, 'foo_bar@sugar-crm.com'),
            array(LegacyCleanString::STANDARD, '4.2.1'),
            array(LegacyCleanString::STANDARDSPACE, 'more foo_bar@sugar-crm.com'),
            array(LegacyCleanString::FILE, 'FileName-v1.0_latest.zip'),
            array(LegacyCleanString::NUMBER, '987654'),
            array(LegacyCleanString::NUMBER, '-987654'),
            array(LegacyCleanString::NUMBER, '98-7654'),
            array(LegacyCleanString::SQL_COLUMN_LIST, 'date(d),now_time.x'),
            array(LegacyCleanString::PATH_NO_URL, '/etc/passwd'),
            array(LegacyCleanString::PATH_NO_URL, '../../etc/passwd'),
            // TODO .. to be completed
        );
    }

    /**
     * @covers ::validate
     * @covers ::validateRecursive
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValues($filter, $value)
    {
        $constraint = new LegacyCleanString(array(
            'message' => 'testMessage',
            'filter' => $filter,
        ));

        $this->validator->validate($value, $constraint);

        $this->buildViolation('testMessage')
            ->setCode(LegacyCleanString::FILTER_ERROR)
            ->setParameter('%filter%', $filter)
            ->setCause($filter)
            ->setInvalidValue($value)
            ->assertRaised();
    }

    /**
     * @covers ::validate
     * @covers ::validateRecursive
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValuesRecursive($filter, $value)
    {
       $recursiveValue = array($value, array($value, $value));

       $constraint = new LegacyCleanString(array(
           'message' => 'testMessage',
           'filter' => $filter,
       ));

       $this->validator->validate($recursiveValue, $constraint);

       $this->buildViolation('testMessage')
           ->setCode(LegacyCleanString::FILTER_ERROR)
           ->setParameter('%filter%', $filter)
           ->setCause($filter)
           ->setInvalidValue($value)

           ->buildNextViolation('testMessage')
           ->setCode(LegacyCleanString::FILTER_ERROR)
           ->setParameter('%filter%', $filter)
           ->setCause($filter)
           ->setInvalidValue($value)

           ->buildNextViolation('testMessage')
           ->setCode(LegacyCleanString::FILTER_ERROR)
           ->setParameter('%filter%', $filter)
           ->setCause($filter)
           ->setInvalidValue($value)
           ->assertRaised();
    }

    public function providerTestInvalidValues()
    {
        return array(
            // non strings should always fail regardless the filter
            array(LegacyCleanString::STANDARD, null),
            array(LegacyCleanString::STANDARD, new \stdClass()),
            array(LegacyCleanString::STANDARD, 17),
            array(LegacyCleanString::STANDARD, 23.69),

            // filter specific failures
            array(LegacyCleanString::STANDARD, 'teststring!'),
            array(LegacyCleanString::STANDARD, 'teststring!'),
            array(LegacyCleanString::STANDARD, 'test string'),
            array(LegacyCleanString::STANDARD, '4.2$1'),
            array(LegacyCleanString::STANDARDSPACE, 'test, string'),
            array(LegacyCleanString::FILE, '/etc/passwd'),
            array(LegacyCleanString::FILE, '../goback.php'),
            array(LegacyCleanString::NUMBER, '987654.123'),
            array(LegacyCleanString::NUMBER, '987654,123'),
            array(LegacyCleanString::NUMBER, 'abc'),
            array(LegacyCleanString::SQL_COLUMN_LIST, 'date(d)"'),
            array(LegacyCleanString::PATH_NO_URL, 'https://www.google.com'),
            array(LegacyCleanString::PATH_NO_URL, 'file:///etc/passwd'),
            // TODO .. to be completed
        );
    }
}
