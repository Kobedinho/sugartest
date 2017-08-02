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

import {BaseView} from '@sugarcrm/seedbed';
import LoginView from '../views/login-view';

/**
 * Represents Login page layout.
 *
 * @class LoginLayout
 * @extends BaseView
 */
export default class LoginLayout extends BaseView {

    public type = 'login';
    public LoginView: LoginView;
    public defaultView: LoginView;

    constructor(options) {

        super(options);

        this.defaultView = this.LoginView = this.createComponent(LoginView, {module: 'Login', default: true});

        this.selectors = this.mergeSelectors({
            $: '#sugarcrm .thumbnail.login'
        });

    }
}
