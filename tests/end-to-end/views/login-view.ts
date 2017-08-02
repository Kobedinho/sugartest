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
Represents Login page PageObject.
 */

import BaseView from './base-view';
import {seedbed} from '@sugarcrm/seedbed';

/**
 * @class LoginView
 * @extends BaseView
 */
export default class LoginView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({

            'username': 'input[name=username]',
                'password': 'input[name=password]',
                buttons: {
                'login': 'a[name=login_button]'
            }
        });

    }

    public async login(username, password) {

        await seedbed.client
            .setValue(this.$('username'), username)
            .setValue(this.$('password'), password);

        return seedbed.client.click(this.$('buttons.login'));
    }
}
