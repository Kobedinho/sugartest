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


class SugarTestRestUtilities
{
    private function __construct() {}
    /**
     * Get the RestServiceMock
     * @param User $user            A User to put in the rest service
     *
     * @return RestService SugarTestRestService
     */
    public static function getRestServiceMock(User $user = null)
    {
        $mock = new SugarTestRestServiceMock();
        $mock->user = ($user == null) ? $GLOBALS['current_user'] : $user;

        // Api helpers must be reset after a new service was created.
        ApiHelper::$moduleHelpers = array();

        return $mock;
    }
}

class SugarTestRestServiceMock extends RestService
{
    public function execute() {}
    protected function handleException(Exception $exception) {}
}
