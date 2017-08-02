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

namespace Sugarcrm\SugarcrmTestsUnit\Console;

use Sugarcrm\Sugarcrm\Console\Application;
use Sugarcrm\Sugarcrm\Console\CommandRegistry\CommandRegistry;
use Sugarcrm\SugarcrmTestsUnit\Console\Fixtures\ApplicationTestCommandA;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Console\Application
 *
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::create
     * @dataProvider providerTestAvailableStockCommands
     */
    public function testAvailableStockCommands($mode, array $expected)
    {
        $app = Application::create($mode);
        $commands = $app->all();

        $this->assertEquals(
            count($expected),
            count($commands),
            'Amount of stock commands does not match expected count'
        );

        foreach ($expected as $name => $class) {
            $this->assertArrayHasKey($name, $commands);
            $this->assertInstanceOf($class, $commands[$name]);
        }
    }

    public function providerTestAvailableStockCommands()
    {
        $ns = 'Sugarcrm\Sugarcrm\Console\Command';
        return array(
            array(
                CommandRegistry::MODE_STANDALONE,
                array(
                    'help' => 'Symfony\Component\Console\Command\HelpCommand',
                    'list' => 'Symfony\Component\Console\Command\ListCommand',
                ),
            ),
            array(
                CommandRegistry::MODE_INSTANCE,
                array(
                    'help' => 'Symfony\Component\Console\Command\HelpCommand',
                    'list' => 'Symfony\Component\Console\Command\ListCommand',
                    'elastic:indices' => $ns . '\Api\ElasticsearchIndicesCommand',
                    'elastic:queue' => $ns . '\Api\ElasticsearchQueueCommand',
                    'elastic:queue_cleanup' => $ns . '\Elasticsearch\CleanupQueueCommand',
                    'elastic:routing' => $ns . '\Api\ElasticsearchRoutingCommand',
                    'elastic:explain' => $ns . '\Elasticsearch\ExplainCommand',
                    'search:fields' => $ns . '\Api\SearchFieldsCommand',
                    'search:reindex' => $ns . '\Api\SearchReindexCommand',
                    'search:status' => $ns . '\Api\SearchStatusCommand',
                    'search:module' => $ns . '\Elasticsearch\ModuleCommand',
                    'password:config' => $ns . '\Password\PasswordConfigCommand',
                    'password:reset' => $ns . '\Password\PasswordResetCommand',
                    'password:weak' => $ns . '\Password\WeakHashesCommand',
                ),
            ),
        );
    }

    /**
     * @covers ::getDefaultInputDefinition
     */
    public function testProfileInputDefinition()
    {
        $app = new Application(new CommandRegistry(), true);
        $this->assertTrue($app->getDefinition()->hasOption('profile'));
    }

    /**
     * @covers ::__construct
     * @covers ::doRun
     */
    public function testDoRun()
    {
        $app = new Application();
        $app->add(new ApplicationTestCommandA());
        $app->setAutoExit(false);

        $this->assertSame('SugarCRM Console', $app->getName());

        $tester = new ApplicationTester($app);

        // regular execution
        $tester->run(array('command' => 'apptest:A'));
        $this->assertEquals(
            'Success Application Test A' . PHP_EOL,
            $tester->getDisplay()
        );

        // execution with profiling
        $tester->run(array('command' => 'apptest:A', '--profile' => true));
        $this->assertRegExp(
            '/^Success Application Test A\n\nMemory usage: (.*) MB \(peak: (.*) MB\), time: (.*)s\n$/',
            $tester->getDisplay()
        );
    }

    /**
     * @covers ::getSugarVersion
     */
    public function testGetSugarVersion()
    {
        // make a backup of the current file
        $sugarVersionFile = SUGAR_BASE_DIR . '/sugar_version.php';
        $backupFile = SUGAR_BASE_DIR . '/sugar_version.tests.unit-php';
        copy($sugarVersionFile, $backupFile);

        // version from source tree
        $this->setupSugarVersionFixture('sugar_version_source');
        $app = new Application();
        $this->assertEquals(
            '[standalone mode]',
            $app->getVersion(),
            'Expecting standalone mode for source base sugar_version'
        );

        // version from installed sugar
        $this->setupSugarVersionFixture('sugar_version_installed');
        $app = new Application();
        $this->assertEquals(
            '7.7.0.0-ULT-1234',
            $app->getVersion(),
            'Expecting actual version number from built/installed system'
        );

        // corrupt sugar_version file
        $this->setupSugarVersionFixture('sugar_version_corrupt');
        $app = new Application();
        $this->assertEquals(
            '[standalone mode]',
            $app->getVersion(),
            'Expecting standalone mode for corrupt sugar_version'
        );

        // missing sugar version file (shouldnt happen, but got it covered)
        unlink($sugarVersionFile);
        $app = new Application();
        $this->assertEquals(
            '[standalone mode]',
            $app->getVersion(),
            'Expecting standalone mode for missing sugar_version'
        );

        // restore original version file
        copy($backupFile, $sugarVersionFile);
        unlink($backupFile);
    }

    /**
     * Setup sugar_verion.php fixture
     * @param string $file
     */
    protected function setupSugarVersionFixture($file)
    {
        $file = __DIR__ . '/Fixtures/' . $file . '.txt';
        copy($file, SUGAR_BASE_DIR . '/sugar_version.php');
    }

    /**
     * @covers ::setMode
     * @covers ::getMode
     */
    public function testSetGetMode()
    {
        $app = new Application();
        $this->assertSame('', $app->getMode());
        $app->setMode('yeaha');
        $this->assertSame('yeaha', $app->getMode());
    }
}
