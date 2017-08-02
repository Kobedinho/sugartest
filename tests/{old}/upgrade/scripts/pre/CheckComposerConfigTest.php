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
require_once 'upgrade/scripts/pre/CheckComposerConfig.php';

/**
 *
 * CheckComposerConfig pre script test suite
 *
 */
class SugarUpgradeCheckComposerConfigTest extends UpgradeTestCase
{
    /**
     * @var string Default context source_dir
     */
    protected $sourceDir;

    /**
     * @var string Default context new_source_dir
     */
    protected $newSourceDir;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        // Disable logging
        unset($this->upgrader->context['log']);

        // Default context
        $this->sourceDir = $this->upgrader->context['source_dir'] = sugar_cached('composerupgrade/src');
        $this->newSourceDir = $this->upgrader->context['new_source_dir'] = sugar_cached('composerupgrade/newsrc');
    }

    /**
     * @group unit
     * @covers SugarUpgradeCheckComposerConfig::initialize
     */
    public function testInitialize()
    {
        $sut = $this->getMockSut();

        $sut->upgrader->context = array();
        $this->assertFalse(SugarTestReflection::callProtectedMethod($sut, 'initialize'));

        $sut->upgrader->context['source_dir'] = 'src';
        $sut->upgrader->context['new_source_dir'] = 'new';
        $this->assertTrue(SugarTestReflection::callProtectedMethod($sut, 'initialize'));
        $this->assertNotEmpty(SugarTestReflection::getProtectedValue($sut, 'jsonFile'));
        $this->assertNotEmpty(SugarTestReflection::getProtectedValue($sut, 'lockFile'));
        $this->assertNotEmpty(SugarTestReflection::getProtectedValue($sut, 'newJsonFile'));
    }

    /**
     * @group unit
     * @covers SugarUpgradeCheckComposerConfig::validateGenericSettings
     *
     * @dataProvider dataProviderTestValidateGenericSettings
     * @param array $target Target definition
     * @param array $config Composer configuration
     * @param boolean $expected Valid or not
     */
    public function testValidateGenericSettings(array $target, array $config, $expected)
    {
        $result = SugarTestReflection::callProtectedMethod(
            $this->getMockSut(),
            'validateGenericSettings',
            array($target, $config)
        );

        $this->assertEquals($expected, $result);
    }

    public function dataProviderTestValidateGenericSettings()
    {
        return array(
            array(
                array(
                    'generic' => array()
                ),
                array(),
                true,
            ),
            array(
                array(
                    'generic' => array(
                        'name' => 'foo/bar',
                        'description' => 'beer',
                        'config' => array(
                            'sweet' => 'sugar',
                        ),
                    )
                ),
                array(
                    'name' => 'foo/bar',
                    'description' => 'beer',
                    'config' => array(
                        'sweet' => 'sugar',
                    ),
                ),
                true,
            ),
            array(
                array(
                    'generic' => array(
                        'name' => 'foo/bar',
                        'description' => 'beer',
                    )
                ),
                array(),
                false,
            ),
            array(
                array(
                    'generic' => array(
                        'name' => 'foo/bar',
                        'description' => 'beer',
                    )
                ),
                array(
                    'name' => 'foo/bar',
                    'description' => 'coke',
                ),
                false,
            ),
        );
    }

    /**
     * @group unit
     * @covers SugarUpgradeCheckComposerConfig::createProposal
     *
     * @dataProvider dataProviderTestCreateProposal
     * @param array $config Current composer config
     * @param array $generic Generic config settings
     * @param array $pack Missing packages
     * @param array $expected
     */
    public function testCreateProposal(array $config, array $generic, array $pack, array $expected)
    {
        $sut = $this->getMockSut(array('saveToFile'));
        SugarTestReflection::callProtectedMethod($sut, 'initialize');

        $expectedFile = sprintf(
            "%s/%s.proposal",
            $this->newSourceDir,
            SugarUpgradeCheckComposerConfig::COMPOSER_JSON
        );

        $sut->expects($this->once())
            ->method('saveToFile')
            ->with($this->equalTo($expectedFile), $this->equalTo($expected));

        SugarTestReflection::callProtectedMethod(
            $sut,
            'createProposal',
            array($config, $generic, $pack)
        );
    }

    public function dataProviderTestCreateProposal()
    {
        return array(
            // Test generic settings override
            array(
                array(
                    'name' => 'foo',
                    'config' => 'bar',
                ),
                array(
                    'name' => 'new',
                ),
                array(),
                array(
                    'name' => 'new',
                    'config' => 'bar',
                ),
            ),
            // Test missing module
            array(
                array(
                    'name' => 'foo',
                    'config' => 'bar',
                ),
                array(),
                array(
                    'sugarcrm/modulex' => '1.2.3',
                    'sugarcrm/moduley' => 'v1.0',
                ),
                array(
                    'name' => 'foo',
                    'config' => 'bar',
                    'require' => array(
                        'sugarcrm/modulex' => '1.2.3',
                        'sugarcrm/moduley' => 'v1.0',
                    ),
                ),
            ),
            // Test mix
            array(
                array(
                    'name' => 'foo',
                    'config' => 'bar',
                    'require' => array(
                        'existing/lib' => '4.5.6',
                    ),
                ),
                array(
                    'name' => 'new',
                    'config' => array(
                        'config1' => true,
                        'config2' => false,
                        'config3' => 'ok',
                    ),
                ),
                array(
                    'sugarcrm/modulex' => '1.2.3',
                    'sugarcrm/moduley' => 'v1.0',
                ),
                array(
                    'name' => 'new',
                    'config' => array(
                        'config1' => true,
                        'config2' => false,
                        'config3' => 'ok',
                    ),
                    'require' => array(
                        'existing/lib' => '4.5.6',
                        'sugarcrm/modulex' => '1.2.3',
                        'sugarcrm/moduley' => 'v1.0',
                    ),
                ),
            ),
        );
    }

    /**
     * @group unit
     * @covers SugarUpgradeCheckComposerConfig::useCustomComposerFiles
     */
    public function testUseCustomComposerFiles()
    {
        $sut = $this->getMockSut(array('copy'));
        $this->assertArrayNotHasKey('composer_custom', ($sut->upgrader->state));

        $files = array('composer.json', 'composer.lock');
        foreach ($files as $index => $file) {
            $sut->expects($this->at($index))
                ->method('copy')
                ->with($this->equalTo($file), $this->equalTo($file . '.valid'))
                ->will($this->returnValue(true));
        }

        SugarTestReflection::callProtectedMethod($sut, 'useCustomComposerFiles', array($files));

        $this->assertArrayHasKey('composer_custom', ($sut->upgrader->state));
        $this->assertSame($files, $sut->upgrader->state['composer_custom']);
    }

    /**
     * @group unit
     * @covers SugarUpgradeCheckComposerConfig::isPlatformPackage
     *
     * @dataProvider dataProviderTestIsPlatformPackage
     * @param array $tests List of tests and assertions
     */
    public function testIsPlatformPackage(array $tests)
    {
        $sut = $this->getMockSut();

        foreach ($tests as $test => $expected) {
            $this->assertSame(
                $expected,
                SugarTestReflection::callProtectedMethod($sut, 'isPlatformPackage', array($test))
            );
        }
    }

    public function dataProviderTestIsPlatformPackage()
    {
        return array(
            array(
                array(
                    'php' => true,
                    'ext-apc' => true,
                    'lib-gd' => true,
                    'sugarcrm/sugarcrm' => false,
                    'monolog/monolog' => false,
                ),
            ),
        );
    }

    /**
     * @group unit
     * @covers SugarUpgradeCheckComposerConfig::isPackageAvailable
     */
    public function testIsPackageAvailable()
    {
        $sut = $this->getMockSut();
        $lock = array('sugarcrm/sugarcrm' => '7.6.0.1');

        $this->assertTrue(SugarTestReflection::callProtectedMethod(
            $sut,
            'isPackageAvailable',
            array('sugarcrm/sugarcrm', '7.6.0.1', $lock)
        ));

        $this->assertTrue(SugarTestReflection::callProtectedMethod(
            $sut,
            'isPackageAvailable',
            array('php', '5.5.0', $lock)
        ));


        $this->assertFalse(SugarTestReflection::callProtectedMethod(
            $sut,
            'isPackageAvailable',
            array('sugarcrm/sugarcrm', '7.6.0.2', $lock)
        ));

        $this->assertFalse(SugarTestReflection::callProtectedMethod(
            $sut,
            'isPackageAvailable',
            array('foo/bar', '7.6.0.1', $lock)
        ));
    }

    /**
     * @group unit
     * @covers SugarUpgradeCheckComposerConfig::getMissingPackages
     */
    public function testGetMissingPackages()
    {
        $target = array(
            'foo' => 'bar'
        );

        $sut = $this->getMockSut(array('isPackageAvailable'));
        $sut->expects($this->exactly(count($target)))
        ->method('isPackageAvailable');

        SugarTestReflection::callProtectedMethod($sut, 'getMissingPackages', array($target, array()));
    }

    /**
     * Get mock for subject under test
     * @param null|array $method
     * @param array $context Additional context settings
     * @return SugarUpgradeCheckComposerConfig|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockSut($method = null, array $context = array())
    {
        foreach ($context as $k => $v) {
            $this->upgrader->context[$k] = $v;
        }

        return $this->getMockBuilder('SugarUpgradeCheckComposerConfig')
            ->setConstructorArgs(array($this->upgrader))
            ->setMethods($method)
            ->getMock();
    }

    public function testIsStockComposer()
    {
        $isStockComposer = $this->isStockComposer('the-hash', 'the-hash', 'the-hash');
        $this->assertTrue($isStockComposer);
    }

    /**
     * @dataProvider isNotStockComposerProvider
     */
    public function testIsNotStockComposer($lockHash, $actualHash, $stockHash)
    {
        $isStockComposer = $this->isStockComposer($lockHash, $actualHash, $stockHash);
        $this->assertFalse($isStockComposer);
    }

    public static function isNotStockComposerProvider()
    {
        return array(
            'empty-lock-hash' => array(null, null, null),
            'empty-actual-hash' => array('X', null, null),
            'actual-lock-mismatch' => array('X', null, 'Y'),
            'empty-stock-hash' => array('X', null, 'X'),
            'actual-stock-mismatch' => array('X', 'Y', 'X'),
        );
    }

    private function isStockComposer($lockHash, $stockHash, $actualHash)
    {
        $sut = $this->getMockSut(array('loadLock', 'getActualHash', 'getStockHash'));
        SugarTestReflection::setProtectedValue($sut, 'lockHash', $lockHash);
        $sut->expects($this->any())
            ->method('getActualHash')
            ->willReturn($actualHash);
        $sut->expects($this->any())
            ->method('getStockHash')
            ->willReturn($stockHash);
        return SugarTestReflection::callProtectedMethod($sut, 'isStockComposer');
    }
}
