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

class Bug44503Test extends Sugar_PHPUnit_Framework_TestCase
{
	protected $authclassname = null;

	public function setUp()
    {
    	$this->authclassname = 'TestAuthClass'.mt_rand();

    	sugar_mkdir("custom/modules/Users/authentication/{$this->authclassname}/",null,true);

        sugar_file_put_contents(
            "custom/modules/Users/authentication/{$this->authclassname}/{$this->authclassname}.php",
            "<?php
class {$this->authclassname} extends SugarAuthenticate {
    public \$userAuthenticateClass = '{$this->authclassname}User';
    public \$authenticationDir = '{$this->authclassname}';
}"
            );
        sugar_file_put_contents(
            "custom/modules/Users/authentication/{$this->authclassname}/{$this->authclassname}User.php",
            "<?php
class {$this->authclassname}User extends SugarAuthenticateUser {
}"
            );
        SugarAutoLoader::addToMap("custom/modules/Users/authentication/{$this->authclassname}/{$this->authclassname}.php", false);
        SugarAutoLoader::addToMap("custom/modules/Users/authentication/{$this->authclassname}/{$this->authclassname}User.php", false);
	}

	public function tearDown()
	{
	    if ( !is_null($this->authclassname) && is_dir("custom/modules/Users/authentication/{$this->authclassname}/") ) {
	        rmdir_recursive("custom/modules/Users/authentication/{$this->authclassname}/");
	        SugarAutoLoader::delFromMap("custom/modules/Users/authentication/{$this->authclassname}", false);
	    }
	}

	public function testLoadingCustomAuthClassFromAuthenicationController()
	{
	    $authController = new AuthenticationController($this->authclassname);

	    $this->assertInstanceOf($this->authclassname,$authController->authController);
	    $this->assertInstanceOf($this->authclassname.'User',$authController->authController->userAuthenticate);
	}
}
