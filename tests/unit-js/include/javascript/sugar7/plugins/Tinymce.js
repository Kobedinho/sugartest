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
describe('Plugins.Tinymce', function() {
    var module = 'KBContents',
        fieldName = 'htmleditable',
        fieldType = 'htmleditable_tinymce',
        app, field, sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();

        SugarTest.testMetadata.init();
        Handlebars.templates = {};
        SugarTest.loadComponent('base', 'field', fieldType);
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'detail', module);
        SugarTest.loadHandlebarsTemplate('file', 'field', 'base', 'detail', 'EmbeddedFiles');
        SugarTest.loadPlugin('Tinymce');
        SugarTest.testMetadata.set();
        app.data.declareModels();

        field = SugarTest.createField('base', fieldName, fieldType, 'edit', {}, module);
    });

    afterEach(function() {
        delete app.plugins.plugins['field']['Tinymce'];
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        field.dispose();
        field = null;
        app.cache.cutAll();
        app.view.reset();
    });

    it('Append input for embedded files on render.', function() {
        var name = 'testName';
        field.$embeddedInput = $('<input />', {name: name, type: 'file'});
        field.render();
        expect(field.$el.find('input[name=' + name + ']').length).toEqual(1);
    });

    it('Clear element on file type mismatching.', function() {
        tinymce.activeEditor = {
            windowManager: {
                alert: sinonSandbox.stub()
            }
        };
        var winObj = {};
        var fakeFileObj = {name: 'filename.txt', type: 'text/plain'};
        var clearFileSpy = sinonSandbox.spy(field, 'clearFileInput');

        sinonSandbox.stub(field, 'initTinyMCEEditor', $.noop());
        field.render();

        // The fake file is text, image required.
        // Need to replace `input` with `p`, because `FileList` attribute of `HTMLInputElement` is read-only.
        field.$embeddedInput = $('<p/>');
        field.$embeddedInput[0].files = [fakeFileObj];

        field.tinyMCEFileBrowseCallback('fakeName', 'fakeUrl', 'image', winObj);
        field.$embeddedInput.change();

        expect(clearFileSpy).toHaveBeenCalledOnce();
    });

});
