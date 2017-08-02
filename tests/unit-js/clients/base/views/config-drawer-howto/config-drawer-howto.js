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
describe('Base.View.ConfigDrawerHowto', function() {
    var app,
        view;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.createView('base', null, 'config-drawer-howto')
    });

    afterEach(function() {
        sinon.collection.restore();
        view = null;
    });

    describe('bindDataChange()', function() {
        var onSpy;

        beforeEach(function() {
            onSpy = spyOn(view.context, 'on');
        });

        it('should listen for `config:howtoData:change` event', function() {
            view.bindDataChange();
            expect(onSpy.mostRecentCall.args[0]).toBe('config:howtoData:change');
        });
    });
});
