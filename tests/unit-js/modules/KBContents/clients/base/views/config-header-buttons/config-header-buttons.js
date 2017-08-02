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
describe('modules.KBContents.clients.base.view.ConfigHeaderButtons', function() {
    var app;
    var view;
    var module;
    var context;

    beforeEach(function() {
        app = SugarTest.app;
        module = 'KBContents';

        SugarTest.loadComponent('base', 'view', 'config-header-buttons');
        view = SugarTest.createView('base', module, 'config-header-buttons', null, null, true);

        context = app.context.getContext({
            module: module
        });
        context.set('model', app.data.createBean(module));

        sinon.collection.stub(view, 'triggerBefore').returns(true);
        sinon.collection.stub(view, 'getField').withArgs('save_button').returns({
            setDisabled: $.noop
        });

        sinon.collection.stub(app, 'sync');
        sinon.collection.stub(app.accessibility, 'run', function() {});
        sinon.collection.stub(app.alert, 'show', function() {
            return {
                getCloseSelector: function() {
                    return {
                        on: function() {}
                    };
                }
            };
        });
        sinon.collection.stub(app.api, 'call', function(method, url, data, callbacks) {
            callbacks.success({});
        });
    });

    afterEach(function() {
        sinon.collection.restore();
        view = null;
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    describe('saveConfig()', function() {

        it('will sync metadata', function() {
            app.drawer = {
                close: $.noop,
                count: $.noop
            };
            sinon.collection.stub(app.drawer, 'count').returns(1);

            var model = view.context.get('model');
            var doValidateStub = sinon.collection.stub(model, 'doValidate');

            // emulating passed validation
            doValidateStub.callsArgWith(1, true);

            view.saveConfig();
            expect(app.sync).toHaveBeenCalledOnce();
        });

        it('will validate model', function() {
            app.drawer = {
                close: $.noop,
                count: $.noop
            };
            sinon.collection.stub(app.drawer, 'count').returns(1);

            var model = view.context.get('model');
            sinon.collection.stub(model, 'doValidate');

            view.saveConfig();
            expect(model.doValidate).toHaveBeenCalledOnce();
        });
    });
});

