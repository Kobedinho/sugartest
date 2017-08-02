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
// turn of warnings like "Confusing use of '!'., W018"
/* jshint -W018 */

const whenStepsHeader = function () {

    /**
     * Click header panel buttons
     *
     * @example "I click Save button on #AccountsDrawer header"
     */
    this.When(/^I click (Create|Edit|Cancel|Save) button on (#[A-Z](?:\w|\S)*) header$/,
         (btnName, layout) => {
            return layout.HeaderView.clickButton(btnName.toLowerCase());
        }, true);

    /**
     * Open header panel actions menu
     *
     * @example "I open actions menu in #Account_ARecord"
     */
    this.When(/^I open actions menu in (#[A-Z]\w+)$/,
        layout => {
            return layout.HeaderView.clickButton('actions');
        }, true);

    /**
     * Choose Actions menu options
     *
     * @example "I choose Delete from actions menu in #Account_ARecord"
     */
    this.When(/^I choose (Copy|Delete) from actions menu in (#[A-Z]\w+)\s*$/,
        (action, layout) => {
            return layout.HeaderView.clickButton(action);
        }, true);
};

module.exports = whenStepsHeader;
