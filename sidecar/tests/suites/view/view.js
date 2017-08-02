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
describe("View.View", function() {
    var app, bean, collection, context, views;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;

        bean = app.data.createBean("Contacts", {
            first_name: "Foo",
            last_name: "Bar"
        });
        bean.fields = fixtures.metadata.modules.Contacts.fields;
        collection = new app.BeanCollection([bean]);
        context = app.context.getContext({
            url: "someurl",
            module: "Contacts",
            model: bean,
            collection: collection
        });
    });

    afterEach(function() {
        sinon.collection.restore();
    });

    it('should respect the dataView property from child views', function () {

        let CustomView = app.view.View.extend({
            dataView: 'sidecar-testing',
            getFieldNames() {
                return ['field1', 'field2'];
            },
        });

        expect(context.get('dataView')).toBeUndefined();
        expect(context.get('fields')).toBeUndefined();

        new CustomView({
            context: context,
        });

        expect(context.get('dataView')).toBe('sidecar-testing');
        expect(context.get('fields')).toBeUndefined();
    });

    it('should add fields to context from child views', function () {

        let CustomView = app.view.View.extend({
            getFieldNames() {
                return ['field1', 'field2'];
            },
        });

        expect(context.get('dataView')).toBeUndefined();
        expect(context.get('fields')).toBeUndefined();

        new CustomView({
            context: context,
        });

        expect(context.get('dataView')).toBeUndefined();
        expect(context.get('fields')).toEqual(['field1', 'field2']);
    });

    it('should render edit views', function() {
        var aclSpy = sinon.spy(app.acl,'hasAccess'), html,
            view = SugarTest.createComponent("View", {
                context: context,
                type: "edit"
            });

        view.render();
        html = view.$el.html();
        expect(html).toContain('edit');

        expect(view.$el.find('input[value="Foo"]').length).toEqual(1);
        expect(aclSpy).toHaveBeenCalled();
        aclSpy.restore();
    });

    it('should re-render views when fire app:locale:change fires', function() {
        var spy;
        SugarTest.createComponent('View', {
            context: context,
            type: 'custom'
        });
        spy = sinon.spy(app.view.View.prototype, '_setLabels');
        app.events.trigger('app:locale:change');

        expect(spy).toHaveBeenCalled();

        spy.restore();
    });

    it('should render detail views', function() {
        var view = SugarTest.createComponent("View", {
                context: context,
                type: "detail"
            }), html;
        view.render();
        expect(view.moduleSingular).toEqual("Kontact");
        expect(view.modulePlural).toEqual("Kontacts");
        html = view.$el.html();
        expect(html).toContain('detail');
    });

    it('should render with custom context for its template', function() {
        app.view.views.CustomView = app.view.View.extend({
            _renderHtml: function() {
                app.view.View.prototype._renderHtml.call(this, { prop: "kommunizma"});
            }
        });
        var view = SugarTest.createComponent("View", {
                context: context,
                type: "custom"
            }), html;

        view.template = Handlebars.compile("K pobede {{prop}}!");
        view.render();
        html = view.$el.html();
        expect(html).toContain('K pobede kommunizma!');
    });

   it('should return its fields, related fields and dispose them when re-rendering', function(){
        var view = SugarTest.createComponent("View", {
                context: context,
                type: "detail"
            }),
            fields = [ 'first_name', 'last_name', 'phone_work', 'phone_home', 'email1', 'account_name', 'parent_name', 'date_modified' ],
            mock = sinon.mock(app.view.Field.prototype);

        mock.expects("dispose").exactly(11);

        //getFieldName should returns its related fields
        expect(view.getFieldNames()).toEqual([ 'first_name', 'last_name', 'phone_work', 'phone_home', 'email1', 'account_name', 'parent_name', 'date_modified', 'modified_by_name', 'account_id', 'parent_id', 'parent_type']);

        expect(_.isEmpty(view.getFields())).toBeTruthy();
        expect(_.isEmpty(view.fields)).toBeTruthy();

        view.render();

        expect(_.keys(view.fields).length).toEqual(11);
        expect(_.pluck(view.getFields(), "name")).toEqual(fields);

        // Make sure the number of fields is still the same
        view.render();

        expect(_.keys(view.fields).length).toEqual(11);
        expect(_.pluck(view.getFields(), "name")).toEqual(fields);
        mock.verify();
    });

    it('should only load data when the user has read access for the View\'s module', function(){
        var aclSpy = sinon.spy(app.acl,'hasAccess'),
            // Function that makes API call to load Fields defined in View
            loadStub = sinon.stub(context,'loadData', function(){
                return;
            }),
            view = SugarTest.createComponent("View", {
                context: context,
                type: "details",
                module: "Bugs"
            });

        view.loadData();
        expect(aclSpy).toHaveBeenCalledWith('read','Bugs');
        expect(loadStub).toHaveBeenCalled();
        aclSpy.restore();
        loadStub.reset();

        var hasAccessStub = sinon.stub(app.acl,"hasAccess",function(access, module) {
            return false;
        });
        view.loadData();
        expect(aclSpy).toHaveBeenCalledWith('read','Bugs');
        expect(loadStub).not.toHaveBeenCalled();
        hasAccessStub.restore();
        loadStub.restore();
    });

    it('should occur an error dialog only if the primary view has not rendered due to the acl failure', function(){
        var hasAccessStub = sinon.stub(app.acl,"hasAccessToModel",function(action, model) {
                return false;
            }),
            view = SugarTest.createComponent("View", {
                context: context,
                type: "details",
                module: "Bugs",
                primary: true
            }),
            errorSpy = sinon.spy(app.error,'handleRenderError');

        view.render();
        expect(errorSpy).toHaveBeenCalled();
        errorSpy.restore();

        errorSpy = sinon.spy(app.error,'handleRenderError');
        view.primary = false;
        expect(errorSpy).not.toHaveBeenCalled();

        hasAccessStub.restore();
        errorSpy.restore();
    });

   describe('loading the template', function() {
        using('different cases where templates are defined or not', [
            // We get the template defined in the view's module first.
            {
                module: 'Accounts',
                moduleTpl: true,
                expectedTpl: 'moduleTpl'
            },
            {
                module: 'Accounts',
                loadModule: 'Contacts',
                moduleTpl: true,
                loadModuleTpl: false,
                expectedTpl: 'moduleTpl'
            },
            {
                module: 'Accounts',
                loadModule: 'Contacts',
                moduleTpl: true,
                loadModuleTpl: true,
                expectedTpl: 'moduleTpl'
            },
            // If the template is not defined in the
            // view's module and `loadModule` is not passed, we fallback to the
            // template in base.
            {
                module: 'Accounts',
                moduleTpl: false,
                expectedTpl: 'baseTpl'
            },
            // If the template is not defined in the view's
            // module, we fallback to the one defined in `loadModule` module.
            {
                module: 'Accounts',
                loadModule: 'Contacts',
                moduleTpl: false,
                loadModuleTpl: true,
                expectedTpl: 'loadModuleTpl'
            },
            // If the template in `loadModule` module
            // is undefined, we do NOT fallback to the one defined in base.
            {
                module: 'Accounts',
                loadModule: 'Contacts',
                moduleTpl: false,
                loadModuleTpl: false,
                expectedTpl: 'emptyTpl'
            },
        ], function(data) {
            it('should load the template from the correct module', function() {
                var viewName = 'testView';
                var templates = {
                    moduleTpl: '<div>' + data.module + '</div>',
                    loadModuleTpl: '<div>' + data.loadModule + '</div>',
                    baseTpl: '<div>base</div>',
                    emptyTpl: app.template.empty()
                };

                var getViewStub = sinon.collection.stub(app.template, 'getView');
                if (data.loadModuleTpl) {
                    getViewStub.withArgs(viewName, data.loadModule).returns(
                        function() {
                            return templates.loadModuleTpl;
                        }
                    );
                }

                if (data.moduleTpl) {
                    getViewStub.withArgs(viewName, data.module).returns(
                        function() {
                            return templates.moduleTpl;
                        }
                    );
                }

                getViewStub.withArgs(viewName, void 0).returns(
                    function() {
                        return templates.baseTpl;
                    }
                );

                var view = app.view.createView({type: viewName, module: data.module, loadModule: data.loadModule});

                expect(view.template()).toEqual(templates[data.expectedTpl]);
            });
        });
    });
});
