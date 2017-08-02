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

class SugarMinifyUtilsTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * The file that is built by this process
     * 
     * @var string
     */
    protected $builtFile = 'include/javascript/unit_test_built.min.js';

    /** {@inheritDoc} */
    protected function setUp()
    {
        parent::setUp();

        SugarTestHelper::saveFile(sugar_cached($this->builtFile));
    }

    public function testConcatenateFiles()
    {
        global $sugar_config;
        $sugar_config['minify_resources'] = true;
        $sugar_config['developerMode'] = false;

        /** @var SugarMinifyUtils|PHPUnit_Framework_MockObject_MockObject $minifier */
        $minifier = $this->getMockBuilder('SugarMinifyUtils')
            ->setMethods(array('getJSGroupings'))
            ->getMock();
        $minifier->expects($this->any())
            ->method('getJSGroupings')
            ->willReturn(array(
                array(
                    'jssource/minify/test/var.js' => $this->builtFile,
                    'jssource/minify/test/if.js' => $this->builtFile,
                ),
            ));

        $minifier->ConcatenateFiles('tests/{old}');

        // Test the file was created
        $this->assertFileExists(sugar_cached($this->builtFile));
        
        // Test the contents of the file. Using contains instead of equals so
        // systems without JSMin won't fail hard
        $content = file_get_contents(sugar_cached($this->builtFile));
        $expect1 = file_get_contents('tests/{old}/jssource/minify/expect/var.js');
        $expect2 = file_get_contents('tests/{old}/jssource/minify/expect/if.js');
        $this->assertContains($expect1, $content);
        $this->assertContains($expect2, $content);
    }
}
