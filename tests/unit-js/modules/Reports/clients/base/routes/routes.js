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
describe('Reports.Routes', function() {
    var app;
    var loadViewStub;
    var filterOptionsSpy;
    var redirectSpy;

    beforeEach(function() {
        app = SugarTest.app;
        loadViewStub = sinon.collection.stub(app.controller, 'loadView');
        SugarTest.loadFile('../modules/Reports/clients/base/routes', 'routes', 'js', function(d) {
            eval(d);
            app.routing.start();
        });

        app.isSynced = true;
        sinon.collection.stub(app.router, 'index');
        sinon.collection.stub(app.router, 'hasAccessToModule').returns(true);
        sinon.collection.stub(app.api, 'isAuthenticated').returns(true);
        sinon.collection.stub(app, 'sync');
        filterOptionsSpy = sinon.collection.spy(app.utils, 'FilterOptions');
        redirectSpy = sinon.collection.spy(app.router, 'redirect');
        sinon.collection.stub(app.lang, 'get', function(key) {
            return 'Accounts';
        });
    });

    afterEach(function() {
        app.router.navigate('', {trigger: true});
        Backbone.history.stop();
        sinon.collection.restore();
        app.routing.stop();
        app.events.off('router:init');
    });


    it('should route to Reports listview without filterOptions when no URL params are passed', function() {
        app.router.navigate('Reports', {trigger: true});

        expect(filterOptionsSpy).not.toHaveBeenCalled();
        expect(loadViewStub).toHaveBeenCalledWith({
            filterOptions: null,
            layout: 'records',
            module: 'Reports'
        });
    });

    it('should set filterOptions with the filterModule passed in URL params', function() {
        var filterModule = 'Accounts';
        app.router.navigate('Reports?filterModule=' + filterModule, {trigger: true});

        expect(filterOptionsSpy).toHaveBeenCalled();
        expect(loadViewStub).toHaveBeenCalledWith({
            module: 'Reports',
            layout: 'records',
            filterOptions: {
                filter_populate: {module: {$in: [filterModule]}},
                initial_filter: '$relate',
                initial_filter_label: 'Accounts',
                stickiness: false
            }
        });
    });

    it('should reroute sidecar record view to bwc detail view', function() {
        app.router.navigate('Reports/1234', {trigger: true});
        var bwcRoute = 'bwc/index.php?module=Reports&action=DetailView&record=1234';
        expect(redirectSpy).toHaveBeenCalledWith(bwcRoute);
    });
});
