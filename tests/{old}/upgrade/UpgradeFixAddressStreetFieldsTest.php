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

require_once 'tests/{old}/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/7_FixAddressStreetFields.php';

class FixAddressStreetFieldsTest extends UpgradeTestCase
{
    protected $testClass = null;

    protected $sampleVardefs = array(
        'example' => array(
            'type' => 'id',
        ),
        'validCandidate_street' => array(
            'type' => 'varchar',
        ),
        'validCandidate_city' => array(
            'type' => 'varchar',
        ),
        'noCity_street' => array(
            'type' => 'varchar',
        ),
        'alreadyUpgraded_street' => array(
            'type' => 'text',
        ),
        'alreadyUpgraded_city' => array(
            'type' => 'varchar',
        ),
        'trailingCharacter_street_3' => array(
            'type' => 'varchar',
        ),
        'trailingCharacter_city' => array(
            'type' => 'varchar',
        ),

    );

    public function setup() {
        parent::setUp();
        $this->testClass = new SugarUpgradeFixAddressStreetFields($this->upgrader);
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testValidateStreetField() {
        $this->assertFalse($this->testClass->validateStreetField($this->sampleVardefs, 'example'));
        $this->assertTrue($this->testClass->validateStreetField($this->sampleVardefs, 'validCandidate_street'));
        $this->assertFalse($this->testClass->validateStreetField($this->sampleVardefs, 'validCandidate_city'));
        $this->assertFalse($this->testClass->validateStreetField($this->sampleVardefs, 'noCity_street'));
        $this->assertFalse($this->testClass->validateStreetField($this->sampleVardefs, 'alreadyUpgraded_street'));
        $this->assertFalse($this->testClass->validateStreetField($this->sampleVardefs, 'alreadyUpgraded_city'));
        $this->assertFalse($this->testClass->validateStreetField($this->sampleVardefs, 'trailingCharacter_street_3'));
        $this->assertFalse($this->testClass->validateStreetField($this->sampleVardefs, 'trailingCharacter_city'));
    }
}
