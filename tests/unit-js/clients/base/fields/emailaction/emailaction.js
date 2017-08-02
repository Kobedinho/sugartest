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
describe('Base.Field.Emailaction', function() {
    var app, fieldName, parentModel, context, field, createField, sandbox;

    beforeEach(function() {
        var moduleName = 'Contacts';
        app = SugarTest.app;
        fieldName = 'emailaction';
        parentModel = app.data.createBean(moduleName);
        parentModel.module = moduleName;
        context = app.context.getContext();
        context.set('model', parentModel);
        SugarTest.loadComponent('base', 'field', 'button');
        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        sandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field = null;
    });

    createField = function(fieldDef) {
        return SugarTest.createField({
            name: fieldName,
            type: fieldName,
            viewName: 'edit',
            context: context,
            fieldDef: fieldDef
        });
    };

    it('should initialize email options to empty when no defs set', function() {
        field = createField({});
        expect(field.emailOptions).toBeUndefined();
    });

    it('should initialize email options to have to addresses when set_recipient_to_parent set', function() {
        field = createField({set_recipient_to_parent: true});
        expect(field.emailOptions.to_addresses).not.toBeUndefined();
    });

    it('should initialize email options to have related bean when set_related_to_parent set', function() {
        field = createField({set_related_to_parent: true});
        expect(field.emailOptions.related).not.toBeUndefined();
    });

    it('should update email options if the parent model changes', function() {
        var initName = 'foo',
            changeName = 'bar';

        parentModel.set('name', initName);
        field = createField({set_recipient_to_parent: true, set_related_to_parent: true});
        expect(field.emailOptions.related.get('name')).toEqual(initName);
        expect(field.emailOptions.to_addresses[0].bean.get('name')).toEqual(initName);

        parentModel.set('name', changeName);
        expect(field.emailOptions.related.get('name')).toEqual(changeName);
        expect(field.emailOptions.to_addresses[0].bean.get('name')).toEqual(changeName);
    });

    it('should render after updating email options if the parent model changes', function() {
        parentModel.set('name', 'foo');
        field = createField();
        sandbox.stub(field, '_updateEmailOptions');
        sandbox.stub(field, 'render');
        parentModel.set('name', 'bar');
        expect(field._updateEmailOptions).toHaveBeenCalled();
        expect(field.render).toHaveBeenCalled();
    });
});
