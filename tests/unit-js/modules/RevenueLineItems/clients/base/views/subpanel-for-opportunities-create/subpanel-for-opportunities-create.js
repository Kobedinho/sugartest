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
describe('RevenueLineItems.Base.View.SubpanelForOpportunitiesCreate', function() {
    var app,
        view,
        layout,
        parentLayout,
        sandbox;
    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();

        var context = app.context.getContext();
        context.set({
            model: new Backbone.Model(),
            collection: new Backbone.Collection()
        });
        context.parent = new Backbone.Model();

        layout = SugarTest.createLayout("base", null, "subpanels", null, null);
        parentLayout = SugarTest.createLayout("base", null, "list", null, null);
        layout.layout = parentLayout;

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.seedMetadata();

        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'subpanel-list');
        SugarTest.loadComponent('base', 'view', 'subpanel-list-create');

        if (!_.isFunction(app.utils.generateUUID)) {
            app.utils.generateUUID = function() {}
        }
        sinon.sandbox.stub(app.utils, 'generateUUID', function() {
            return 'testUUID'
        });

        sinon.sandbox.stub(app.metadata, 'getCurrency', function() {
            return {
                currency_id: '-99',
                conversion_rate: '1.0'
            }
        });

        sinon.sandbox.stub(app.user, 'getPreference', function() {
            return '-99';
        });

        sinon.sandbox.stub(app.currency, 'getBaseCurrencyId', function() {
            return '-98';
        });

        sinon.sandbox.stub(app.metadata, 'getModule', function() {
            return {
                is_setup: 1
            }
        });

        sinon.sandbox.stub(app.lang, 'getAppListStrings', function() {
            return {
                Prospecting: 10
            }
        });

        view = SugarTest.createView('base', 'RevenueLineItems', 'subpanel-for-opportunities-create', {}, context, true, layout, true);
    });

    afterEach(function() {
        sinon.sandbox.restore();
        view.dispose();
        view = null;
    });

    describe('_addCustomFieldsToBean()', function() {
        var result;
        beforeEach(function() {
            view.model.set({
                sales_stage: 'Prospecting'
            });

            view._addBeanToList(true);
            result = view.collection.models[0];
        });

        afterEach(function() {
            result = null;
        });

        describe('should populate bean with default fields', function() {
            it('should have commit_stage', function() {
                expect(result.has('commit_stage')).toBeTruthy();
                expect(result.get('commit_stage')).toBe('exclude');
            });

            it('should have currency_id', function() {
                expect(result.has('currency_id')).toBeTruthy();
                expect(result.get('currency_id')).toBe('-99');
            });

            it('should have quantity', function() {
                expect(result.has('quantity')).toBeTruthy();
                expect(result.get('quantity')).toBe(1);
            });

            it('should have probability', function() {
                expect(result.has('probability')).toBeTruthy();
                expect(result.get('probability')).toBe(10);
            });

            it('should have currency_id', function() {
                expect(result.has('currency_id')).toBeTruthy();
                expect(result.get('currency_id')).toBe('-99');
            });

            it('should have base_rate', function() {
                expect(result.has('base_rate')).toBeTruthy();
                expect(result.get('base_rate')).toBe('1.0');
            });
        });

        describe("should use base defaults if no user prefs exist", function() {
            var result;
            beforeEach(function() {
                view.model.set({
                    sales_stage: 'Prospecting'
                });
                view.collection.reset();
            });

            afterEach(function() {
                result = null;
            });

            it('should have use base currency if no user preferred currency exists', function() {
                app.user.getPreference.restore();
                sinon.sandbox.stub(app.user, 'getPreference', function() {
                    return undefined;
                });
                view._addBeanToList(true);
                result = view.collection.models[0];
                expect(result.get('currency_id')).toBe('-98');
            });
        });
    });
})
