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
require_once 'upgrade/scripts/post/9_LockedFieldsToRelateCollection.php';

/**
 * Test for updating locked fields into the new architecture
 */
class SugarUpgradeLockedFieldsToRelateCollectionTest extends UpgradeTestCase
{
    /**
     * Tests the criteria builder
     */
    public function testGetAssembledQuery()
    {
        // Our upgrade object
        $ug = new SugarUpgradeLockedFieldsToRelateCollection($this->upgrader);

        // Our test method... gets a SugarQuery object with all the settings set
        $q = $ug->getAssembledQuery();

        // All we really want to do is ensure the query has our relevant join clause
        // correctly set
        $sql = $q->compile()->getSQL();

        // Handle the test
        $this->assertContains('flow.pro_id = pd.id', $sql);
    }

    public function testGetLogMessage()
    {
        // Create our empty SugarBean that is needed for passing into the method
        $bean = BeanFactory::newBean('Empty');
        $bean->id = '123-456';

        // Get our upgrade object
        $ug = new SugarUpgradeLockedFieldsToRelateCollection($this->upgrader);

        // Set an empty bean into the PD object on the upgrader
        $ug->setPD('Empty');
        $ug->pd->id = 'abc-foo';

        $actual = $ug->getLogMessage($bean);
        $expect = 'Failed to create relationship for record: 123-456 pd: abc-foo';

        $this->assertEquals($expect, $actual);
    }
}
