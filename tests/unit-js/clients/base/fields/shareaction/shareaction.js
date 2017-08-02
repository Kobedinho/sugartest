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
describe('Base.Field.Shareaction', function() {

    var app, field, sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        app.drawer = { open: $.noop };

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('share', 'view', 'base', 'subject');
        SugarTest.loadHandlebarsTemplate('share', 'view', 'base', 'body');
        SugarTest.loadHandlebarsTemplate('share', 'view', 'base', 'body-html');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'emailaction');
        SugarTest.loadComponent('base', 'field', 'shareaction');
        SugarTest.testMetadata.set();

        sinonSandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        sinonSandbox.restore();
        app.drawer = undefined;
        SugarTest.testMetadata.dispose();
        field.dispose();
        field = null;
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    it('should set up email options with subject, html body, and text body', function() {
        field = SugarTest.createField('base', 'shareaction', 'shareaction', 'edit');
        expect(field.emailOptions.subject).toContain('TPL_RECORD_SHARE_SUBJECT');
        expect(field.emailOptions.html_body).toContain('TPL_RECORD_SHARE_BODY');
        expect(field.emailOptions.text_body).toContain('TPL_RECORD_SHARE_BODY');
    });

    it('should retrieve the appropriate name for the share parameters', function() {
        var shareParams;

        field = SugarTest.createField('base', 'shareaction', 'shareaction', 'edit');
        field.model.set({
            first_name: 'Foo',
            last_name: 'Bar'
        });
        shareParams = field._getShareParams();
        expect(shareParams.name.toString()).toEqual('Foo Bar');
    });

    it('should re-retrieve the share parameters when the model changes', function() {
        var getShareParamsSpy;

        field = SugarTest.createField('base', 'shareaction', 'shareaction', 'edit');
        getShareParamsSpy = sinonSandbox.spy(field, '_getShareParams');
        field.model.set({
            first_name: 'Foo',
            last_name: 'Bar'
        });
        expect(getShareParamsSpy.callCount).toEqual(1);
    });
});
