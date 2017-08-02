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
describe('modules.kbcontents.clients.base.fields.attachments', function() {
	var app, field, sandbox,
        module = 'KBContents',
        model, 
        apiCallStub,
        fieldName = 'attachments',
        fieldType = 'attachments',
        fieldDef = {
        	'name': 'attachment_list',
            'type': 'attachments',
            'link': 'attachments',
            'field' : 'attachments',
            'module': 'Notes',
            'modulefield': 'filename'
        };

    beforeEach(function() {
        sandbox = sinon.sandbox.create();
        Handlebars.templates = {};
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', fieldType);
        SugarTest.loadPlugin('DragdropAttachments');
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'edit', module);
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'detail', module);
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'selection-partial', module);
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        apiCallStub = sandbox.stub(app.api, 'call', function(method, url, data, callbacks) {
            if (callbacks && callbacks.success)
                callbacks.success({});
        });
        model = app.data.createBean(module);
    });

    afterEach(function() {
        sandbox.restore();
        if (field) {
            field.dispose();
        }
        apiCallStub.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
        delete app.plugins.plugins['field']['DragdropAttachments'];
    });

    it('should not rendered download all attachments button when attachments empty', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'detail', 
            fieldDef, module, model, null, true
        );
        field.render();
        expect(field.$('[data-action="download-all"]').length).toEqual(0);
    });

    it('should rendered download all attachments button when attachments not empty', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'detail', 
            fieldDef, module, model, null, true
        );
        model.set('attachments', [{   
            id: 'testAttach1',
            name: 'testAttach1'
        }]);
        field.render();
        expect(field.$('[data-action="download-all"]').length).toEqual(1);
    });

    it('should load related collection when model is not new', function() {
        var getRelatedCollectionStub = sandbox.stub(model, 'getRelatedCollection', function() {
            return {
                fetch: function(callbacks) {
                    if (callbacks && callbacks.success)
                        callbacks.success({});
                }
            }
        });
        model.set({id: 'test_id'});
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', 
            fieldDef, module, model, null, true
        );
        expect(getRelatedCollectionStub).toHaveBeenCalled();
    });

    it('format should return valid value', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'detail', 
            fieldDef, module, model, null, true
        );
        field.render();

        var result = field.format([{   
            id: 'testAttach1',
            name: 'testAttach1.jpg',
            mimeType: 'image'
        },
        {   
            id: 'testAttach2',
            name: 'testAttach2'
        }]);

        expect(result.length).toEqual(2);
        expect(_.first(result).mimeType).toEqual('image');
        expect(_.last(result).mimeType).toEqual('application/octet-stream');
    });

    it('should be called setSelect2Node during render', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', 
            fieldDef, module, model, null, true
        );
        var setSelect2Node = sandbox.spy(field, 'setSelect2Node');
        field.render();
        expect(setSelect2Node).toHaveBeenCalled();
    });

    it('should be called setSelect2Node on bind DOM change', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', 
            fieldDef, module, model, null, true
        );
        field.render();
        var setSelect2Node = sandbox.spy(field, 'setSelect2Node');
        field.bindDomChange();
        expect(setSelect2Node).toHaveBeenCalled();
    });

    it('should be able to get DOM element using getFileNode function', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', 
            fieldDef, module, model, null, true
        );
        field.render();
        expect(field.getFileNode().length).toEqual(1);
    });

    it('should add the event handlers to upload a file', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'edit',
            fieldDef, module, model, null, true
        );
        field.render();

        var event = 'change ' + field.getFileNode().selector;

        expect(field.events[event]).toBeDefined();
        expect(field.events[event]).toEqual('uploadFile');
    });

    it('should be able to download all files as archive from server', function() {
        var apiDownloadCallStub = sandbox.stub(app.api, 'fileDownload', function(url, callbacks) {
            if (callbacks && callbacks.success)
                callbacks.success({});
        });

        model.set('attachments', [{   
            id: 'testAttach1',
            name: 'testAttach1'
        }]);

        field = SugarTest.createField('base', fieldName, fieldType, 'detail', 
            fieldDef, module, model, null, true
        );
        field.render();
        field.$('[data-action="download-all"]').click();
        expect(apiDownloadCallStub).toHaveBeenCalled();
    });

    it('should be able to update `Select2` data from model', function() {
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', 
            fieldDef, module, model, null, true
        );
        model.set('attachments', [{   
            id: 'testAttach1',
            name: 'testAttach1'
        }]);
        field.render();
        var sel2Data = field.$node.select2('data');
        model.set('attachments', [{   
            id: 'testAttach2',
            name: 'testAttach2'
        }]);
        field.refreshFromModel();
        expect(sel2Data).not.toEqual(field.$node.select2('data'));
    });

});
