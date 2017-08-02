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
describe('Base.fields.avatar', function() {
    var app,
        field,
        beanType = 'Contacts',
        model;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('image', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('image', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('avatar', 'field', 'base', 'module-icon');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        model = app.data.createBean(beanType);
        field = SugarTest.createField(
            'base',
            'picture',
            'avatar',
            'detail',
            {width: 42, height: 42, dismiss_label: true},
            beanType,
            model
        );
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
    });

    describe('render', function() {
        it('Should not do anything extra when in edit mode.', function() {
            var spyOnTemplateGetField = sinon.spy(app.template, 'getField');
            // switch to edit mode
            field.setMode('edit');
            field.render();
            expect(spyOnTemplateGetField.calledWithExactly(field.type, 'module-icon', field.module)).toBeFalsy();
            expect(field.$('.image_field').hasClass('image_rounded')).toBeFalsy();
            spyOnTemplateGetField.restore();
        });

        it('Should add the image_rounded css class when in detail mode and there is an avatar.', function() {
            var stubUnderscoreIsEmpty = sinon.stub(_, 'isEmpty', function() { return false; });
            field.render();
            expect(field.$('.image_field').hasClass('image_rounded')).toBeTruthy();
            stubUnderscoreIsEmpty.restore();
        });

        it('Should render the module icon when in detail mode and there is not an avatar.', function() {
            field.render();
            expect(field.$('.image_field').length).toBe(0);
            expect(field.$('.label-' + beanType).length).toBe(1);
        });
    });
});
