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

namespace Sugarcrm\SugarcrmTestsUnit\Console\Command\Api;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\Command\Api\ElasticsearchQueueCommand
 *
 */
class ElasticsearchQueueCommandTest extends AbstractApiCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->commandClass = 'Sugarcrm\Sugarcrm\Console\Command\Api\ElasticsearchQueueCommand';
        $this->apiClass = 'AdministrationApi';
        $this->apiMethod = 'elasticSearchQueue';
    }

    /**
     * {@inheritdoc}
     */
    public function providerTestExecuteCommand()
    {
        return array(
            array(
                array(),
                array(),
                'ElasticsearchQueueCommand_0.txt',
                0,
            ),
            array(
                array(
                    'queued' => array(
                        'accounts' => 73,
                        'contacts' => 27,
                    ),
                    'total' => 100,
                ),
                array(),
                'ElasticsearchQueueCommand_1.txt',
                1,
            ),
        );
    }
}
