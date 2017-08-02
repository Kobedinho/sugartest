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

require_once('tests/{old}/rest/RestTestPortalBase.php');

class RestMetadataBWCTest extends RestTestPortalBase {

    /**
     * @group rest
     */
    public function testDefaultPortalLayoutMetaData() {
        $restReply = $this->_restCall('metadata?type_filter=modules');

        // bwc if set should always be false
        foreach($restReply['reply']['modules'] as $modName => $modMeta){
            if (isset($modMeta['isBwcEnabled'])) {
                $this->assertFalse($modMeta['isBwcEnabled'], "A portal module is bwc enabled.");
            }
        }
    }


}
