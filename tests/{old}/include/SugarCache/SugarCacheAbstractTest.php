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

/**
 * @covers SugarCacheAbstract
 */
abstract class SugarCacheAbstractTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarCacheAbstract
     */
    protected static $cache;

    protected function setUp()
    {
        parent::setUp();

        if (!$this->getInstance()->useBackend()) {
            $this->markTestSkipped('Backend unavailable');
        }
    }

    public function testGetSet()
    {
        $reader = $this->getInstance();
        $writer = $this->getInstance();

        $this->assertNull($reader->get('key1'));
        $this->assertNull($reader->get('key2'));
        $this->assertNull($reader->get('key3'));

        $writer->set('key1', array('key' => 'value'));
        $writer->set('key2', 'Hello, World!');
        $writer->set('key3', 1024);

        $this->assertSame(array('key' => 'value'), $reader->get('key1'));
        $this->assertSame('Hello, World!', $reader->get('key2'));

        // some extensions (I'm pointing at you, memcache) are too lazy to serialize integers
        $this->assertEquals(1024, $reader->get('key3'));
    }

    public function testUnset()
    {
        $reader = $this->getInstance();
        $writer = $this->getInstance();

        $writer->set('key1', 'X');
        $writer->set('key2', 'Y');

        unset($writer->key1);

        $this->assertNull($reader->get('key1'));
        $this->assertNotNull($reader->get('key2'));
    }

    public function testReset()
    {
        $reader = $this->getInstance();
        $writer = $this->getInstance();

        $writer->set('key', 'value');
        $writer->flush();
        $this->assertNull($reader->get('key'));
    }

    public function testExpiration()
    {
        $reader = $this->getInstance();
        $writer = $this->getInstance();

        $writer->set('key', 'value', 1);
        usleep(1200000);

        $this->assertNull($reader->get('key'));
    }

    /**
     * @return SugarCacheAbstract
     */
    abstract protected function newInstance();

    /**
     * @return SugarCacheAbstract
     */
    protected function getInstance()
    {
        $instance = $this->newInstance();
        $instance->useLocalStore = false;

        return $instance;
    }
}
