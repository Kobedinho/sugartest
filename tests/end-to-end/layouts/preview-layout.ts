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
import PreviewView from '../views/preview-view';
import PreviewHeaderView from '../views/preview-header-view';

/**
 * Represents Preview page layout.
 *
 * @class PreviewLayout
 * @extends BaseView
 */
export default class PreviewLayout extends BaseView {

    public PreviewView: PreviewView;
    public defaultView: PreviewView;
    public PreviewHeaderView: PreviewHeaderView;

    constructor(options) {

        super(options);

        this.defaultView = this.PreviewView = this.createComponent(PreviewView);
        this.PreviewHeaderView = this.createComponent(PreviewHeaderView);

        this.selectors = this.mergeSelectors({
            $: '#sugarcrm .preview-pane.active'
        });

    }
}
