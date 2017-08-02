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

class BeanDuplicateCheckTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @group duplicatecheck
     */
    public function testConstructor_MetadataCountIsZero_TheStrategyRemainsFalse() {
        $bean               = self::getMock("Lead");
        $metadata           = array();
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $actual = $beanDuplicateCheck->getStrategy();
        self::assertFalse($actual, "The strategy should not have been changed from 'false'");
    }

    /**
     * @group duplicatecheck
     */
    public function testConstructor_MetadataCountIsTwo_TheStrategyRemainsFalse() {
        $bean               = self::getMock("Lead");
        $metadata           = array(
            'FilterDuplicateCheck' => array(
                'filter_template' => array(
                    array(
                        'account_name' => array(
                            '$starts' => '$account_name',
                        ),
                    ),
                ),
                'ranking_fields'  => array(
                    array(
                        'in_field_name'   => 'account_name',
                        'dupe_field_name' => 'account_name',
                    ),
                ),
            ),
            'ExtraDuplicateCheck'  => array(
                'filter_template' => array(
                    array(
                        'account_name' => array(
                            '$starts' => '$account_name',
                        ),
                    ),
                ),
                'ranking_fields'  => array(
                    array(
                        'in_field_name'   => 'account_name',
                        'dupe_field_name' => 'account_name',
                    ),
                ),
            ),
        );
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $actual = $beanDuplicateCheck->getStrategy();
        self::assertFalse($actual, "The strategy should not have been changed from 'false'");
    }

    /**
     * @group duplicatecheck
     */
    public function testConstructor_TheStrategyDefinedInTheMetadataIsInvalid_TheStrategyRemainsFalse() {
        $bean               = self::getMock("Lead");
        $metadata           = array(
            'Foobar' => array(
                'filter_template' => array(
                    array(
                        'account_name' => array(
                            '$starts' => '$account_name',
                        ),
                    ),
                ),
                'ranking_fields'  => array(
                    array(
                        'in_field_name'   => 'account_name',
                        'dupe_field_name' => 'account_name',
                    ),
                ),
            ),
        );
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $actual = $beanDuplicateCheck->getStrategy();
        self::assertFalse($actual, "The strategy should not have been changed from 'false'");
    }

    /**
     * @group duplicatecheck
     */
    public function testConstructor_MetadataCountIsOne_TheStrategyIsInitializedToTheStrategyDefinedInTheMetadata() {
        $bean               = self::getMock("Lead");
        $metadata           = array(
            'FilterDuplicateCheck' => array(
                'filter_template' => array(
                    array(
                        'account_name' => array(
                            '$starts' => '$account_name',
                        ),
                    ),
                ),
                'ranking_fields'  => array(
                    array(
                        'in_field_name'   => 'account_name',
                        'dupe_field_name' => 'account_name',
                    ),
                ),
            ),
        );
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $expected = "FilterDuplicateCheck";
        $actual   = $beanDuplicateCheck->getStrategy();
        self::assertInstanceOf($expected,
                               $actual,
                               "The strategy should have been changed to an instance of '{$expected}'");
    }

    /**
     * @group duplicatecheck
     */
    public function testFindDuplicates_TheStrategyIsFalse_ReturnsNull() {
        $bean               = self::getMock("Lead");
        $metadata           = array(); // invalid metadata forces the strategy to remain false
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $actual = $beanDuplicateCheck->findDuplicates();
        self::assertNull($actual, "BeanDuplicateCheck::findDuplicates should return null when the strategy is 'false'");
    }

    /**
     * @group duplicatecheck
     */
    public function testFindDuplicates_TheStrategyIsValid_TheFindDuplicatesMethodOnTheStrategyIsCalled() {
        $bean               = self::getMock("Lead");
        $metadata           = array(
            'DuplicateCheckMock' => array(
                'filter_template' => array(
                    array(
                        'account_name' => array(
                            '$starts' => '$account_name',
                        ),
                    ),
                ),
                'ranking_fields'  => array(
                    array(
                        'in_field_name'   => 'account_name',
                        'dupe_field_name' => 'account_name',
                    ),
                ),
            ),
        );
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $actual = $beanDuplicateCheck->findDuplicates();
        self::assertTrue($actual, "DuplicateCheckMock::findDuplicates should return 'true'");
    }
}

// need to make sure SugarApi is included when extending DuplicateCheckStrategy to avoid a fatal error

/**
 * Use the following class to test that DuplicateCheckStrategy::findDuplicates is called when it should be.
 */
class DuplicateCheckMock extends DuplicateCheckStrategy
{
    protected function setMetadata($metadata) {}

    public function findDuplicates() {
        return true;
    }

}
