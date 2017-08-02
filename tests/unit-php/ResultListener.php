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

namespace Sugarcrm\SugarcrmTestsUnit;

/**
 *
 * Result listener ensuring that any risky or incomplete tests are marked a failures,
 * since they should not be merged into master
 *
 */
class ResultListener extends \PHPUnit_Framework_BaseTestListener
{
    /**
     * {@inheritdoc}
     */
    public function addRiskyTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        if (!$test instanceof \PHPUnit_Framework_TestCase) {
            return;
        }

        $this->raiseFailure($test, 'Risky tests are NOT allowed', $time);
    }

    /**
     * {@inheritdoc}
     */
    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        if (!$test instanceof \PHPUnit_Framework_TestCase) {
            return;
        }

        $this->raiseFailure($test, 'Incomplete tests are NOT allowed', $time);
    }

    /**
     * Raise failure on given test case
     * @param \PHPUnit_Framework_TestCase $test
     * @param string $failure Failure message
     * @param double $time Elapsed time
     */
    protected function raiseFailure(\PHPUnit_Framework_TestCase $test, $failure, $time)
    {
        if ($resultObject = $test->getTestResultObject()) {
            $e = new \PHPUnit_Framework_AssertionFailedError($failure);
            $resultObject->addFailure($test, $e, $time);
        }
    }
}
