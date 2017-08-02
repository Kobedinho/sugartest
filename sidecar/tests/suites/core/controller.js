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
describe("Controller", function() {

    var app;
    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.seedMetadata();
        SugarTest.seedFakeServer();
    });

    describe("whether layout.loadData gets called is controlled by caller using skipFetch", function() {
        
        var params,
            spy,
            stub;

        beforeEach(function() {
            params = {
                module: "Contacts",
                layout: "list"
            };
            // Essentially, we need to spy on whether layout.loadData is called. But the controller 
            // creates this using view mgr's createLayout; so we hijack then set back to original.
            spy = sinon.spy(app.view.Layout.prototype, "loadData");
            stub = sinon.stub(app.view.Layout.prototype, "bindDataChange");

        });
        afterEach(function() {
            spy.restore();
            stub.restore();
        });
        it("should call loadData by default", function() {
            app.controller.loadView(params);
            expect(spy).toHaveBeenCalled();
        });

        it("should NOT call loadData if skipFetch passed", function() {
            params.skipFetch = true;
            app.controller.loadView(params);
            expect(spy).not.toHaveBeenCalled();
        });

    });
    describe("when a route is matched", function() {


        it("should load the view properly", function() {
            var params = {
                module: "Contacts",
                layout: "list"
            };

            SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts.*/,
                [200, {  "Content-Type":"application/json"},
                    JSON.stringify(fixtures.api["rest/v10/contact"].GET.response)]);

            app.controller.loadView(params);
            SugarTest.server.respond();

            expect(app.controller.layout).toBeDefined();
            expect(app.controller.layout instanceof Backbone.View).toBeTruthy();
            expect(app.controller.context.get("collection")).toBeDefined();
            expect(app.controller.context.get("collection").models.length).toEqual(2);

        });
    });

    describe('when additional components', function() {
        var targetId = 'footer';
        var targetSelector = '#' + targetId;
        beforeEach(function() {
            sinon.collection.spy(app.view, 'createView');
            sinon.collection.spy(app.view, 'createLayout');
            app.controller.$el.append('<div id="' + targetId + '"></div>');
        });

        afterEach(function() {
            sinon.collection.restore();
            app.additionalComponents = {};
        });

        it('should log an error and return if the target element is not in the DOM', function() {
            sinon.collection.stub(app.logger, 'error');
            var components = {login: {target: '#header'}};

            app.controller.loadAdditionalComponents(components);
            expect(app.logger.error).toHaveBeenCalled();
        });

        it('should log an error if some of the components do not have a target', function() {
            sinon.collection.stub(app.logger, 'error');
            var components = {login: {}};

            app.controller.loadAdditionalComponents(components);
            expect(app.logger.error).toHaveBeenCalled();
            expect(app.view.createView).not.toHaveBeenCalled();
        });

        it('should log an error and return if app.additionalComponents is already defined.', function() {
            sinon.collection.stub(app.logger, 'error');
            var components = {login: {target: targetSelector, view: 'login'}};
            app.controller.loadAdditionalComponents(components);
            app.controller.loadAdditionalComponents(components);
            expect(app.logger.error).toHaveBeenCalled();
            expect(app.view.createView).not.toHaveBeenCalledTwice();
        });


        it('should create and render a view', function() {
            var components = {login: {target: targetSelector}};
            app.controller.loadAdditionalComponents(components);
            expect(app.additionalComponents.login instanceof app.view.View).toBeTruthy();
            expect(app.additionalComponents.login.name).toEqual('login');
            expect(app.view.createView).toHaveBeenCalled();
        });


        it('should create and render a layout', function() {
            var components = {header: {target: targetSelector, layout: 'header'}};
            app.controller.loadAdditionalComponents(components);
            expect(app.additionalComponents.header instanceof app.view.Layout).toBeTruthy();
            expect(app.additionalComponents.header.name).toEqual('header');
            expect(app.view.createLayout).toHaveBeenCalled();
        });

        it('should create and render a view with a name different from the add comp name', function() {
            var components = {login: {target: targetSelector, view: 'footer'}};
            app.controller.loadAdditionalComponents(components);
            expect(app.additionalComponents.login instanceof app.view.View).toBeTruthy();
            expect(app.additionalComponents.login.name).toEqual('footer');
        });

        it('should re-render them when app:sync:complete fires', function() {
            var components = {
                login: {
                    target: targetSelector
                },
                testlayout: {
                    target: targetSelector,
                    layout: 'header'
                }
            };
            app.controller.loadAdditionalComponents(components);
            var renderStub = sinon.collection.stub(app.additionalComponents.login, 'render');

            app.router = app.router || {};
            app.router.reset = app.router.reset || function() {};
            sinon.collection.stub(app.router, 'reset');

            app.events.trigger('app:sync:complete');

            expect(renderStub).toHaveBeenCalled();
        });
    });
});
