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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Indexer;

use Sugarcrm\Sugarcrm\Elasticsearch\Indexer\BulkHandler;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Document;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Indexer\BulkHandler
 */
class BulkHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Trace log messages
     * @var array
     */
    public $logMessages = array();

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->logMessages = array();
    }

    /**
     * @covers ::batchDocuments
     * @covers ::batchDocument
     * @covers ::getBatchedDocuments
     * @covers ::removeIndexFromDocument
     * @covers ::setMaxBulkThreshold
     */
    public function testBatchDocuments()
    {
        $bulk = $this->getBulkMock(array('__destruct', 'sendBulk'));
        $bulk->setMaxBulkThreshold(3);

        // 4 document batch (within threshold of 3 per index)
        $doc1 = new Document('11', array('name' => 'doc1'), 'Module1', 'index1');
        $doc2 = new Document('12', array('name' => 'doc2'), 'Module1', 'index1');
        $doc3 = new Document('23', array('name' => 'doc3'), 'Module2', 'index2');
        $doc4 = new Document('24', array('name' => 'doc4'), 'Module2', 'index2');
        $docs = array($doc1, $doc2, $doc3, $doc4);

        // ensure index is set
        foreach ($docs as $doc) {
            $this->assertTrue($doc->hasParam('_index'));
        }

        // expect 2 indices in batch, verify docs
        $bulk->batchDocuments($docs);
        $this->assertCount(2, $bulk->getBatchedDocuments());

        $this->assertSame(array(
            'index1' => array($doc1, $doc2),
            'index2' => array($doc3, $doc4),
        ), $bulk->getBatchedDocuments());

        // indices are expected to be stripped from the docs
        foreach ($docs as $doc) {
            $this->assertFalse($doc->hasParam('_index'));
        }

        // testing trigger of sendBulk
        $bulk->expects($this->exactly(2))
            ->method('sendBulk');

        // 3th document should trigger a send to index1
        $doc5 = new Document('15', array('name' => 'doc5'), 'Module1', 'index1');
        $bulk->batchDocument($doc5);
        $batched = $bulk->getBatchedDocuments();
        $this->assertCount(0, $batched['index1']);

        // 3th document should trigger a send to index2
        $doc6 = new Document('26', array('name' => 'doc6'), 'Module2', 'index2');
        $bulk->batchDocument($doc6);
        $batched = $bulk->getBatchedDocuments();
        $this->assertCount(0, $batched['index2']);
    }

    /**
     * @covers ::finishBatch
     */
    public function testFinishBatch()
    {
        $bulk = $this->getBulkMock(array('__destruct', 'sendBulk'));
        $bulk->setMaxBulkThreshold(10);

        $docs = array(
            new Document('11', array('name' => 'doc1'), 'Module1', 'index1'),
            new Document('12', array('name' => 'doc2'), 'Module1', 'index1'),
            new Document('23', array('name' => 'doc3'), 'Module2', 'index2'),
            new Document('24', array('name' => 'doc4'), 'Module2', 'index2'),
        );

        $bulk->batchDocuments($docs);

        // expect 2 batches, one for every index
        $bulk->expects($this->exactly(2))
            ->method('sendBulk');

        $bulk->finishBatch();

        $batched = $bulk->getBatchedDocuments();
        $this->assertCount(0, $batched['index1']);
        $this->assertCount(0, $batched['index2']);
    }

    /**
     * @covers ::sendBulk
     */
    public function testSendBulk()
    {
        $bulk = $this->getBulkMock(array('__destruct', 'newBulkObject'));

        // set threshold to amount of docs we are testing with
        $bulk->setMaxBulkThreshold(2);

        // index document
        $doc1 = new Document('11', array('name' => 'doc1'), 'Module1', 'index1');
        $doc1->setOpType(\Elastica\Bulk\Action::OP_TYPE_INDEX);

        // delete document
        $doc2 = new Document('12', array(), 'Module1', 'index1');
        $doc2->setOpType(\Elastica\Bulk\Action::OP_TYPE_DELETE);

        $docs = array($doc1, $doc2);

        // mock Elastica bulk object
        $elasticaBulk = $this->getElasticaBulkMock(array('send'));
        $bulk->expects($this->once())
            ->method('newBulkObject')
            ->will($this->returnValue($elasticaBulk));

        // batch our documents, this will invoke sendBulk
        $bulk->batchDocuments($docs);

        // both documents end up in same index
        $this->assertSame('index1', $elasticaBulk->getIndex());

        // verify documents are properly added
        $this->assertCount(2, $elasticaBulk->getActions());
        foreach ($elasticaBulk->getActions() as $i => $action) {
            $this->assertSame($docs[$i], $action->getDocument());
        }

        // batch queue should be empty
        $batched = $bulk->getBatchedDocuments();
        $this->assertCount(0, $batched['index1']);
    }

    /**
     * Test error handling using raw example failure requests
     * @covers ::sendBulk
     * @covers ::handleBulkException
     * @dataProvider providerTestHandleBulkException
     */
    public function testHandleBulkException($docCount, $responseString, $status, $expectedLog)
    {
        $bulk = $this->getBulkMock(array('__destruct', 'newBulkObject', 'log'));
        $bulk->setMaxBulkThreshold($docCount);

        // mock Elastica bulk/client
        $elasticaBulk = $this->getElasticaBulkClientMock($responseString, $status);

        $bulk->expects($this->once())
            ->method('newBulkObject')
            ->will($this->returnValue($elasticaBulk));

        $that = $this;
        $bulk->expects($this->exactly(count($expectedLog)))
            ->method('log')
            ->will($this->returnCallback(function ($level, $message) use ($that) {
                $that->logMessages[] = $message;
            }));

        // build documents to send
        $documents = array();
        for ($i = 1; $i <= $docCount; $i++) {
            $documents[] = new Document($i, array('name' => 'foo'), 'Accounts', 'foobar');
        }

        $bulk->batchDocuments($documents);
        $this->assertSame($expectedLog, $this->logMessages);
    }

    public function providerTestHandleBulkException()
    {
        return array(
            // one document, one failure, HTTP 200 return (actual log message)
            array(
                1,
                '{"took":4,"errors":true,"items":[{"index":{"_index":"foobar","_type":"Accounts","_id":"633aca08-594e-1ba3-2ec4-5727c4e231e0","status":500,"error":"IllegalArgumentException[Document contains at least one immense term in field=\"Accounts__description\" (whose UTF8 encoding is longer than the max length 32766), all of which were skipped.  Please correct the analyzer to not produce such terms, original message: bytes can be at most 32766 in length; got 40003]; nested: MaxBytesLengthExceededException[bytes can be at most 32766 in length; got 40003];"}}]}',
                200,
                array(
                    'Unrecoverable indexing failure [500]: foobar -> Accounts -> 633aca08-594e-1ba3-2ec4-5727c4e231e0 -> IllegalArgumentException[Document contains at least one immense term in field="Accounts__description" (whose UTF8 encoding is longer than the max length 32766), all of which were skipped.  Please correct the analyzer to not produce such terms, original message: bytes can be at most 32766 in length; got 40003]; nested: MaxBytesLengthExceededException[bytes can be at most 32766 in length; got 40003];',
                ),
            ),
            // 3 documents, two failures, HTTP 200 return (actual log message)
            array(
                3,
                '{"took":10,"errors":true,"items":[{"index":{"_index":"autobr4142_accountsonly","_type":"Accounts","_id":"5d3f72e7-4dee-4208-185f-571fc5d95a9d","_version":1,"status":201}},{"index":{"_index":"autobr4142_accountsonly","_type":"Accounts","_id":"633aca08-594e-1ba3-2ec4-5727c4e231e0","status":500,"error":"IllegalArgumentException[Document contains at least one immense term in field=\"Accounts__description\" (whose UTF8 encoding is longer than the max length 32766), all of which were skipped.  Please correct the analyzer to not produce such terms., original message: bytes can be at most 32766 in length; got 40003]; nested: MaxBytesLengthExceededException[bytes can be at most 32766 in length; got 40003]; "}},{"index":{"_index":"autobr4142_accountsonly","_type":"Accounts","_id":"a88802d5-909e-3bc1-c025-5727fd4cb5e8","status":500,"error":"IllegalArgumentException[Document contains at least one immense term in field=\"Accounts__description\" (whose UTF8 encoding is longer than the max length 32766), all of which were skipped.  Please correct the analyzer to not produce such terms., original message: bytes can be at most 32766 in length; got 40003]; nested: MaxBytesLengthExceededException[bytes can be at most 32766 in length; got 40003]; "}}]}',
                200,
                array(
                    'Unrecoverable indexing failure [500]: autobr4142_accountsonly -> Accounts -> 633aca08-594e-1ba3-2ec4-5727c4e231e0 -> IllegalArgumentException[Document contains at least one immense term in field="Accounts__description" (whose UTF8 encoding is longer than the max length 32766), all of which were skipped.  Please correct the analyzer to not produce such terms., original message: bytes can be at most 32766 in length; got 40003]; nested: MaxBytesLengthExceededException[bytes can be at most 32766 in length; got 40003]; ',
                    'Unrecoverable indexing failure [500]: autobr4142_accountsonly -> Accounts -> a88802d5-909e-3bc1-c025-5727fd4cb5e8 -> IllegalArgumentException[Document contains at least one immense term in field="Accounts__description" (whose UTF8 encoding is longer than the max length 32766), all of which were skipped.  Please correct the analyzer to not produce such terms., original message: bytes can be at most 32766 in length; got 40003]; nested: MaxBytesLengthExceededException[bytes can be at most 32766 in length; got 40003]; ',
                ),
            ),
        );
    }

    /**
     * @covers ::__wakeup
     */
    public function testWakeup()
    {
        $bulk = $this->getBulkMock(array('__destruct'));

        // add one document
        $bulk->batchDocument(new Document('x', array(), 'y', 'z'));
        $this->assertCount(1, $bulk->getBatchedDocuments());

        // ensure document count is empty on unserialize
        $new = unserialize(serialize($bulk));
        $this->assertCount(0, $new->getBatchedDocuments());
    }

    /**
     * Elastica bulk mock
     * @param array $methods
     * @return \Elastica\Bulk
     */
    protected function getElasticaBulkMock(array $methods = null)
    {
        return $this->getMockBuilder('Elastica\Bulk')
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Elastica bulk mock based on mocked client raw response
     * @param string $responseString
     * @param integer $status
     * @param array $methods
     * @return \Elastica\Bulk
     */
    protected function getElasticaBulkClientMock($responseString, $status, array $methods = null)
    {
        $client = $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client')
            ->disableOriginalConstructor()
            ->setMethods(array('request'))
            ->getMock();

        $client->expects($this->any())
            ->method('request')
            ->will($this->returnValue(new \Elastica\Response($responseString, $status)));

        return $this->getMockBuilder('Elastica\Bulk')
            ->setConstructorArgs(array($client))
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Bulk handler mock
     * @param array $methods
     * @return BulkHandler
     */
    protected function getBulkMock(array $methods = null)
    {
        $container = $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Container')
            ->setMethods(null)
            ->getMock();

        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Indexer\BulkHandler')
            ->setMethods($methods)
            ->setConstructorArgs(array($container))
            ->getMock();
    }
}
