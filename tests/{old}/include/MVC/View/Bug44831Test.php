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

class Bug44831Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);

        // Create a Custom editviewdefs.php
        sugar_mkdir("custom/modules/Leads/metadata/",null,true);

        if ( is_dir("cache/modules/Leads") )
            rmdir_recursive("cache/modules/Leads");

        if (file_exists("custom/modules/Leads/metadata/editviewdefs.php")) 
            unlink("custom/modules/Leads/metadata/editviewdefs.php");

        // Create a very simple custom EditView Layout
        if( $fh = @fopen("custom/modules/Leads/metadata/editviewdefs.php", 'w+') ) 
        {
$string = <<<EOQ
<?php
\$viewdefs['Leads']['EditView'] = array('templateMeta' => array (
                                                                 'form' => array('buttons' => array ('SAVE', 'CANCEL'),
                                                                                 'hidden' => array ('<a>HiddenPlaceHolder</a>',
                                                                                                   ),
                                                                                ),
                                                                 'maxColumns' => '2', 
                                                                 'useTabs' => true,
                                                                 'widths' => array( array ('label' => '10', 'field' => '30'),
                                                                                    array ('label' => '10', 'field' => '30'),
                                                                                  ),
                                                                 'javascript' => array( array ('file' => 'custom/modules/Leads/javascript/LeadJS1.js'),
                                                                                        array ('file' => 'custom/modules/Leads/javascript/LeadJS2.js'),
                                                                                      ),
                                                                ),
                                        'panels' => array ('default' => array (0 => array (0 => array ('name' => 'first_name',
                                                                                                      ),
                                                                                           1 => array ('name' => 'last_name',
                                                                                                      ),
                                                                                          ),
                                                                               1 => array (0 => array ('name' => 'unknown_field',
                                                                                                       'customCode' => '<a href="#">Unknown Field Link</a>',
                                                                                                      ),
                                                                                          ),
                                                                              ),
                                                          ),  
                                       );
?>
EOQ;
            fputs( $fh, $string);
            fclose( $fh );
        }


    }
    
    public function tearDown()
    {
        if ( is_dir("cache/modules/Leads") )
            rmdir_recursive("cache/modules/Leads");

        if (file_exists("custom/modules/Leads/metadata/editviewdefs.php")) 
            unlink("custom/modules/Leads/metadata/editviewdefs.php");

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['current_user']);
        unset($_SERVER['REQUEST_METHOD']);
    }
    
    /**
    * @group bug44831
    */
    public function testJSInjection()
    {
    	$this->markTestIncomplete('Marked as skipped for now... too problematic');
    	return;
        require_once('include/utils/layout_utils.php');
        $_SERVER['REQUEST_METHOD'] = "POST";

        $lead = SugarTestLeadUtilities::createLead();
        $lead->name = 'LeadName';
        $lead->save();
        
        $_REQUEST['module'] = 'Leads';
        $_REQUEST['action'] = 'EditView';
        $_REQUEST['record'] = $lead->id;
        
        $GLOBALS['app']->controller = ControllerFactory::getController($_REQUEST['module']);
        //ob_start();
        $GLOBALS['app']->controller->execute();
        //$tStr = ob_get_clean();
        
        // First of all, need to be sure that I'm actually dealing with my new custom DetailView Layout
        $this->expectOutputRegex('/.*HiddenPlaceHolder.*/');
        // Then check inclusion of LeadJS1.js
        $this->expectOutputRegex('/.*<script src=\"custom\/modules\/Leads\/javascript\/LeadJS1\.js.*\"><\/script>.*/');
        // Then check inclusion of LeadJS2.js
        $this->expectOutputRegex('/.*<script src=\"custom\/modules\/Leads\/javascript\/LeadJS2\.js.*\"><\/script>.*/');
        
        //unset($GLOBALS['app']->controller);
        unset($_REQUEST['module']);
        unset($_REQUEST['action']);
        unset($_REQUEST['record']);
        SugarTestLeadUtilities::removeAllCreatedLeads();
    }
}
