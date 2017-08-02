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
/*
 Alert.
 */

import {BaseView, seedbed} from '@sugarcrm/seedbed';

/**
 * @class AlertCmp
 * @extends BaseView
 */
export default class AlertCmp extends BaseView {

    public alertType: boolean;
    public method: string;

    constructor(options) {
        super(options);

        this.selectors = {
            container: '#alerts .alert-{{alertType}}',
            closeIcon: '.icon-remove',
            message: '.message',
            buttons: {
                'confirm': 'a.alert-btn-confirm',
                'cancel': 'a.alert-btn-cancel'
            }
        };

        this.alertType = options.type; /*
         {load | success | ... }
         each CUD alert should have method name {create | update | delete}
         */
        this.method = options.method;
    }

    /**
     * Close Alert
     */
    public async close() {
        return seedbed.client.waitForVisibleAndClick(this.$('closeIcon'));
    }

    public async clickButton(selectorName) {
        return seedbed.client.click(this.$(`buttons.${selectorName.toLowerCase()}`));
    }
};
