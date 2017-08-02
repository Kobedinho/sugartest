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
describe('Base.View.ConfigPanel', function() {
    var app,
        context,
        options,
        view;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());

        sinon.collection.stub(app.controller.context, 'get', function() {
            return 'Opportunities'
        });

        var meta = {
            label: 'testLabel'
        };

        options = {
            context: context,
            meta: meta
        };

        view = SugarTest.createView('base', null, 'config-panel', meta, context);
    });

    afterEach(function() {
        sinon.collection.restore();
        view = null;
    });

    describe('initialize()', function() {
        it('should set `this.titleViewNameTitle`', function() {
            view.initialize(options);
            expect(view.titleViewNameTitle).toBe('testLabel');
        });
    });

    describe('_render()', function() {
        beforeEach(function() {
            sinon.collection.stub(view, 'updateTitle', function() {})
        });

        it('should add the "accordion-group" class to this.$el', function() {
            view._render();
            expect(view.$el.hasClass('accordion-group')).toBeTruthy();
        });

        it('should call updateTitle()', function() {
            view._render();
            expect(view.updateTitle).toHaveBeenCalled();
        });
    });


    describe('updateTitle()', function() {
        beforeEach(function() {
            sinon.collection.stub(view, '_updateTitleValues', function() {});
            sinon.collection.stub(view, '_updateTitleTemplateVars', function() {});
            sinon.collection.stub(view, '$', function() {
                return {
                    html: function() {}
                }
            });
            sinon.collection.stub(view, 'toggleTitleTpl', function() {});
        });

        it('should call _updateTitleValues()', function() {
            view.updateTitle();
            expect(view._updateTitleValues).toHaveBeenCalled();
        });

        it('should call _updateTitleTemplateVars()', function() {
            view.updateTitle();
            expect(view._updateTitleTemplateVars).toHaveBeenCalled();
        });

        it('should set the view $el', function() {
            view.updateTitle();
            expect(view.$).toHaveBeenCalled();
        });
    });

    describe('_updateTitleValues()', function() {
        it('should set `this.titleSelectedValues`', function() {
            view.model.set('config-panel', 'testValue');
            view._updateTitleValues();
            expect(view.titleSelectedValues).toBe('testValue');
        });
    });

    describe('_updateTitleTemplateVars()', function() {
        it('should set `this.titleTemplateVars`', function() {
            view.model.set('config-panel', 'testValue');
            view._updateTitleValues();
            view._updateTitleTemplateVars();
            expect(view.titleTemplateVars).toEqual({
                title: 'testLabel',
                selectedValues: 'testValue',
                viewName: 'config-panel'
            });
        });
    });
});
