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
describe('RevenueLineItems.Base.Field.ConvertToQuote', function() {
    var app, field, moduleName = 'RevenueLineItems', context, def, fieldModel, message, sandbox;

    beforeEach(function() {
        sandbox = sinon.sandbox.create();
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('rowaction', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('alert', 'view', 'base', 'error');

        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'view', 'alert');

        SugarTest.testMetadata.set();

        app = SUGAR.App;
        context = app.context.getContext();
        def = {
            type: 'convert-to-quote',
            event: 'button:convert_to_quote:click',
            name: 'convert_to_quote_button',
            label: 'LBL_CONVERT_TO_QUOTE',
            acl_module: moduleName
        };

        fieldModel = new Backbone.Model({
            id: 'aaa',
            name: 'boo',
            module: moduleName
        });

        field = SugarTest.createField({
            name: 'convert-to-quote',
            type: 'convert-to-quote',
            viewName: 'detail',
            fieldDef: def,
            module: moduleName,
            model: fieldModel,
            loadFromModule: true
        });
    });

    afterEach(function() {
        field = null;
        app = null;
        context = null;
        def = null;
        fieldModel = null;
        message = null;
        sandbox.restore();
    });

    describe('_toggleDisable', function() {
        beforeEach(function() {
            field.render();
        });

        it('will set disabled class on the field element', function() {
            field.model.set('quote_id', 'my_new_quote', {silent: true});
            field._toggleDisable();
            expect(field.getFieldElement().hasClass('disabled')).toBeTruthy();
        });

        it('will remove disabled class when quote_id changes to empty', function() {
            field.model.set('quote_id', 'my_new_quote', {silent: true});
            field._toggleDisable();
            expect(field.getFieldElement().hasClass('disabled')).toBeTruthy();

            field.model.set('quote_id', '');
            expect(field.getFieldElement().hasClass('disabled')).toBeFalsy();
        });

        it('will remove disabled class when quote_id is unset', function() {
            field.model.set('quote_id', 'my_new_quote', {silent: true});
            field._toggleDisable();
            expect(field.getFieldElement().hasClass('disabled')).toBeTruthy();

            field.model.unset('quote_id');
            expect(field.getFieldElement().hasClass('disabled')).toBeFalsy();
        });
    });

    describe('convertToQuote()', function() {
        var layoutTriggerStub;
        var massCollection;
        beforeEach(function() {
            layoutTriggerStub = sinon.collection.stub();
            field.view.layout = {
                trigger: layoutTriggerStub
            };

            field.convertToQuote({});
        });

        afterEach(function() {
            layoutTriggerStub = null;
            delete field.view.layout;
        });

        it('should set mass_collection on the context', function() {
            massCollection = field.context.get('mass_collection');

            expect(massCollection).toBeDefined();
        });

        it('should trigger list:massquote:fire on view.layout', function() {
            expect(layoutTriggerStub).toHaveBeenCalledWith('list:massquote:fire');
        });
    });
});
