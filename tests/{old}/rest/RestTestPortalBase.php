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

require_once('tests/{old}/rest/RestTestBase.php');

class RestTestPortalBase extends RestTestBase {

    protected $currentPortalBean = null;
    protected $testConsumer = null;
    protected $originalSetting = array();

    public function setUp()
    {
        // Setup the original settings
        if (empty($GLOBALS['system_config']->settings)) {
            $GLOBALS['system_config']->retrieveSettings();
        }
        
        if (isset($GLOBALS['system_config']->settings['supportPortal_RegCreatedBy'])) {
            $this->originalSetting['portaluserid'] = $GLOBALS['system_config']->settings['supportPortal_RegCreatedBy'];
        }
        
        if (isset($GLOBALS['system_config']->settings['portal_on'])) {
            $this->originalSetting['portalon'] = $GLOBALS['system_config']->settings['portal_on'];
        }
        
        parent::setUp();

        // Make the current user a portal only user
        $this->_user->portal_only = '1';
        $this->_user->save();
        
        // Reset the support portal user id to the newly created user id        
        $GLOBALS ['system_config']->saveSetting('supportPortal', 'RegCreatedBy', $this->_user->id);
        
        $this->role = $this->_getPortalACLRole();
        if (!($this->_user->check_role_membership($this->role->name))) {
            $this->_user->load_relationship('aclroles');
            $this->_user->aclroles->add($this->role);
            $this->_user->save();
        }


        // A little bit destructive, but necessary.
        $GLOBALS['db']->query("DELETE FROM contacts WHERE portal_name = 'unittestportal'");

        // Create the portal contact
        $this->contact = BeanFactory::newBean('Contacts');
        // Make the contact id unique-ish for test runs
        $this->contact->id = "UNIT-TEST-" . create_guid_section(10);
        $this->contact->new_with_id = true;
        $this->contact->first_name = "Little";
        $this->contact->last_name = "Unittest";
        $this->contact->description = "Little Unittest";
        $this->contact->portal_name = "unittestportal";
        $this->contact->portal_active = '1';
        $this->contact->portal_password = User::getPasswordHash("unittest");
        $this->contact->assigned_user_id = $this->_user->id;
        $this->contact->save();

        $this->portalGuy = $this->contact;

        // Adding it to the contacts array makes sure it gets deleted when done
        $this->contacts[] = $this->contact;

        // Add the support_portal oauth key
        $this->testConsumer = BeanFactory::newBean('OAuthKeys');

        // use consumer to find bean with client_type === support portal
        $this->currentPortalBean = BeanFactory::newBean('OAuthKeys');
        $this->currentPortalBean->getByKey('support_portal', 'oauth2');
        $this->currentPortalBean->new_with_id = true;

        $GLOBALS['db']->query("DELETE FROM ".$this->testConsumer->table_name." WHERE client_type = 'support_portal'");

        // Create a unit test login ID
        $this->testConsumer->id = 'UNIT-TEST-portallogin';
        $this->testConsumer->new_with_id = true;
        $this->testConsumer->c_key = 'support_portal';
        $this->testConsumer->c_secret = '';
        $this->testConsumer->oauth_type = 'oauth2';
        $this->testConsumer->client_type = 'support_portal';
        $this->testConsumer->save();

        $GLOBALS['db']->commit();
    }
    public function tearDown()
    {
        global $db;
        // Re-enable the old portal users
        if ( isset($this->oldPortal) ) {
            $portalIds = "('".implode("','",$this->oldPortal)."')";
            $db->query("UPDATE users SET deleted = '0' WHERE id IN {$portalIds}");
        }


        // Delete test support_portal user
        $db->query("DELETE FROM ".$this->testConsumer->table_name." WHERE client_type = 'support_portal'");
        
        $this->_cleanUpRecords();

        // Add back original support_portal user
        if(!empty($this->currentPortalBean->id)) {
            $this->currentPortalBean->save();
        }
        
        // reset the config table back to what it was originally, default if nothing was there
        $portalUserId = isset($this->originalSetting['portaluserid']) ? $this->originalSetting['portaluserid'] : '';
        $portalOn = empty($this->originalSetting['portalon']) ? '0' : '1';
        $GLOBALS['system_config']->saveSetting('supportPortal', 'RegCreatedBy', $portalUserId);
        $GLOBALS['system_config']->saveSetting('portal', 'on', $portalOn);
        $GLOBALS['db']->commit();
        parent::tearDown();
    }


    protected function _restLogin($username = '', $password = '', $platform = 'base')
    {
        $args = array(
            'grant_type' => 'password',
            'username' => 'unittestportal',
            'password' => 'unittest',
            'client_id' => 'support_portal',
            'client_secret' => '',
            'platform' => 'portal',
        );

        // Prevent an infinite loop, put a fake authtoken in here.
        $this->authToken = 'LOGGING_IN';

        $reply = $this->_restCall('oauth2/token',json_encode($args));
        if ( empty($reply['reply']['access_token']) ) {
            throw new Exception("Rest authentication failed, message looked like: ".$reply['replyRaw']);
        }
        $this->authToken = $reply['reply']['access_token'];
        $this->refreshToken = $reply['reply']['refresh_token'];
    }

    protected function _restLogout()
    {

        if (!empty($this->authToken) && !empty($this->refreshToken)) {
            $args = array(
                'token' => $this->authToken,
            );

            $reply = $this->_restCall('oauth2/logout',json_encode($args));
            if ( !isset($reply['reply']['success']) ) {
                throw new Exception("Rest logout failed, message looked like: ".$reply['replyRaw']);
            }
        }
    }

    // Copied from parser.portalconfig.php, when that gets merged we should probably just abuse that function.
    protected function _getPortalACLRole()
    {
        $allowedModules = array('Accounts','Bugs', 'Cases', 'Notes', 'Contacts');
        $allowedActions = array('edit', 'admin', 'access', 'list', 'view');
        $role = new ACLRole();
        $role->retrieve_by_string_fields(array('name' => 'Customer Self-Service Portal Role'));
        $role->name = "Customer Self-Service Portal Role";
        $role->description = "Customer Self-Service Portal Role";
        $role->save();
        $GLOBALS['db']->commit();
        $roleActions = $role->getRoleActions($role->id);
        foreach ($roleActions as $moduleName => $actions) {
            // enable allowed moduels
            if (isset($actions['module']['access']['id']) && !in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_DISABLED);
            } elseif (isset($actions['module']['access']['id']) && in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_ENABLED);
            } else {
                foreach ($actions as $action => $actionName) {
                    if (isset($actions[$action]['access']['id'])) {
                        $role->setAction($role->id, $actions[$action]['access']['id'], ACL_ALLOW_DISABLED);
                    }
                }
            }

            if (in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_ENABLED);
                $role->setAction($role->id, $actions['module']['admin']['id'], ACL_ALLOW_ALL);
                foreach ($actions['module'] as $actionName => $action) {
                    if (in_array($actionName, $allowedActions)) {
                        $aclAllow = ACL_ALLOW_ALL;
                    } else {
                        $aclAllow = ACL_ALLOW_NONE;
                    }
                    if ($moduleName == 'Contacts') {
                        if ($actionName == 'edit' ) {
                            $aclAllow = ACL_ALLOW_OWNER;
                        }
                    }
                    if ($moduleName == 'Accounts' && $actionName == 'edit') {
                        $aclAllow = ACL_ALLOW_NONE;
                    }
                    $role->setAction($role->id, $action['id'], $aclAllow);
                }
            }

        }
        return $role;
    }

}
