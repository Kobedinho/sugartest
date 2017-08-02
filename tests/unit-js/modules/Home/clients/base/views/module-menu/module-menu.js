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
describe('Home Menu', function() {
    var moduleName = 'Home',
        viewName = 'module-menu',
        app,
        view;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', null, moduleName);
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'recently-viewed', moduleName);
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'view', viewName, moduleName);
        SugarTest.testMetadata.set();

        view = SugarTest.createView('base', moduleName, 'module-menu', null, null);
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
        $('body').empty();
    });

    it('should populate recently viewed on menu open', function() {
        var fetchStub = sinon.collection.stub(view.recentlyViewed, 'fetch', function(options) {
            options.success.call(this, {
                next_offset: -1,
                models: []
            });
        });

        // ignore dashboards fetch
        sinon.collection.stub(view.dashboards, 'fetch');

        view.$el.trigger('shown.bs.dropdown');

        expect(fetchStub.calledOnce).toBeTruthy();
    });

    using('different recently records amount and settings', [{
        recordSize: 4,
        nextOffset: -1,
        visible: 1,
        expect: {
            open: false,
            showRecentToggle: true
        }
    },{
        recordSize: 5,
        nextOffset: 5,
        visible: 1,
        expect: {
            open: false,
            showRecentToggle: true
        }
    },{
        recordSize: 3,
        nextOffset: 3,
        visible: 0,
        expect: {
            open: true,
            showRecentToggle: true
        }
    },{
        recordSize: 3,
        nextOffset: -1,
        visible: 0,
        expect: {
            open: true,
            showRecentToggle: false
        }
    }], function(value) {
        it('should show recently viewed toggle based on amount of records found', function() {
            var renderPartialSpy = sinon.collection.spy(view, '_renderPartial');

            sinon.collection.stub(app.user.lastState, 'get', function() {
                return value.visible;
            });

            sinon.collection.stub(view.recentlyViewed, 'fetch', function(options) {

                var models = [];
                for (var i = 0; i < value.recordSize; i++) {
                    models.push(new Backbone.Model({
                        name: 'Record ' + (i + 1)
                    }));
                }

                options.success.call(this, {
                    next_offset: value.nextOffset,
                    models: models
                });
            });

            view.populateRecentlyViewed(false);
            expect(renderPartialSpy.lastCall.args[0]).toBe('recently-viewed');
            _.each(value.expect, function(value, key) {
                expect(renderPartialSpy.lastCall.args[1][key]).toBe(value);
            });
        });
    });

    describe('recently viewed toggle', function() {
        beforeEach(function() {
            sinon.collection.stub(view.recentlyViewed, 'fetch', function(options) {
                options.success.call(this, {
                    next_offset: -1,
                    models: []
                });
            });
            sinon.collection.stub(view, 'filterByAccess').returns([
                {label: 'foo', route: '#foo'},
                {label: 'bar', route: '#bar'}
            ]);
            sinon.collection.stub(Handlebars.helpers, 'buildUrl', function() {
                return '#';
            });
        });

        describe('focusing the recently viewed toggle after render by calling view.populateRecentlyViewed()', function() {
            var focusStub;

            beforeEach(function() {
                focusStub = sinon.collection.stub(view, '_focusRecentlyViewedToggle');
            });

            it('should focus the toggle when the menu is open and the parameter is true', function() {
                sinon.collection.stub(view, 'isOpen').returns(true);
                view.render();
                view.populateRecentlyViewed(true);
                expect(focusStub).toHaveBeenCalled();
            });

            it('should not focus the toggle when the menu is open and the parameter is false', function() {
                sinon.collection.stub(view, 'isOpen').returns(true);
                view.render();
                view.populateRecentlyViewed(false);
                expect(focusStub).not.toHaveBeenCalled();
            });

            it('should not focus the toggle when the menu is closed', function() {
                sinon.collection.stub(view, 'isOpen').returns(false);
                view.render();
                view.populateRecentlyViewed(false);
                expect(focusStub).not.toHaveBeenCalled();
            });
        });

        it('should call view.populateRecentlyViewed(true) when [data-toggle="recently-viewed"] is clicked', function() {
            var spy = sinon.collection.spy(view, 'populateRecentlyViewed');
            sinon.collection.stub(view, '_renderPartial', function() {
                view.$el.append('<a href="javascript:void(0);" data-toggle="recently-viewed" tabindex="-1">foo</a>');
            }).withArgs('recently-viewed');
            sinon.collection.stub(view, 'isOpen').returns(true);
            view.render();
            view.populateRecentlyViewed(true);
            view.$('[data-toggle="recently-viewed"]').click();
            // should only have been called from within this test and again on
            // the click
            expect(spy.calledTwice).toBe(true);
            // the first call is inconsequential since it was only a part of
            // setup
            expect(spy.secondCall.args.length).toBe(1);
            expect(spy.secondCall.args[0]).toBe(true);
        });
    });
});
