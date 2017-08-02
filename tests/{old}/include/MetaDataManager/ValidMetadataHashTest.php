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

require_once 'tests/{old}/SugarTestACLUtilities.php';

/**
 * Testing valid caches to prevent error 412 loops.
 */
class ValidMetadataHashTest extends Sugar_PHPUnit_Framework_TestCase
{

    protected $baseHash = "1234asdf";
    protected $portalHash = "zzz123";

    public function testHashValid()
    {
        $hashes = array(
            'meta_hash_base' => $this->baseHash,
            'meta_hash_portal_base' => $this->portalHash,
        );

        // If we are getting a system level warning the hashes won't match up
        $systemStatus = apiCheckSystemStatus(true);
        if ($systemStatus !== true) {
            $this->markTestSkipped("Can't test metadata hashing with bad system status.");
        }

        // Get the base metadata manager
        $mm = new ValidMetadataHashMetadataManager();
        $mm->setHashCacheForTest($hashes);

        $this->assertTrue(
            $mm->isMetadataHashValid($this->baseHash, "base"),
            "Base metadata hash should have been valid but was not"
        );
        $this->assertFalse(
            $mm->isMetadataHashValid("invalid Hash", "base"),
            "Base metadata hash should have been invalid, but was valid"
        );

        // Get the portal metadata manager
        $mm = new ValidMetadataHashMetadataManager(array('portal'));
        $this->assertTrue(
            $mm->isMetadataHashValid($this->portalHash, "portal"),
            "Portal metadata hash should have been valid but was not"
        );
        $this->assertFalse(
            $mm->isMetadataHashValid($this->baseHash, "portal"),
            "Portal metadata hash should have been invalid, but was valid"
        );
    }
}

class ValidMetadataHashMetadataManager extends MetaDataManager
{

    function setHashCacheForTest($hashes)
    {
        foreach ($hashes as $key => $hash) {
            $this->addToHashCache($key, $hash);
        }
    }

}
