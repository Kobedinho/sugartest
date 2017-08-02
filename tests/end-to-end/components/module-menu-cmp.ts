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

import {BaseView, seedbed} from '@sugarcrm/seedbed';

/**
 * Represents Modules Top Menu.
 *
 * @class ModuleMenuCmp
 * @extends BaseView
 */
export default class extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = {
            moduleList: {
                $: '.module-list',
                item: {
                    $: 'a[href="#{{itemName}}"].module-name',
                    iconStatus: '.icon.status-{{status}}'
                },
                listItem: {
                    $: 'a.module-list-link[href="#{{itemName}}"]'
                },
                currentItem: 'li.current a[href="#{{itemName}}"]',
                moreIcon: '.more button'
            }
        };
    }

    /**
     * Get Module Item Selector
     *
     * @param itemName
     * @param dropdown
     * @returns {*|String}
     */
    public getModuleButtonSelector(itemName, dropdown?) {
        return (dropdown) ?
            this.$('moduleList.listItem', {itemName: itemName}) :
            this.$('moduleList.item', {itemName: itemName});
    }

    /**
     * Click on Module Item in Modules menu
     *
     * @param link
     * @param dropdown
     * @returns {*}
     */
    public async clickItem (link, dropdown?) {
        let itemSelector = this.getModuleButtonSelector(link, dropdown);

        return seedbed.client
            .waitForVisible(itemSelector)
            .click(itemSelector);

    }

    /**
     * Is Module Menu Item visible
     * Modules Mega menu contain visible modules and other modules are hidden in dropdown menu
     *
     * @param itemName
     */
    public async isVisible(itemName) {
        return seedbed.client.isVisible(this.getModuleButtonSelector(itemName));
    }

    /**
     * Click on Modules Mege Menu dropdown to show all modules
     */
    public async showAllModules() {
        return seedbed.client
            .click(this.$('moduleList.moreIcon'));
    }
};
