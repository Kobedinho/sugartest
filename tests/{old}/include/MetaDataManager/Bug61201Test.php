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


class Bug61201Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //Turn off caching now() or else date_modified checks are invalid
        TimeDate::getInstance()->allow_cache = false;

        SugarTestHelper::setUp('current_user');
    }
    
    public function tearDown()
    {
        SugarTestReflection::setProtectedValue('MetaDataManager', 'isQueued', false);
        SugarTestReflection::setProtectedValue('MetaDataManager', 'inProcess', false);
        $queue = SugarTestReflection::getProtectedValue('MetaDataManager', 'queue');
        if ($queue) {
            $queue->clear();
        }

        TimeDate::getInstance()->allow_cache = true;
        SugarTestHelper::tearDown();
    }

    /**
     * Tests that the queue flag was set, that the queue prevents an immediate 
     * refresh and that a run queue actually fires a refresh of the cache
     * 
     * @group Bug61201
     */
    public function testMetaDataCacheQueueHandling()
    {
        $db = DBManagerFactory::getInstance();

        // Get the private metadata manager for base
        $mm = MetaDataManager::getManager();
        
        // Get the metadata now to force a cache build if it isn't there
        $mm->getMetadata();
        
        // Assert that there is a private base metadata file
        $dateModified =  $db->fromConvert($db->getOne("SELECT date_modified FROM metadata_cache WHERE type='meta_hash_base'"), 'datetime');
        $this->assertNotEmpty($dateModified);
        
        // Set the queue
        MetaDataManager::enableCacheRefreshQueue();
        
        // Test the state of the queued flag
        $state = SugarTestReflection::getProtectedValue('MetaDataManager', 'isQueued');
        $this->assertTrue($state, "MetaDataManager cache queue state was not properly set");
        
        // Try to refresh a section while queueing is on
        MetaDataManager::refreshSectionCache(MetaDataManager::MM_SERVERINFO);

        // Get the metadata again and ensure it is the same
        $mm->getMetadata();
        $newDateModified =  $db->fromConvert($db->getOne("SELECT date_modified FROM metadata_cache WHERE type='meta_hash_base'"), 'datetime');
        $this->assertEquals($dateModified, $newDateModified, "Meta Data cache has changed and it should not have");
        
        // Force a time diff
        sleep(1);
        
        // Run the queue. This should fire the refresh jobs
        MetaDataManager::runCacheRefreshQueue();
        
        // Get the metadata again and ensure it is different now
        $mm->getMetadata();

        $newDateModified = $db->fromConvert($db->getOne("SELECT date_modified FROM metadata_cache WHERE type='meta_hash_base'"), 'datetime');
        
        // Test the file first
        $this->assertNotEmpty($newDateModified, "Private cache metadata was not found after refresh.");
        
        // Test the time on the new file
        $this->assertGreaterThan(
            TimeDate::getInstance()->fromDb($dateModified)->getTimestamp(),
            TimeDate::getInstance()->fromDb($newDateModified)->getTimestamp(),
            "Second cache file make time is not greater than the first."
        );
    }
}
