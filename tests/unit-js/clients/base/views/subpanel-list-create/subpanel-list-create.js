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
describe('Base.View.SubpanelListCreate', function() {
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

        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'subpanel-list');

        if (!_.isFunction(app.utils.generateUUID)) {
            app.utils.generateUUID = function() {}
        }
        sinon.sandbox.stub(app.utils, 'generateUUID', function() {
            return 'testUUID'
        });

        view = SugarTest.createView('base', null, 'subpanel-list-create', {}, context, false, layout, true);
    });

    afterEach(function() {
        sinon.sandbox.restore();
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        beforeEach(function() {
            sinon.sandbox.stub(view, '_super', function() {});
        });

        it('should set the dataView on the context', function() {
            view.initialize({});
            expect(view.context.get('dataView')).toBe('subpanel-list-create');
        });

        it('should set isCreateSubpanel to be true on the context', function() {
            view.initialize({});
            expect(view.context.get('isCreateSubpanel')).toBeTruthy();
        });
    });

    describe('bindDataChange', function() {
        beforeEach(function() {
            sinon.sandbox.spy(view, '_addBeanToList');
        });

        it('should call _addBeanToList when collection length == 0', function() {
            view.collection = new Backbone.Collection();
            view.bindDataChange();
            expect(view._addBeanToList).toHaveBeenCalled();
        });
    });

    describe('render', function() {
        beforeEach(function() {
            sinon.sandbox.stub(view, '_super', function() {});
            sinon.sandbox.stub(view, '_toggleFields', function() {});
        });

        it('should call toggleFields', function() {
            view.render();
            expect(view._toggleFields).toHaveBeenCalledWith(true);
        });
    });
});
