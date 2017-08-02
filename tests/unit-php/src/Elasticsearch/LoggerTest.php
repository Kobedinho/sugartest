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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Elasticsearch\Logger;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Logger
 *
 */
class LoggerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers ::encodeData
     * @dataProvider providerEncodeData
     *
     * @param string $inputData
     * @param string $outputData
     */
    public function testEncodeData($inputData, $outputData)
    {
        $logger = $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Logger')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $encoded = TestReflection::callProtectedMethod($logger, 'encodeData', array($inputData));
        $this->assertEquals($outputData, $encoded);
    }

    public function providerEncodeData()
    {
        return array(
            array(
                "Hello world",
                "Hello world",
            ),
            array(
                array("1" => "Hello", "2" => "world"),
                '{"1":"Hello","2":"world"}',
            ),
        );
    }

    /**
     * @covers ::isDeleteMissingIndexRequest
     * @dataProvider providerIsDeleteMissingIndexRequest
     *
     * @param array $expMsg : message for the exception
     * @param string $path : path of the request
     * @param string $method : method of the request
     * @param string $output : the expected return value of the method
     */
    public function testIsDeleteMissingIndexRequest($expMsg, $path, $method, $output)
    {
        $logger = new Logger(\LoggerManager::getLogger());

        $request = new \Elastica\Request($path, $method);
        $response = new \Elastica\Response($expMsg);
        $e = new \Elastica\Exception\ResponseException($request, $response);

        $result = $logger->isDeleteMissingIndexRequest($e);
        $this->assertEquals($output, $result);
    }

    public function providerIsDeleteMissingIndexRequest()
    {
        return array(
            array(
                '{"error":"IndexMissingException[[0e787f44c65e77fc6ac2c4fac1a01c65_shared] missing]","status":400}',
                "0e787f44c65e77fc6ac2c4fac1a01c65_shared/",
                "DELETE",
                true,
            ),
            array(
                '{"error":"IndexMissingException[[0e787f44c65e77fc6ac2c4fac1a01c65] missing]","status":400}',
                "0e787f44c65e77fc6ac2c4fac1a01c65/",
                "DELETE",
                true,
            ),
            array(
                '{"error":"IndexMissingException[[0e787f44c65e77fc6ac2c4fac1a01c65_shared] missing]","status":400}',
                "0e787f44c65e77fc6ac2c4fac1a01c65_shared/",
                "GET",
                false,
            ),
            array(
                '{"error":"IndexAlreadyExistsException[[0e787f44c65e77fc6ac2c4fac1a01c65_shared]]","status":400}',
                "0e787f44c65e77fc6ac2c4fac1a01c65_shared/",
                "DELETE",
                false,
            )
        );
    }

}
