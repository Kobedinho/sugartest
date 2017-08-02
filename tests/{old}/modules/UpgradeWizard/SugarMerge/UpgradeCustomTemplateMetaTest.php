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
require_once 'include/dir_inc.php';

class UpgradeCustomTemplateMetaTest extends Sugar_PHPUnit_Framework_TestCase
{

    var $merge;

    function setUp()
    {
        SugarTestMergeUtilities::setupFiles(array('Calls', 'Meetings', 'Notes'), array('editviewdefs'), 'tests/{old}/modules/UpgradeWizard/SugarMerge/metadata_files');
    }


    function tearDown()
    {
        SugarTestMergeUtilities::teardownFiles();
    }

    /**
     * @group SugarMerge
     */
    function testMergeCallsEditviewdefsFor611()
    {
        $this->merge = new EditViewMerge();
        $this->merge->merge('Calls', 'tests/{old}/modules/UpgradeWizard/SugarMerge/metadata_files/611/modules/Calls/metadata/editviewdefs.php', 'modules/Calls/metadata/editviewdefs.php', 'custom/modules/Calls/metadata/editviewdefs.php');

        //Load file
        require('custom/modules/Calls/metadata/editviewdefs.php');

        $this->assertNotContains('forms[0]', $viewdefs['Calls']['EditView']['templateMeta']['form']['buttons'][0]['customCode'], "forms[0] did not get replaced");
    }

    /**
     * @group SugarMerge
     */
    function testMergeMeetingsEditviewdefsFor611()
    {
        $this->merge = new EditViewMerge();
        $this->merge->merge('Meetings', 'tests/{old}/modules/UpgradeWizard/SugarMerge/metadata_files/611/modules/Meetings/metadata/editviewdefs.php', 'modules/Meetings/metadata/editviewdefs.php', 'custom/modules/Meetings/metadata/editviewdefs.php');

        //Load file
        require('custom/modules/Meetings/metadata/editviewdefs.php');

        $this->assertNotContains('this.form.', $viewdefs['Meetings']['EditView']['templateMeta']['form']['buttons'][0]['customCode'], "this.form did not get replaced");
    }


    /**
     * Custom button definitions should not be kept during upgrade
     * @group SugarMerge
     */
    function testMergeCustomButtonsAndStudioChanges()
    {
        $this->merge = new EditViewMerge();
        $this->merge->merge('Notes', 'tests/{old}/modules/UpgradeWizard/SugarMerge/metadata_files/610/modules/Notes/metadata/editviewdefs.php', 'modules/Notes/metadata/editviewdefs.php', 'custom/modules/Notes/metadata/editviewdefs.php');

        //Load file
        require('custom/modules/Notes/metadata/editviewdefs.php');

        //Assert that custom Buttons are not kept
        $this->assertArrayNotHasKey('buttons', $viewdefs['Notes']['EditView']['templateMeta']['form'], "Buttons array picked up from custom file");

        //Assert that studio possible changes are retained
        $this->assertArrayHasKey('useTabs', $viewdefs['Notes']['EditView']['templateMeta']);
        $this->assertArrayHasKey('tabDefs', $viewdefs['Notes']['EditView']['templateMeta']);
        $this->assertArrayHasKey('syncDetailEditViews', $viewdefs['Notes']['EditView']['templateMeta']);

    }

}