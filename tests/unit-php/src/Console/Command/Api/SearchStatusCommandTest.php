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
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\Command\Api\SearchStatusCommand
 *
 */
class SearchStatusCommandTest extends AbstractApiCommandTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->commandClass = 'Sugarcrm\Sugarcrm\Console\Command\Api\SearchStatusCommand';
        $this->apiClass = 'AdministrationApi';
        $this->apiMethod = 'searchStatus';
    }

    /**
     * {@inheritdoc}
     */
    public function providerTestExecuteCommand()
    {
        return array(
            array(
                array(
                    'available' => true,
                    'enabled_modules' => array('Accounts', 'Contacts'),
                ),
                array(),
                'SearchStatusCommand_0.txt',
                0,
            ),
            array(
                array(
                    'available' => false,
                    'enabled_modules' => array('Accounts'),
                ),
                array(),
                'SearchStatusCommand_1.txt',
                1,
            ),
        );
    }
}
