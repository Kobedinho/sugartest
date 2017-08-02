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
describe('Base.View.Helplet', function() {
    var sandbox;
    var layout;
    var view;
    var app = SUGAR.App;

    beforeEach(function() {
        sandbox = sinon.sandbox.create();
        sandbox.stub(app.help, 'get');
        SugarTest.loadComponent('base', 'view', 'helplet');
        layout = app.view.createLayout({type: 'base'});
        view = app.view.createView({
            type: 'helplet',
            layout: layout
        });
    });

    afterEach(function() {
        view.dispose();
        layout.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        sandbox.restore();
    });

    describe('toggleTourLink', function() {
        using('different values for app.tutorial.hasTutorial', [true, false], function(state) {
            it('should dictate if the tour is enabled', function() {
                sandbox.stub(app.tutorial, 'hasTutorial').returns(state);
                view.toggleTourLink();
                expect(view.isTourEnabled()).toEqual(state);
            });
        });
    });

    describe('showTour', function() {
        var stubHelpLayout;

        beforeEach(function() {
            stubHelpLayout = {toggle: $.noop};

            sandbox.stub(layout, 'closestComponent', function() {
                return stubHelpLayout;
            });

            sandbox.stub(app.controller.context, 'get', function(key) {
                switch (key) {
                    case 'layout': return 'testLayout';
                    case 'module': return 'testModule';
                }
            });

            sandbox.spy(stubHelpLayout, 'toggle');
            sandbox.stub(app.tutorial, 'resetPrefs');
            sandbox.stub(app.tutorial, 'show');
        });

        it('should only show tour if it\'s enabled', function() {
            var tourEnabled = false;
            sandbox.stub(view, 'isTourEnabled', function() {
                return tourEnabled;
            });
            view.showTour();
            sinon.assert.notCalled(stubHelpLayout.toggle);
            sinon.assert.notCalled(app.tutorial.resetPrefs);
            sinon.assert.notCalled(app.tutorial.show);

            tourEnabled = true;
            view.showTour();
            expect(stubHelpLayout.toggle).toHaveBeenCalledWith(false);
            expect(app.tutorial.resetPrefs).toHaveBeenCalled();
            expect(app.tutorial.show).toHaveBeenCalledWith('testLayout', {module: 'testModule'});
        });
    });
});
