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
describe("Record View", function () {
    var moduleName = 'Cases',
        app,
        viewName = 'record',
        sinonSandbox,
        view,
        createListCollection,
        buildGridsFromPanelsMetadataStub;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('rowaction', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'headerpane');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'tabspanels');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'businesscard');
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'actiondropdown');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.addViewDefinition(viewName, {
            "buttons": [
                {
                    "type": "button",
                    "name": "cancel_button",
                    "label": "LBL_CANCEL_BUTTON_LABEL",
                    "css_class": "btn-invisible btn-link",
                    "showOn": "edit"
                },
                {
                    "type": "actiondropdown",
                    "name": "main_dropdown",
                    "buttons": [
                        {
                            "type": "rowaction",
                            "event": "button:edit_button:click",
                            "name": "edit_button",
                            "label": "LBL_EDIT_BUTTON_LABEL",
                            "primary": true,
                            "showOn": "view",
                            "acl_action":"edit"
                        },
                        {
                            "type": "rowaction",
                            "event": "button:save_button:click",
                            "name": "save_button",
                            "label": "LBL_SAVE_BUTTON_LABEL",
                            "primary": true,
                            "showOn": "edit",
                            "acl_action":"edit"
                        },
                        {
                            "type": "rowaction",
                            "name": "delete_button",
                            "label": "LBL_DELETE_BUTTON_LABEL",
                            "showOn": "view",
                            "acl_action":"delete"
                        },
                        {
                            "type": "rowaction",
                            "name": "duplicate_button",
                            "label": "LBL_DUPLICATE_BUTTON_LABEL",
                            "showOn": "view",
                            'acl_module': moduleName
                        }
                    ]
                }
            ],
            "panels": [
                {
                    "name": "panel_header",
                    "header": true,
                    "fields": [{name: "name", span: 8, labelSpan: 4}],
                    "labels": true
                },
                {
                    "name": "panel_body",
                    "label": "LBL_PANEL_2",
                    "columns": 1,
                    "labels": true,
                    "labelsOnTop": false,
                    "placeholders": true,
                    "fields": [
                        {name: "description", type: "base", label: "description", span: 8, labelSpan: 4},
                        {name: "case_number", type: "float", label: "case_number", span: 8, labelSpan: 4},
                        {name: "type", type: "text", label: "type", span: 8, labelSpan: 4}
                    ]
                },
                {
                    "name": "panel_hidden",
                    "hide": true,
                    "columns": 1,
                    "labelsOnTop": false,
                    "placeholders": true,
                    "fields": [
                        {name: "created_by", type: "date", label: "created_by", span: 8, labelSpan: 4},
                        {name: "date_entered", type: "date", label: "date_entered", span: 8, labelSpan: 4},
                        {name: "date_modified", type: "date", label: "date_modified", span: 8, labelSpan: 4},
                        {name: "modified_user_id", type: "date", label: "modified_user_id", span: 8, labelSpan: 4}
                    ]
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.routing.start();
        app.data.declareModels();
        sinonSandbox = sinon.sandbox.create();

        view = SugarTest.createView("base", moduleName, "record", null, null);

        buildGridsFromPanelsMetadataStub = sinon.stub(view, "_buildGridsFromPanelsMetadata", function(panels) {
            view.hiddenPanelExists = true;

            // The panel grid contains references to the actual fields found in panel.fields, so the fields must
            // be modified to include the field attributes that would be calculated during a normal render
            // operation and then added to the grid in the correct row and column.
            panels[0].grid      = [[panels[0].fields[0]]];
            panels[1].grid      = [
                [panels[1].fields[0]],
                [panels[1].fields[1]],
                [panels[1].fields[2]]
            ];
            panels[2].grid      = [
                [panels[2].fields[0]],
                [panels[2].fields[1]],
                [panels[2].fields[2]],
                [panels[2].fields[3]]
            ];
        });
    });

    afterEach(function () {
        sinonSandbox.restore();
        sinon.collection.restore();
        buildGridsFromPanelsMetadataStub.restore();
        app.routing.stop();
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        app.data.reset();
        view.dispose();
        view = null;
    });

    describe('Render', function () {
        it("Should render 8 editable fields and 2 sets of buttons", function () {

            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            var actual_field_length = _.keys(view.editableFields).length,
                actual_button_length = _.keys(view.buttons).length;
            expect(actual_field_length).toBe(8);
            expect(actual_button_length).toBe(2);
        });



        it("Should hide 4 editable fields", function () {
            var hiddenFields = 0;
            view.hidePanel = true; //setting directly instead of using togglePlugin
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            _.each(view.editableFields, function (field) {
                if ((field.$el.closest('.hide').length === 1)) {
                    hiddenFields++;
                }
            });

            expect(hiddenFields).toBe(4);
        });

        it("Should place name field in the header", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            expect(view.getField('name').$el.closest('.headerpane').length === 1).toBe(true);
        });

        it("Should not render any fields when a user doesn't have access to the data", function () {
            sinonSandbox.stub(SugarTest.app.acl, 'hasAccessToModel', function () {
                return false;
            });
            sinonSandbox.stub(SugarTest.app.error, 'handleRenderError', $.noop());

            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            expect(_.size(view.fields)).toBe(0);
        });

        it("should call clearValidationErrors when Cancel is clicked", function () {
            var clock = sinon.useFakeTimers();
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var stub = sinon.stub(view, "clearValidationErrors");
            view.cancelClicked();
            //Use sinon clock to delay expectations since decoration is deferred
            clock.tick(20);
            expect(stub.calledOnce).toBe(true);
            stub.restore();
            clock.restore();
        });

        it("Should display all 8 editable fields when more link is clicked", function () {
            var hiddenFields = 0,
                visibleFields = 0;

            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            view.$('.more').click();
            _.each(view.editableFields, function (field) {
                if (field.$el.closest('.hide').length === 1) {
                    hiddenFields++;
                } else {
                    visibleFields++;
                }

            });

            expect(hiddenFields).toBe(0);
            expect(visibleFields).toBe(8);
        });

        it("Should not be editable when this field is in the noEditFields array", function () {
            var noEditFields = ["name", "created_by", "date_entered", "date_modified", "case_number"];

            _.each(view.meta.panels, function (panel) {
                _.each(panel.fields, function (field) {
                    if (_.indexOf(noEditFields, field.name) >= 0) {
                        view.noEditFields.push(field.name);
                    }
                }, this);
            }, this);

            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            view.$('.more').click();

            var editableFields = 0;
            _.each(view.editableFields, function (field) {
                if (field.$el.closest(".record-cell").find(".record-edit-link-wrapper").length === 1) {
                    editableFields++;
                }
            });

            expect(editableFields).toBe(3);
            expect(_.size(view.editableFields)).toBe(3);
        });

        it('Should define view `hashSync` settings `true` by default', function() {
            view.render();
            expect(view.meta.hashSync).toBeTruthy();
        });
    });

    describe('Edit', function () {
        it("Should toggle to an edit mode when a user clicks on the inline edit icon", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var field = view.getField('name');
            sinon.collection.stub(field, 'hasChanged', function() {
                return false;
            });

            expect(field.options.viewName).toBe(view.action);

            field.$el.closest('.record-cell').find('a.record-edit-link').click();

            expect(field.options.viewName).toBe('edit');
        });

        it("Should toggle all editable fields to edit modes when a user clicks on the edit button", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            _.each(view.editableFields, function (field) {
                expect(field.options.viewName).toBe(view.action);
            });

            view.context.trigger('button:edit_button:click');

            waitsFor(function () {
                return (_.last(view.editableFields)).options.viewName === 'edit';
            }, 'it took too long to wait switching view', 1000);

            runs(function () {
                _.each(view.editableFields, function (field) {
                    expect(field.options.viewName).toBe('edit');
                });
            });
        });

        it('Should ask the model to revert if cancel clicked', function() {
            view.render();
            var revertStub = sinon.collection.stub(view.model, 'revertAttributes');

            view.context.trigger('button:edit_button:click');
            view.model.set({
                name: 'Bar'
            });

            view.context.trigger('button:cancel_button:click');
            expect(revertStub).toHaveBeenCalled();
        });

        describe('Hash synchronisation with record/button state', function() {
            var navigateStub;

            beforeEach(function() {
                navigateStub = sinon.collection.stub(app.router, 'navigate');
                view.model.set('id', 'my-case-id');
            });

            it('Should enter in edit mode if typed url is like /:record/edit', function() {
                var toggleStub = sinon.collection.stub(view, 'toggleEdit'),
                    setButtonStatesStub = sinon.collection.stub(view, 'setButtonStates');
                view.action = 'edit';
                view.render();

                expect(toggleStub).toHaveBeenCalledWith(true);
                expect(setButtonStatesStub).toHaveBeenCalledWith(view.STATE.EDIT);
            });

            using('different hashSync settings',
                [true, false],
                function(hashSyncValue) {

                    it('Should handle the url properly if edit button is clicked', function() {
                        view.render();
                        view.meta.hashSync = hashSyncValue;
                        view.context.trigger('button:edit_button:click');

                        if (view.meta.hashSync) {
                            expect(navigateStub).toHaveBeenCalledWith('Cases/my-case-id/edit', {trigger: false});
                        } else {
                            expect(navigateStub).not.toHaveBeenCalled();
                        }
                    });

                    it('Should handle the url properly if cancel button is clicked', function() {
                        view.render();
                        view.meta.hashSync = hashSyncValue;
                        view.context.trigger('button:edit_button:click');
                        view.context.trigger('button:cancel_button:click');

                        if (view.meta.hashSync) {
                            expect(navigateStub).toHaveBeenCalledWith('Cases/my-case-id/edit', {trigger: false});
                            expect(navigateStub).toHaveBeenCalledWith('Cases/my-case-id', {trigger: false});
                        } else {
                            expect(navigateStub).not.toHaveBeenCalled();
                        }
                    });

                    it('Should handle the url properly if save button is clicked and unset context action', function() {

                        // need this to pretend that the model is valid for record view first pass
                        sinonSandbox.stub(view.model, 'doValidate', function(fields, cb) {
                            cb(true);
                        });

                        // don't save the model (no need to send to the server)
                        sinonSandbox.stub(view.model, 'save');

                        view.render();
                        view.meta.hashSync = hashSyncValue;
                        view.context.trigger('button:edit_button:click');
                        view.context.trigger('button:save_button:click');

                        if (view.meta.hashSync) {
                            expect(navigateStub).toHaveBeenCalledWith('Cases/my-case-id/edit', {trigger: false});
                            expect(navigateStub).toHaveBeenCalledWith('Cases/my-case-id', {trigger: false});
                        } else {
                            expect(navigateStub).not.toHaveBeenCalled();
                        }
                        expect(view.context.get('action')).toBeUndefined();
                    });

                });
        });
    });

    describe('Locked Fields', function() {
        var fieldset = {
            name: 'custom_fieldset',
            type: 'fieldset',
            fields: [
                {name: 'fake_field1'},
                {name: 'fake_field2'}
            ]
        };

        beforeEach(function() {
            buildGridsFromPanelsMetadataStub.restore();
            view.meta.panels[1].fields.push(fieldset);
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description',
                fake_field1: 'fake1',
                fake_field2: 'fake2'
            });
        });

        it('should set _hasLockedFields to true when a field is locked', function() {
            view.render();
            view.model.set('locked_fields', ['name']);
            view.handleLockedFields();

            expect(view._hasLockedFields).toBe(true);
        });

        it('should set _hasLockedFields to true when a fieldset field is locked', function() {
            view.render();
            view.model.set('locked_fields', ['fake_field1']);
            view.handleLockedFields();

            expect(view._hasLockedFields).toBe(true);
        });

        it('should only show a lock if the field is locked', function() {
            view.render();
            view.model.set('locked_fields', ['case_number']);
            view.handleLockedFields();

            expect(view.$('.record-lock-link-wrapper[data-name="case_number"]').hasClass('hide')).toBe(false);
            expect(view.$('.record-lock-link-wrapper[data-name="description"]').hasClass('hide')).toBe(true);
        });

        it('should only show a lock if all fields in a fieldset are locked', function() {
            view.render();
            view.model.set('locked_fields', ['fake_field1', 'fake_field2']);
            view.handleLockedFields();

            expect(view.$('.record-lock-link-wrapper[data-name="custom_fieldset"]').hasClass('hide')).toBe(false);
        });

        it('should not show a lock if a field in a fieldset is not locked', function() {
            view.render();
            view.model.set('locked_fields', ['fake_field1']);
            view.handleLockedFields();

            expect(view.$('.record-lock-link-wrapper[data-name="custom_fieldset"]').hasClass('hide')).toBe(true);
        });
    });

    describe("build grids", function() {
        var hasAccessToModelStub,
            readonlyFields = ["created_by", "date_entered", "date_modified"],
            aclFailFields  = ["case_number"];

        beforeEach(function() {
            buildGridsFromPanelsMetadataStub.restore();
            hasAccessToModelStub = sinon.stub(SugarTest.app.acl, "hasAccessToModel", function (method, model, field) {
                return _.indexOf(aclFailFields, field) < 0;
            });
        });

        afterEach(function() {
            hasAccessToModelStub.restore();
        });

        it("Should convert string fields to objects", function() {
            var meta = {
                panels: [{
                    fields: ["description"]
                }]
            };
            view._buildGridsFromPanelsMetadata(meta.panels);
            expect(meta.panels[0].fields[0].name).toBe("description");
        });

        it("Should add readonly fields and acl fail fields to the noEditFields array", function () {
            var meta = {
                panels: [{
                    fields: [
                        {name: "case_number"},
                        {name: "name"},
                        {name: "description"},
                        {name: "created_by"},
                        {name: "date_entered"},
                        {name: "date_modified"}
                    ]
                }]
            };

            _.each(meta.panels, function (panel) {
                _.each(panel.fields, function (field) {
                    if (_.indexOf(readonlyFields, field.name) >= 0) {
                        field.readonly = true;
                    }
                }, this);
            }, this);

            view._buildGridsFromPanelsMetadata(meta.panels);

            var actual   = view.noEditFields,
                expected = _.union(readonlyFields, aclFailFields);

            expect(actual.length).toBe(expected.length);
            _.each(actual, function (noEditField) {
                expect(_.indexOf(expected, noEditField) >= 0).toBeTruthy();
            });
        });

        it("Should add a field to the noEditFields array when a user doesn't have write access on the field", function () {
            var meta = {
                panels: [{
                    fields: [
                        {name: "case_number"},
                        {name: "name"},
                        {name: "description"},
                        {name: "created_by"},
                        {name: "date_entered"},
                        {name: "date_modified"}
                    ]
                }]
            };

            hasAccessToModelStub.restore();
            sinonSandbox.stub(SugarTest.app.acl, "_hasAccessToField", function (action, acls, field) {
                return field !== 'case_number';
            });
            sinonSandbox.stub(SugarTest.app.user, "getAcls", function () {
                var acls = {};
                acls[moduleName] = {
                    edit: "yes",
                    fields: {
                        name: {
                            write: "no"
                        }
                    }
                };
                return acls;
            });

            view._buildGridsFromPanelsMetadata(meta.panels);

            var actual   = view.noEditFields,
                expected = aclFailFields;

            expect(actual.length).toBe(expected.length);
            _.each(actual, function (noEditField) {
                expect(_.indexOf(expected, noEditField) >= 0).toBeTruthy();
            });
        });

        it("Should add a fieldset to the noEditFields array when user does not have write access to any of the child fields", function () {
            var fieldset = {
                name: 'fieldset_field',
                type: 'fieldset',
                fields: [{name: 'case_number'}]
            };
            var meta = {
                panels: [{
                    fields: [fieldset]
                }]
            };

            hasAccessToModelStub.restore();
            sinonSandbox.stub(SugarTest.app.acl, "_hasAccessToField", function (action, acls, field) {
                return field !== 'case_number';
            });

            view._buildGridsFromPanelsMetadata(meta.panels);

            var actual = view.noEditFields;

            expect(actual.length).toBe(1);
            expect(actual[0]).toEqual(fieldset.name);
        });

        it("Should not add a fieldset to the noEditFields array when user has write access to any child fields", function () {
            var fieldset = {
                name: 'fieldset_field',
                type: 'fieldset',
                fields: [{name: 'case_number'}, {name: 'blah'}]
            };
            var meta = {
                panels: [{
                    fields: [fieldset]
                }]
            };

            hasAccessToModelStub.restore();
            sinonSandbox.stub(SugarTest.app.acl, "_hasAccessToField", function (action, acls, field) {
                return field !== 'case_number';
            });

            view._buildGridsFromPanelsMetadata(meta.panels);

            var actual = view.noEditFields;

            expect(_.isEmpty(actual)).toBe(true);
        });
    });

    describe('Switching to next and previous record', function () {

        beforeEach(function () {
            createListCollection = function (nbModels, offsetSelectedModel) {
                view.context.set('listCollection', new app.data.createBeanCollection(moduleName));
                view.collection = app.data.createBeanCollection(moduleName);

                var modelIds = [];
                for (var i = 0; i <= nbModels; i++) {
                    var model = new Backbone.Model(),
                        id = i + '__' + Math.random().toString(36).substr(2, 16);

                    model.set({id: id});
                    if (i === offsetSelectedModel) {
                        view.model.set(model.toJSON());
                        view.collection.add(model);
                    }
                    view.context.get('listCollection').add(model);
                    modelIds.push(id);
                }
                return modelIds;
            };
        });

        it("Should find previous and next model from list collection", function () {
            var modelIds = createListCollection(5, 3);
            view.showPreviousNextBtnGroup();
            expect(view.showPrevious).toBeTruthy();
            expect(view.showNext).toBeTruthy();
        });

        it("Should find previous model from list collection", function () {
            var modelIds = createListCollection(5, 5);
            view.showPreviousNextBtnGroup();
            expect(view.showPrevious).toBeTruthy();
            expect(view.showNext).toBeFalsy();
        });

        it("Should find next model from list collection", function () {
            var modelIds = createListCollection(5, 0);
            view.showPreviousNextBtnGroup();
            expect(view.showPrevious).toBeFalsy();
            expect(view.showNext).toBeTruthy();
        });
    });

    describe('duplicateClicked', function () {
        var triggerStub, openStub, closeStub, expectedModel = {id: 'abcd12345'};

        beforeEach(function () {
            closeStub = sinon.stub();
            triggerStub = sinon.stub(Backbone.Model.prototype, 'trigger', function (event, model) {
                if (event === "duplicate:before") {
                    expect(model.get("name")).toEqual(view.model.get("name"));
                    expect(model.get("description")).toEqual(view.model.get("description"));
                    expect(model).toNotBe(view.model);
                }
            });
            SugarTest.app.drawer = {
                open: function () {
                },
                close: function () {
                }
            };
            openStub = sinon.stub(SugarTest.app.drawer, "open", function (opts, closeCallback) {
                expect(opts.context.model).toBeDefined();
                expect(opts.layout).toEqual("create");
                expect(opts.context.model.get("name")).toEqual(view.model.get("name"));
                expect(opts.context.model.get("description")).toEqual(view.model.get("description"));
                expect(opts.context.model).toNotBe(view.model);
                if (closeCallback) {
                    closeStub(expectedModel);
                }
            });
        });
        afterEach(function () {
            if (triggerStub) {
                triggerStub.restore();
            }
            if (openStub) {
                openStub.restore();
            }
        });
        it("should trigger 'duplicate:before' on model prior to opening create drawer", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            triggerStub.reset();
            view.layout = new Backbone.Model();

            view.duplicateClicked();
            expect(triggerStub.called).toBe(true);
            expect(triggerStub.calledWith("duplicate:before")).toBe(true);
            expect(openStub.called).toBe(true);
            expect(triggerStub.calledBefore(openStub)).toBe(true);
        });

        it(" should pass model to mutate with 'duplicate:before' event", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            triggerStub.reset();
            view.layout = new Backbone.Model();

            view.duplicateClicked();
            expect(triggerStub.called).toBe(true);
            expect(triggerStub.calledWith('duplicate:before')).toBe(true);
            //Further expectations in stub
        });

        it("should fire 'drawer:create:fire' event with copied model set on context", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            triggerStub.reset();
            view.layout = new Backbone.Model();
            view.duplicateClicked();
            expect(openStub.called).toBe(true);
            expect(openStub.lastCall.args[0].context.model.get("name")).toEqual(view.model.get("name"));
        });

        it("should call close callback", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description',
                module: "Bugs"
            });
            triggerStub.reset();
            view.layout = new Backbone.Model();
            view.duplicateClicked();
            expect(closeStub.lastCall.args[0].id).toEqual(expectedModel.id);
        });
    });

    describe('copying nested collections', function() {
        var collection, sandbox;

        beforeEach(function() {
            SugarTest.loadPlugin('VirtualCollection');
            app.data.declareModelClass(moduleName, app.metadata.getModule(moduleName), app.config.platform, {
                plugins: ['VirtualCollection']
            });

            collection = app.data.createBeanCollection(view.model.module, [
                app.data.createBean(view.model.module, {id: 1, name: 'aaa', status: 'aaa'}),
                app.data.createBean(view.model.module, {id: 2, name: 'bbb', status: 'bbb'}),
                app.data.createBean(view.model.module, {id: 3, name: 'ccc', status: 'aaa'})
            ]);
            collection.fieldName = 'name';
            collection.fetchAll = function(options) {
                options.success(this, options);
            };

            view.model.set(collection.fieldName, collection);
            view.model.trigger = $.noop;

            sandbox = sinon.sandbox.create();
            sandbox.stub(view, 'getField').returns({
                def: {
                    fields: ['name', 'status']
                }
            });
        });

        afterEach(function() {
            sandbox.restore();
            app.data.declareModels();
        });

        it('should not call `fetchAll` when `getCollectionFieldNames` does not exist', function() {
            sandbox.spy(collection, 'fetchAll');

            view.duplicateClicked();

            expect(collection.fetchAll).not.toHaveBeenCalled();
        });

        it('should copy nested collections', function() {
            var target = new app.data.createBean(view.model.module, {});
            target.set(collection.fieldName, new Backbone.Collection());

            view.model.getCollectionFieldNames = function() {
                return [collection.fieldName];
            };

            view._copyNestedCollections(view.model, target);

            expect(target.get(collection.fieldName).length).toBe(collection.length);
        });
    });

    describe('Field labels', function () {
        it("should be hidden on view for headerpane fields", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            expect(view.$('.record-label[data-name=name]').closest('.record-cell').hasClass('edit')).toBe(false);
        });

        it("should be shown on view for non-headerpane fields", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });

            expect(view.$('.record-label[data-name=description]').css('display')).not.toBe('none');
        });

        it("should be shown on edit for headerpane fields", function () {
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var field = view.getField('name');
            sinon.collection.stub(field, 'hasChanged', function() {
                return false;
            });

            field.$el.closest('.record-cell').find('a.record-edit-link').click();

            expect(view.$('.record-label[data-name=name]').closest('.record-cell').hasClass('edit')).toBe(true);
        });
    });

    describe('Set Button States', function () {
        it('should show buttons where the showOn states match', function() {
            // we need our buttons to be initialized before we can test them
            view.render();
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            }, {
                silent:true
            });

            // load up with our spies to detect nefarious activity
            _.each(view.buttons,function(button) {
                sinonSandbox.spy(button,'hide');
                sinonSandbox.spy(button,'show');
            });

            view.setButtonStates(view.STATE.EDIT);

            // with access, assume the show/hide are based solely on showOn
            _.each(view.buttons,function(button) {
                var shouldHide = !!button.def.showOn && (button.def.showOn !== view.STATE.EDIT);
                expect(button.hide.called).toEqual(shouldHide);
                expect(button.show.called).toEqual(!shouldHide);
            });
        });

    });

    describe('hasUnsavedChanges', function() {
        it('should NOT warn unsaved changes when synced values are matched with current model value', function() {
            var attrs = {
                name: 'Original',
                case_number: 456,
                description: 'Previous description'
            };
            view.model.setSyncedAttributes(attrs);
            view.model.set(attrs);
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(false);
        });
        it('should warn unsaved changes among the synced attributes', function() {
            view.model.setSyncedAttributes({
                name: 'Original',
                case_number: 456,
                description: 'Previous description'
            });
            view.model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(true);
        });
        it('should warn unsaved changes ONLY IF the changes are editable fields', function() {
            view.model.setSyncedAttributes({
                name: 'Original',
                case_number: 456,
                description: 'Previous description',
                non_editable: 'system value'
            });
            //un-editable field
            view.model.set({
                name: 'Original',
                case_number: 456,
                description: 'Previous description'
            });
            view.render();
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(false);
            //Changed non-editable field
            view.model.set({
                non_editable: 'user value'
            });
            actual = view.hasUnsavedChanges();
            var editableFields = _.pluck(view.editableFields, 'name');
            expect(_.contains(editableFields, 'non_editable')).toBe(false);
            expect(actual).toBe(false);
            //Changed editable field
            view.model.set({
                description: 'Changed description'
            });
            actual = view.hasUnsavedChanges();
            expect(_.contains(editableFields, 'description')).toBe(true);
            expect(actual).toBe(true);
        });
        it('should warn unsaved changes when values inside a fieldset changes', function() {
            view.meta.panels[0].fields.push({
                name: 'foo',
                fields: [{
                    name: 'bar'
                }]
            });
            view.model.setSyncedAttributes({
                bar: 'test1'
            });
            view.model.set({
                bar: 'test2'
            });

            expect(view.hasUnsavedChanges()).toBe(true);
        });
        it('should warn unsaved changes when field that contains other fields changes', function() {
            view.meta.panels[0].fields.push({
                name: 'foo',
                fields: [{
                    name: 'bar'
                }]
            });
            view.model.setSyncedAttributes({
                foo: 'test1'
            });
            view.model.set({
                foo: 'test2'
            });

            expect(view.hasUnsavedChanges()).toBe(true);
        });
        it('should not warn unsaved changes when the value changed is marked as non-editable.', function() {
            view.noEditFields = ['case_number'];
            view.model.setSyncedAttributes({
                case_number: 'test1'
            });
            view.model.set({
                case_number: 'test2'
            });

            expect(view.hasUnsavedChanges()).toBe(false);
        });
        it('should not warn unsaved changes when the value changed is a read-only field.', function() {
            view.meta.panels[1].fields[1].readonly = true;
            view.model.setSyncedAttributes({
                case_number: 'test1'
            });
            view.model.set({
                case_number: 'test2'
            });

            expect(view.hasUnsavedChanges()).toBe(false);
        });
    });

    describe('_getCellToEllipsify', function () {
        it('should return fullname cell if it is the first cell', function() {
            var actual,
                fullname = $('<div></div>').data('type', 'fullname'),
                text = $('<div></div>').data('type', 'text');

            actual = view._getCellToEllipsify($([fullname, text]));

            expect(actual.data('type')).toBe('fullname');
        });

        it('should return name cell if the first cell cannot be ellipsified', function() {
            var actual,
                html = $('<div></div>').data('type', 'html'),
                name = $('<div></div>').data('type', 'name');

            actual = view._getCellToEllipsify($([html, name]));

            expect(actual.data('type')).toBe('name');
        });
    });

    it('should not return my_favorite field when calling getFieldNames', function () {
        var fields = view.getFieldNames(null, true);
        expect(_.indexOf(fields, 'my_favorite')).toEqual(-1);
    });

    it('should return my_favorite field when calling getFieldNames', function () {
        view.meta.panels[0].fields.push({name: 'favorite', type: 'favorite'});
        var fields = view.getFieldNames(null, true);
        expect(_.indexOf(fields, 'my_favorite')).toBeGreaterThan(-1);
    });

    it('should set a data view on the context', function () {
        expect(view.context.get("dataView")).toBe("record");
    });


    describe("Warning delete", function() {
        var sinonSandbox, alertShowStub, routerStub;
        beforeEach(function() {
            sinonSandbox = sinon.sandbox.create();
            routerStub = sinonSandbox.stub(app.router, "navigate");
            sinonSandbox.stub(Backbone.history, "getFragment");
            alertShowStub = sinonSandbox.stub(app.alert, "show");
        });

        afterEach(function() {
            sinonSandbox.restore();
        });

        it("should not alert warning message if _modelToDelete is not defined", function() {
            app.routing.triggerBefore('route', {});
            expect(alertShowStub).not.toHaveBeenCalled();
        });
        it("should return true if _modelToDelete is not defined", function() {
            sinonSandbox.stub(view, 'warnDelete');
            expect(view.beforeRouteDelete()).toBeTruthy();
        });
        it("should return false if _modelToDelete is defined (to prevent routing to other views)", function() {
            sinonSandbox.stub(view, 'warnDelete');
            view._modelToDelete = new Backbone.Model();
            expect(view.beforeRouteDelete()).toBeFalsy();
        });
        it("should redirect the user to the targetUrl", function() {
            var unbindSpy = sinonSandbox.spy(view, 'unbindBeforeRouteDelete');
            view._modelToDelete = new Backbone.Model();
            view._currentUrl = 'Accounts';
            view._targetUrl = 'Contacts';
            view.deleteModel();
            expect(unbindSpy).toHaveBeenCalled();
            expect(routerStub).toHaveBeenCalled();
        });
    });

    describe("Check the First Panel", function() {
        var tempMeta;
        beforeEach(function() {
            tempMeta = view.meta;
            view.meta.panels = [];
        });
        afterEach(function() {
            view.meta = tempMeta;
        });

        it('should return true when calling checkFirstPanel with header', function() {
            view.meta.panels.push({header: 1});
            view.meta.panels.push({newTab: 1});

            expect(view.checkFirstPanel()).toBeTruthy();
        });

        it('should return true when calling checkFirstPanel with no header', function() {
            view.meta.panels.push({header: 0, newTab: 1});

            expect(view.checkFirstPanel()).toBeTruthy();
        });

        it('should return false when calling checkFirstPanel with header', function() {
            view.meta.panels.push({header: 1, newTab: 1});
            view.meta.panels.push({newTab: 0});

            expect(view.checkFirstPanel()).toBeFalsy();
        });

        it('should return false when calling checkFirstPanel with no header', function() {
            view.meta.panels.push({header: 0, newTab: 0});

            expect(view.checkFirstPanel()).toBeFalsy();
        });
    });

    describe('handle Field Errors', function() {
        var field;
        beforeEach(function() {
            field = SugarTest.createField('base', 'myField', 'base', 'edit');
        });

        afterEach(function() {
            field.dispose();
        });

        it('should expand the `show more` panel if there is an error with a field in that panel', function() {
            var triggerStub = sinon.collection.stub();
            sinon.collection.stub(view, '$')
                .withArgs('.more[data-moreless]').returns({'trigger' : triggerStub});
            sinon.collection.stub(field.$el, 'is')
                .withArgs(':hidden').returns(true);

            view.handleFieldError(field, true);

            expect(triggerStub).toHaveBeenCalledWith('click');
            expect(app.user.lastState.get(view.SHOW_MORE_KEY)).not.toEqual(view.$('.more[data-moreless]'));
        });
    });
});
