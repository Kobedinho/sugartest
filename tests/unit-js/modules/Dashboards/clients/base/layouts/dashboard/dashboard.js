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

describe('Dashboards.Base.Layout.Dashboard', function() {

    var app;
    var layout;
    var apiStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'layout', 'default');
        apiStub = sinon.collection.stub(app.api, 'records', function(method, module, data, params, callbacks, options) {
            callbacks.success();
            callbacks.complete();
        });
        app.routing.start();
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        sinon.collection.restore();
        layout.dispose();
        layout.context = null;
        layout = null;
        app.routing.stop();
    });

    describe('Home Dashboard', function() {

        var sandbox = sinon.sandbox.create();

        beforeEach(function() {
            layout = SugarTest.createLayout('base', 'Home', 'dashboard');
            sinon.collection.stub(layout, '_renderEmptyTemplate', $.noop);
        });

        afterEach(function() {
            sandbox.restore();
        });

        it('should navigate to bwc dashboard', function() {
            layout.collection.add(layout.context.get('model'));
            sandbox.stub(layout, 'getLastStateKey', function() {
                return 'Home:last-visit:Home.';
            });
            sandbox.stub(app.user.lastState, 'get', function() {
                return '#bwc/index.php?module=Home&action=bwc_dashboard';
            });
            var navSpy = sandbox.stub(app.router, 'navigate', function() {
            });

            layout.setDefaultDashboard();
            expect(navSpy).toHaveBeenCalledWith('#bwc/index.php?module=Home&action=bwc_dashboard', {trigger: true});
        });

        afterEach(function() {
            sandbox.restore();
        });

        it('should initialize dashboard model and collection', function() {
            var model = layout.context.get('model');
            expect(model.apiModule).toBe('Dashboards');
            layout.loadData();
            var expectedApiUrl = 'Dashboards';
            expect(apiStub).toHaveBeenCalledWith('read', expectedApiUrl);
            apiStub.reset();

            model.set('foo', 'Blah');
            expectedApiUrl = 'Dashboards';
            model.save();
            expect(apiStub).toHaveBeenCalledWith('create', expectedApiUrl, {view_name: '', foo: 'Blah'});
            apiStub.reset();

            model.set('id', 'fake-id-value');
            expectedApiUrl = 'Dashboards';
            model.save();
            expect(apiStub).toHaveBeenCalledWith('update', expectedApiUrl);
        });
    });

    describe('Module Dashboard', function() {
        var context;
        var parentLayout;
        var parentModule;
        var sandbox = sinon.sandbox.create();

        beforeEach(function() {
            parentModule = 'Tasks';
            context = app.context.getContext({
                module: parentModule,
                layout: 'records'
            });
            parentLayout = app.view.createLayout({
                name: 'records',
                type: 'records',
                module: 'Accounts',
                context: context
            });
            layout = SugarTest.createLayout('base', 'Home', 'dashboard', null, parentLayout.context.getChildContext({
                module: 'Home'
            }));
            sinon.collection.stub(layout, '_renderEmptyTemplate', $.noop);
            parentLayout.addComponent(layout);
        });

        afterEach(function() {
            sandbox.restore();
        });

        it('should initialize dashboard model and collection', function() {
            var model = layout.context.get('model');
            var expectedApiUrl;

            expect(model.apiModule).toBe('Dashboards');
            expect(model.dashboardModule).toBe(parentModule);
            sinon.collection.stub(layout.context.parent, 'isDataFetched').returns(true);
            layout.loadData();

            expectedApiUrl = 'Dashboards/' + parentModule;
            expect(apiStub).toHaveBeenCalledWith('read', expectedApiUrl);
            apiStub.reset();

            model.set('foo', 'Blah');
            expectedApiUrl = 'Dashboards/' + parentModule;
            model.save();
            expect(apiStub).toHaveBeenCalledWith('create', expectedApiUrl, {view_name: 'records', foo: 'Blah'});
            apiStub.reset();

            model.set('id', 'fake-id-value');
            expectedApiUrl = 'Dashboards';
            model.save();
            expect(apiStub).toHaveBeenCalledWith('update', expectedApiUrl);
        });

        it('should navigate RHS panel without replacing document URL', function() {
            sinon.collection.stub(layout.context.parent, 'isDataFetched').returns(true);
            layout.navigateLayout('new-fake-id-value');
            var expectedApiUrl = 'Dashboards';
            expect(apiStub).toHaveBeenCalledWith('read', expectedApiUrl, {
                view_name: 'records',
                id: 'new-fake-id-value'
            });
        });

        afterEach(function() {
            context.clear();
            parentLayout.dispose();
            parentLayout = null;
            parentModule = null;
        });
    });

    describe('Custom Error Handlers', function() {
        var redirectStub;
        var fragmentStub;

        beforeEach(function() {
            layout = SugarTest.createLayout('base', 'Home', 'dashboard');
            redirectStub = sinon.collection.stub(app.router, 'redirect');
            fragmentStub = sinon.collection.stub(Backbone.history, 'getFragment');
        });

        using('different routes', [
            {
                route: 'Home/test',
                redirectCalled: true
            },
            {
                route: 'test',
                redirectCalled: false
            }
        ], function(value) {
            it('should redirect depending on the route when handleNotFoundError is invoked', function() {
                fragmentStub.returns(value.route);
                layout.error.handleNotFoundError();
                expect(redirectStub.called).toBe(value.redirectCalled);
            });
        });

        it('should return false when handleValidationError is invoked', function() {
            var result = layout.error.handleValidationError();
            expect(result).toBe(false);
        });
    });

    describe('navigateLayout', function() {
        var _componentDef;
        var parentModule;
        var parentLayout;
        var context;

        beforeEach(function() {

            parentModule = 'Tasks';
            context = app.context.getContext({
                module: parentModule,
                layout: 'records'
            });
            parentLayout = app.view.createLayout({
                name: 'records',
                type: 'records',
                module: 'Accounts',
                context: context
            });
            layout = SugarTest.createLayout('base', 'Home', 'dashboard', null,
                parentLayout.context.getChildContext({
                    module: 'Home'
                })
            );
            sinon.collection.stub(layout, 'dispose');
            parentLayout.addComponent(layout);
            sinon.collection.stub(layout.layout, 'render');
            sinon.collection.stub(layout.layout, '_addComponentsFromDef', function(def) {
                _componentDef = def;
            });
        });

        afterEach(function() {
            parentLayout.dispose();
        });

        it('will set type to dashboard when undefined', function() {
            layout.navigateLayout('hello-world');
            expect(_componentDef[0].layout.components[0].view).toEqual('dashboard-headerpane');
        });

    });

    describe('initComponents', function() {
        beforeEach(function() {
            layout = SugarTest.createLayout('base', 'Home', 'dashboard');
            sinon.collection.spy(layout.model, 'trigger');
            sinon.collection.stub(layout, 'isSearchContext').returns(true);
            sinon.collection.stub(layout, '_super');
        });

        it('should trigger "change:metadata" on the model if we are in the search results page', function() {
            layout.initComponents('hello-world');

            expect(layout.model.trigger).toHaveBeenCalledWith('change:metadata');
        });
    });

    describe('loadData in search results page', function() {
        beforeEach(function() {
            layout = SugarTest.createLayout('base', 'Home', 'dashboard');
            sinon.collection.spy(layout.model, 'trigger');
            sinon.collection.stub(layout, 'isSearchContext').returns(true);
            sinon.collection.stub(layout, '_getInitialDashboardMetadata');
            sinon.collection.stub(layout, 'navigateLayout');
        });

        it('should return if the model already has the metadata property', function() {
            layout.model.set('metadata', '');
            layout.loadData();
            expect(layout.navigateLayout).not.toHaveBeenCalled();
        });

        it('should set "skipFetch" and "currentDashboardIndex" in the context, and call "navigateLayout"', function() {
            layout.loadData();
            expect(layout.navigateLayout).toHaveBeenCalled();
            expect(layout.context.get('skipFetch')).toBe(true);
        });
    });
});
