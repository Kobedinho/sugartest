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
Represents header view PageObject
 */
import BaseView from './base-view';
import {seedbed} from '@sugarcrm/seedbed';
/**
 * @class HeaderView
 * @extends BaseView
 */
export default class HeaderView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.headerpane',
            buttons: {
                'create': 'a[name="create_button"]',
                'cancel': 'a[name="cancel_button"]',
                'save': 'a[name="save_button"]',
                'edit': 'a[name="edit_button"]',
                'delete': 'a[name="delete_button"]',
                'actions': '.actions:not([style*="display: none"]) a.btn.dropdown-toggle'
            },

            title: {
                'old' : 'h1 [data-name="title"] span.list-headerpane',
                    'new' : 'h1 [data-name="title"] span.list-headerpane div'
            }
        });
    }

    public async clickButton(selectorName) {
        return seedbed.client.click(this.$(`buttons.${selectorName.toLowerCase()}`));
    }
}
