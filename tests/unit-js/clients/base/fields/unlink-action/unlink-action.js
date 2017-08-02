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
describe('View.Fields.UnlinkAction', function() {

    var app, field, sandbox, relatedFields, moduleName = 'Contacts';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', 'tabbed-dashlet');
        SugarTest.loadComponent('base', 'view', 'planned-activities');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'unlink-action');
        field = SugarTest.createField('base', 'unlink-action', 'unlink-action', 'edit', {
            'type':'rowaction',
            'css_class':'btn',
            'tooltip':'Unlink',
            'event':'list:unlinkrow:fire',
            'icon':'fa fa-trash-o',
            'acl_action':'delete'
        }, moduleName);

        sandbox = sinon.sandbox.create();
        sandbox.stub(app.data, 'getRelateFields', function(){
            return relatedFields;
        });
        relatedFields = [{required: false}];
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field = null;
        sandbox.restore();
    });

    it('should hide action if the user does not have access', function() {
        field.model = app.data.createBean(moduleName);
        var aclStub = sinon.stub(app.acl, 'hasAccessToModel', function() {
            return false;
        });
        field.render();
        expect(field.isHidden).toBeTruthy();
        aclStub.restore();
    });

    it('should hide action if parentModule matches Home', function() {
        field.context.set('parentModule', 'Home');

        field.render();
        expect(field.isHidden).toBeTruthy();
    });

    it('should hide action if any related field is required', function() {
        field.context.set('parentModule', moduleName);
        field.context.set('link', true);

        field.model = app.data.createBean(moduleName);
        relatedFields = [{required: true}];
        field.render();
        expect(field.isHidden).toBeTruthy();

        relatedFields = [{required: false}, {required: true}];
        field.render();
        expect(field.isHidden).toBeTruthy();

        relatedFields = [{required: false}, {required: false}];
        field.render();
        expect(field.isHidden).toBeFalsy();
    });
});
