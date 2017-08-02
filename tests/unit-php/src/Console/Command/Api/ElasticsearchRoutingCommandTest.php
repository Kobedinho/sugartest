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
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\Command\Api\ElasticsearchRoutingCommand
 *
 */
class ElasticsearchRoutingCommandTest extends AbstractApiCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->commandClass = 'Sugarcrm\Sugarcrm\Console\Command\Api\ElasticsearchRoutingCommand';
        $this->apiClass = 'AdministrationApi';
        $this->apiMethod = 'elasticSearchRouting';
    }

    /**
     * {@inheritdoc}
     */
    public function providerTestExecuteCommand()
    {
        return array(
            array(
                array(
                    'accounts' => array(
                        'strategy' => 'static',
                        'routing' => array(
                            'write_index' => 'accounts',
                            'read_indices' => array('accounts'),
                        ),
                    ),
                    'emails' => array(
                        'strategy' => 'archive',
                        'routing' => array(
                            'write_index' => 'emails_current',
                            'read_indices' => array(
                                'emails_2015_12',
                                'emails_2015_11',
                                'emails_2015_10',
                            ),
                        ),
                    ),
                ),
                array(),
                'ElasticsearchRoutingCommand_0.txt',
                0,
            ),
        );
    }
}
