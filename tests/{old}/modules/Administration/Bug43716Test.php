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



/**
 * @brief Try to compare dates in different formats
 * @ticket 43716
 */
class Bug43716Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $backup = array();
    private $user = null;
    private $upgradeHistory = null;
    private $moreResetPatch = null;
    private $patchToCheck = null;
    private $moreResetPatchFile = '';
    private $patchToCheckFile = '';
    private $filenamePostfix = '-restore';


    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }


    /**
     * @brief create user, two patches with different dates
     * @return void
     */
    public function setUp()
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->upgradeHistory = new UpgradeHistory();
        if (isset($GLOBALS['current_user']))
        {
            $this->backup['current_user'] = $GLOBALS['current_user'];
        }
        $GLOBALS['current_user'] = $this->user;

        $this->moreResetPatch=new stdClass();
        $this->moreResetPatch->id = 'moreResetPatch-id';
        $this->moreResetPatch->id_name = 'moreResetPatch-id_name';
        $this->moreResetPatch->name = 'moreResetPatch-name';
        $this->moreResetPatch->timestamp = mktime(0, 0, 0, 1, 1, 2006);
        $this->moreResetPatch->filename = 'moreResetPatch-filename';
        $i=0;
        do
        {
            $this->moreResetPatchFile = $this->moreResetPatch->filename.'.'.$i;
            ++$i;
        }
        while (is_file($this->moreResetPatchFile.$this->filenamePostfix));
        $f = fopen($this->moreResetPatchFile.$this->filenamePostfix, 'w+');
        fclose($f);
        $this->moreResetPatch->filename = $this->moreResetPatchFile.'.html';

        $this->patchToCheck=new stdClass();
        $this->patchToCheck->id = 'patchToCheck-id';
        $this->patchToCheck->id_name = 'patchToCheck-id_name';
        $this->patchToCheck->name = 'patchToCheck-name';
        $this->patchToCheck->filename = 'patchToCheck-filename';
        $this->patchToCheck->timestamp = mktime(23, 59, 59, 12, 25, 2004);
        $i=0;
        do
        {
            $this->patchToCheckFile = $this->patchToCheck->filename.'.'.$i;
            ++$i;
        }
        while (is_file($this->patchToCheckFile.$this->filenamePostfix));
        $f = fopen($this->patchToCheckFile.$this->filenamePostfix, 'w+');
        fclose($f);
        $this->patchToCheck->filename = $this->patchToCheckFile.'.html';
    }
    /**
     * @brief formats of date and time for testing
     * @return array
     */
    public function getUninstallAvailable()
    {
        $dateFormats = array_keys($GLOBALS['sugar_config']['date_formats']);
        $timeFormats = array_keys($GLOBALS['sugar_config']['time_formats']);
        $return = array();
        foreach ($dateFormats as $dateFormat)
        {
            foreach ($timeFormats as $timeFormat) {
                $return[] = array($dateFormat, $timeFormat);
            }
        }
        return $return;
    }

    /**
     * @brief creation of two dates in local format and try to compare they through UninstallAvailable
     * @dataProvider getUninstallAvailable
     * @group 43716
     *
     * @param string $dateFormat date format
     * @param string $timeFormat time format
     * @return void
     */
	public function testUninstallAvailable($dateFormat, $timeFormat)
	{
        $this->markTestIncomplete('Needs fix from FRM team.');
        $this->moreResetPatch->date_entered = date($dateFormat.' '.$timeFormat, $this->moreResetPatch->timestamp);
        $this->patchToCheck->date_entered = date($dateFormat.' '.$timeFormat, $this->patchToCheck->timestamp);
        $this->user->setPreference('datef', $dateFormat);
        $this->user->setPreference('timef', $timeFormat);

        $this->assertFalse(
            $this->upgradeHistory->UninstallAvailable(array($this->moreResetPatch), $this->patchToCheck),
            'UninstallAvailable should return false'
        );
	}

    /**
     * @brief remove user and patches information
     * @return void
     */
    public function tearDown()
    {
        unlink($this->moreResetPatchFile.$this->filenamePostfix);
        unlink($this->patchToCheckFile.$this->filenamePostfix);
        unset($this->upgradeHistory);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->user);
        unset($GLOBALS['current_user']);
        foreach ($this->backup as $k => $v)
        {
            global $$k;
            $$k = $v;
            $GLOBALS[$k] = $$k;
        }
    }
}
