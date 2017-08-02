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

import ModuleMenuCmp from '../components/module-menu-cmp';
import {seedbed, whenStepsHelper} from '@sugarcrm/seedbed';

const whenSteps = function () {

    /**
     * Select module in modules menu
     *
     * If "itemName" is visible, it means that it can be located in main menu.
     * If not - trying to open modules dropdown menu and find this module there
     *
     * @example "I choose Accounts in modules menu"
     */
    this.When(/^I choose (\w+) in modules menu$/,
        async(itemName) => {

            let moduleMenuCmp = new ModuleMenuCmp({});

            let isVisible = await moduleMenuCmp.isVisible(itemName);

            if (isVisible) {
                await moduleMenuCmp.clickItem(itemName);

            } else {

                await moduleMenuCmp.showAllModules();
                await moduleMenuCmp.clickItem(itemName, true);
            }

        }, true);

    /**
     * Select item from cached View
     */
    this.When(/^I select (\*[A-Z](?:\w|\S)*) in (#\S+)$/,
        (record, view) => {
            let listItem = view.getListItem({id: record.id});
            return listItem.clickListItem();
        }, true);

    /**
     * Open the preview for the record
     *
     * @example I click on preview button on *Account_A in #AccountsList.ListView
     */
    this.When(/^I click on preview button on (\*[A-Z](?:\w|\S)*) in (#\S+)$/,
        async (record, view) => {
            let listItem = view.getListItem({id: record.id});
            await listItem.clickPreviewButton();
        }, true);


    this.When(/^I wait for (\d+) seconds$/,
            (delay: string): Promise<void> =>
                whenStepsHelper.waitStep(parseInt(delay, 10)));

    this.When(/^I open ([\w,\/]+) view and login$/,
            (module: string): Promise<void> =>
                whenStepsHelper.setUrlHashAndLogin(module), true);

    this.When(/^I go to "([^"]*)" url$/,
            async(urlHash): Promise<void> => seedbed.client.setUrlHash(urlHash), true);


};

module.exports = whenSteps;
