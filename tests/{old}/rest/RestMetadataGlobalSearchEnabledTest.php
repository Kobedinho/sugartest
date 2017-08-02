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
require_once 'tests/{old}/rest/RestTestBase.php';

class RestMetadataGlobalSearchEnabledTest extends RestTestBase
{
    /**
     * Tests the getGlobalSearchEnabled method in the MetadataManager
     * @dataProvider moduleVardefDataProvider
     */
    public function testGlobalSearchEnabled($platform, $seed, $vardefs, $expects, $failMessage)
    {
        $mm = MetaDataManager::getManager(array($platform));
        $actual = $mm->getGlobalSearchEnabled($seed, $vardefs, $platform);
        $this->assertEquals($expects, $actual, $failMessage);
    }

    // Please see `failMessage` property to see what each run is testing for
    public function moduleVardefDataProvider()
    {
        return array(
            array(
                'platform' => 'base',
                'seed' => true,
                'vardefs' => array(),
                'expects' => true,
                'failMessage' => "When globalSearchEnabled not provided, should check if \$seed is Bean; if so should return true"
            ),
            array(
                'platform' => 'base',
                'seed' => false,
                'vardefs' => array(),
                'expects' => false,
                'failMessage' => "When globalSearchEnabled not provided, should check if \$seed is Bean; if NOT should return false"
            ),
            array(
                'platform' => 'base',
                'seed' => true,
                'vardefs' => array(
                    'globalSearchEnabled' => true,
                ),
                'expects' => true,
                'failMessage' => "When globalSearchEnabled used as 'global boolean', that value should be returned (truthy)"
            ),
            array(
                'platform' => 'base',
                'seed' => true,
                'vardefs' => array(
                    'globalSearchEnabled' => false,
                ),
                'expects' => false,
                'failMessage' => "When globalSearchEnabled used as 'global boolean', that value should be returned (falsy)"
            ),
            array(
                'platform' => 'portal',
                'seed' => true,
                'vardefs' => array(
                    'globalSearchEnabled' => array(
                        'portal' => true,
                        'base' => false
                    )
                ),
                'expects' => true,
                'failMessage' => "When globalSearchEnabled used as array with platform, should use value for current platform if exists (truthy)"
            ),
            array(
                'platform' => 'portal',
                'seed' => true,
                'vardefs' => array(
                    'globalSearchEnabled' => array(
                        'portal' => false,
                        'base' => true,
                    )
                ),
                'expects' => false,
                'failMessage' => "When globalSearchEnabled used as array with platform, should use value for current platform if exists (falsy) (even if another platform is truthy)"
            ),
            array(
                'platform' => 'portal',
                'seed' => true,
                'vardefs' => array(
                    'globalSearchEnabled' => array(
                        'base' => true,
                    )
                ),
                'expects' => true,
                'failMessage' => "When globalSearchEnabled used as array with platform and the platform is not set in the meta, it should check to see if base is set; if so, it should return THAT value (truthy check)"
            ),
            array(
                'platform' => 'portal',
                'seed' => true,
                'vardefs' => array(
                    'globalSearchEnabled' => array(
                        'base' => false,
                    )
                ),
                'expects' => false,
                'failMessage' => "When globalSearchEnabled used as array with platform and the platform is not set in the meta, it should check to see if base is set; if so, it should return THAT value (false check)"
            ),
            array(
                'platform' => 'portal',
                'seed' => true,
                'vardefs' => array(
                    'globalSearchEnabled' => array(
                        'notportal1' => false,
                        'notportal2' => false,
                    )
                ),
                'expects' => true,
                'failMessage' => "When globalSearchEnabled used as array but current platform not found should fallback to true ignoring other platform settings"
            ),
        );
    }
}
