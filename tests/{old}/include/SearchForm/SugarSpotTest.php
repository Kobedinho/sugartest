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
 

class SugarSpotTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }
    
    public function tearDown()
    {
        unset($GLOBALS['app_strings']);
    }
    
    /**
     * @ticket 41236
     */
    public function testSearchGrabsModuleDisplayName() 
    {
        $langpack = new SugarTestLangPackCreator();
        $langpack->setAppListString('moduleList',array('Foo'=>'Bar'));
        $langpack->save();
        
        $result = array(
            'Foo' => array(
                'data' => array(
                    array(
                        'ID' => '1',
                        'NAME' => 'recordname',
                        ),
                    ),
                'pageData' => array(
                    'offsets' => array(
                        'total' => 1,
                        'next' => 0,
                        ),
                    'bean' => array(
                        'moduleDir' => 'Foo',
                        ),
                    ),
                ),
                'readAccess' => true,
            );
        
        $sugarSpot = $this->createPartialMock('SugarSpot', array('_performSearch'));
        $sugarSpot->expects($this->any())
            ->method('_performSearch')
            ->will($this->returnValue($result));
            
        $returnValue = $sugarSpot->searchAndDisplay('','');

        $this->assertRegExp('/Bar/',$returnValue);
    }

    /**
     * @ticket 43080
     */
    public function testSearchGrabsMore() 
    {
        $app_strings = return_application_language($GLOBALS['current_language']); 
        $this->assertTrue(array_key_exists('LBL_SEARCH_MORE', $app_strings));

        $langpack = new SugarTestLangPackCreator();
        $langpack->setAppString('LBL_SEARCH_MORE', 'XXmoreXX');
        $langpack->save();
        
        $result = array(
            'Foo' => array(
                'data' => array(
                    array(
                        'ID' => '1',
                        'NAME' => 'recordname',
                        ),
                    ),
                'pageData' => array(
                    'offsets' => array(
                        'total' => 100,
                        'next' => 0,
                        ),
                    'bean' => array(
                        'moduleDir' => 'Foo',
                        ),
                    ),
                ),
                'readAccess' => true,
            );
        
        $sugarSpot = $this->createPartialMock('SugarSpot', array('_performSearch'));
        $sugarSpot->expects($this->any())
            ->method('_performSearch')
            ->will($this->returnValue($result));
            
        $returnValue = $sugarSpot->searchAndDisplay('','');

        $this->assertNotContains('(99 more)',$returnValue);
        $this->assertContains('(99 XXmoreXX)',$returnValue);
    }


    /**
     * providerTestSearchType
     * This is the provider function for testFilterSearchType
     *
     */
    public function providerTestSearchType()
    {
        return array(
              array('phone', '777', true),
              array('phone', '(777)', true),
              array('phone', '%777', true),
              array('phone', '77', false),
              array('phone', '%77) 7', false),
              array('phone', '88-88-88', false),
              array('int', '1', true),
              array('int', '1.0', true),
              array('int', '.1', true),
              array('int', 'a', false),
              array('decimal', '1.0', true),
              array('decimal', '1', true),
              array('decimal', '1,000', true),
              array('decimal', 'aaaaa', false),
              array('float', '1.0', true),
              array('float', '1', true),
              array('float', '1,000', true),
              array('float', 'aaaaa', false),
              array('id', '1', false),
              array('datetime', '2011-01-01 10:10:10', false),
              array('date', '2011-01-01', false),
              array('bool', true, false),
              array('bool', false, false),
              array('foo', 'foo', true),
        );
    }

    /**
     * testFilterSearchType
     * This function uses a provider to test the filter search type
     * @dataProvider providerTestSearchType
     */
    public function testFilterSearchType($type, $query, $expected)
    {
        $sugarSpot = new Bug50484SugarSpotMock();
        $this->assertEquals($expected, $sugarSpot->filterSearchType($type, $query),
            ('SugarSpot->filterSearchType expected type ' . $type . ' with value ' . $query . ' to return ' . $expected ? 'true' : false));
    }

    /**
     * @dataProvider getOptionProvider
     */
    public function testGetOption($options, $name, $module, $expected)
    {
        $sugarSpot = new Bug50484SugarSpotMock();
        $actual = $sugarSpot->getOption($options, $name, $module);
        $this->assertEquals($expected, $actual);
    }

    public static function getOptionProvider()
    {
        return array(
            'none-provided' => array(
                array(),
                'foo',
                null,
                null,
            ),
            'global-provided' => array(
                array(
                    'foo' => 'bar',
                ),
                'foo',
                null,
                'bar',
            ),
            'module-specific-provided' => array(
                array(
                    'modules' => array(
                        'Accounts' => array(
                            'foo' => 'baz',
                        ),
                    ),
                ),
                'foo',
                'Accounts',
                'baz',
            ),
            'both-provided' => array(
                array(
                    'foo' => 'bar',
                    'modules' => array(
                        'Accounts' => array(
                            'foo' => 'baz',
                        ),
                    ),
                ),
                'foo',
                'Accounts',
                'baz',
            ),
        );
    }
}


class Bug50484SugarSpotMock extends SugarSpot
{
    public function filterSearchType($type, $query)
    {
        return parent::filterSearchType($type, $query);
    }

    public function getOption(array $options, $name, $module = null)
    {
        return parent::getOption($options, $name, $module);
    }
}
