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
describe('Handlebars.helpers', function() {

    var app;
    var user = SUGAR.App.user;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.seedMetadata(true);
    });

    afterEach(function() {
        sinon.collection.restore();
    });

    // TODO: Create test for each helper

    describe("fieldValue", function() {

        it("should return value for an existing field", function() {
            var bean = new app.Bean({ foo: "bar"});
            expect(Handlebars.helpers.fieldValue(bean, "foo")).toEqual("bar");
        });

        it("should return empty string for a non-existing field", function() {
            var bean = new app.Bean();
            expect(Handlebars.helpers.fieldValue(bean, "foo", {hash: {}})).toEqual("");
        });

        it("should return default string for a non-existing field", function() {
            var bean = new app.Bean();
            expect(Handlebars.helpers.fieldValue(bean, "foo", {hash: {defaultValue: "bar"}})).toEqual("bar");
        });

    });

    describe("field", function() {
        it("should return a sugarfield span element", function() {
            var model = new app.Bean();
            var context = app.context.getContext({
                    module: 'Cases'
                });
            var view = new app.view.View({ name: "detail", context: context});
            var def = {name: "TestName", label: "TestLabel", type: "text"};

            var fieldId = app.view.getFieldId();
            var result = Handlebars.helpers.field.call(def, view, {
                hash : {
                    model: model
                }
            });
            expect(result.toString()).toMatch(/<span sfuuid=.*(\d+).*/);
            expect(app.view.getFieldId()).toEqual(fieldId + 1);
            expect(view.fields[fieldId + 1]).toBeDefined();
        });

        it("should customize the view type", function() {
            var model = new app.Bean();
            var context = app.context.getContext({
                    module: 'Cases'
                });
            var view = new app.view.View({ name: "detail", context: context});
            var def = {name: "TestName", label: "TestLabel", type: "text"};
            var viewType = 'custom_view_name';

            var fieldId = app.view.getFieldId();
            var result = Handlebars.helpers.field.call(def, view, {
                hash: {
                    model: model,
                    template: viewType
                }
            });
            expect(app.view.getFieldId()).toEqual(fieldId + 1);
            expect(view.fields[fieldId + 1].options.viewName).toEqual(viewType);
        });

        it('should add the child field to the parent\'s field list', function() {

            var model = new Backbone.Model({id:23456});
            var context = app.context.getContext();

            var def = {name: 'ParentName', label: 'TestParent', type: 'base'};

            var view = SugarTest.createComponent('View', {
                type: "detail",
                context: context
            });

            var field = SugarTest.createComponent('Field', {
                def: def,
                view: view,
                context: context,
                model: model
            });

            field = _.extend(field, {
                fields: []
            });

            var result = Handlebars.helpers.field.call(def, view, {
                hash: {
                    model: model,
                    template: 'custom_view_name',
                    parent: field
                }
            });

            expect(field.fields.length).toBe(1);
        });
    });

    describe("buildRoute", function() {
        var routerMock, model, context, module;

        beforeEach(function() {
            app.router = app.router || {};
            app.router.buildRoute = app.router.buildRoute || function() {};
            routerMock = sinon.mock(app.router);

            model = new app.Bean();
            model.set("id", "123");
            module = "Cases";
            context = app.context.getContext({
                module: module
            });
        });

        afterEach(function() {
            routerMock.restore();
        });

        it("should call app.router.buildRoute with the appropriate inputs for create route", function() {
            var action = "create",
                expectedId = model.id;

            routerMock.expects('buildRoute').once().withArgs(module, expectedId, action);
            Handlebars.helpers.buildRoute({hash: {context: context, model: model, action: action}});
            expect(routerMock.verify()).toBeTruthy();
        });

        it("should call app.router.buildRoute with the appropriate inputs for non-create route", function() {
            var action = "",
                expectedId = model.id;

            routerMock.expects('buildRoute').once().withArgs(module, expectedId, action);
            Handlebars.helpers.buildRoute({hash: {context: context, model: model, action: action}});
            expect(routerMock.verify()).toBeTruthy();
        });
    });

    describe("has", function() {
        it("should return the true value if the first value is found in the second value (array)", function() {
            var val1 = "hello",
                val2 = ["world", "fizz", "hello", "buzz"],
                returnTrue = "Success!",
                returnFalse = "Failure!",
                options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.has(val1, val2, options)).toEqual(returnTrue);
        });

        it("should return the true value if the first value is not found in the second value (array)", function() {
            var val1 = "good bye",
                val2 = ["world", "fizz", "hello", "buzz"],
                returnTrue = "Success!",
                returnFalse = "Failure!",
                options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.notHas(val1, val2, options)).toEqual(returnTrue);
        });

        it("should return the false value if the first value is found in the second value (array)", function() {
            var val1 = "hello",
                val2 = ["world", "fizz", "sidecar", "buzz"],
                returnTrue = "Success!",
                returnFalse = "Failure!",
                options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.has(val1, val2, options)).toEqual(returnFalse);
        });

        it("should return the true value if the first value is found in the second value (scalar)", function() {
            var val1 = "hello",
                val2 = "hello",
                returnTrue = "Success!",
                returnFalse = "Failure!",
                options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.has(val1, val2, options)).toEqual(returnTrue);
        });
    });

    describe("eachOptions", function() {
        it("should pull options hash from app list strings and return an iterated block string", function() {
            var optionName = "custom_fields_importable_dom",
                blockHtml = "<li>{{this.key}} {{this.value}}</li>",
                template = Handlebars.compile(blockHtml);

            app.metadata.set(fixtures.metadata);
            expect(Handlebars.helpers.eachOptions(optionName, {fn: template})).toEqual("<li>true Yes</li><li>false No</li><li>required Required</li>");
        });

        it("should pull options array from app list strings and return an iterated block string", function() {
            var optionName = "custom_fields_merge_dup_dom",
                blockHtml = "<li>{{value}}</li>",
                template;

            template = Handlebars.compile(blockHtml);

            expect(Handlebars.helpers.eachOptions(optionName, {fn: template})).toEqual("<li>Disabled</li><li>Enabled</li><li>In Filter</li><li>Default Selected Filter</li><li>Filter Only</li>");
        });

        it("should return an iterated block string for an object", function() {
            var options = {"Disabled": 0, "Enabled": 1},
                blockHtml = "<li>{{this.key}} {{this.value}}</li>",
                template;

            template = Handlebars.compile(blockHtml);

            expect(Handlebars.helpers.eachOptions(options, {fn: template})).toEqual("<li>Disabled 0</li><li>Enabled 1</li>");
        });

        it("should return an iterated block string for an array", function() {
            var options = ["Disabled", "Enabled"],
                blockHtml = "<li>{{value}}</li>",
                template;

            template = Handlebars.compile(blockHtml);

            expect(Handlebars.helpers.eachOptions(options, {fn: template})).toEqual("<li>Disabled</li><li>Enabled</li>");
        });

    });

    describe("eq", function() {
        it("should return the true value if conditional evaluates true", function() {
            var val1 = 1,
                val2 = 1,
                returnTrue = "Success!",
                returnFalse = "Failure!",
                options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.eq(val1, val2, options)).toEqual(returnTrue);
        });

        it("should return the false value if conditional evaluates false", function() {
            var val1 = 1,
                val2 = 2,
                returnTrue = "Success!",
                returnFalse = "Failure!",
                options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.eq(val1, val2, options)).toEqual(returnFalse);
        });
    });

    describe("notEq", function() {
        it("should return the false value if conditional evaluates true", function() {
            var val1 = 1,
                val2 = 1,
                returnTrue = "Success!",
                returnFalse = "Failure!",
                options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.notEq(val1, val2, options)).toEqual(returnFalse);
        });

        it("should return the true value if conditional evaluates false", function() {
            var val1 = 1,
                val2 = 2,
                returnTrue = "Success!",
                returnFalse = "Failure!",
                options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.notEq(val1, val2, options)).toEqual(returnTrue);
        });
    });

    describe("notMatch", function() {
        it("should return inverse of regex evaluation", function() {
            var val1 = "foo-is-not-greedy",
                nonGreedy = "^foo$", 
                greedy = "foo", 
                returnTrue = "Success!",
                returnFalse = "Failure!",
                options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.notMatch(val1, nonGreedy, options)).toEqual(returnTrue);
            expect(Handlebars.helpers.notMatch(val1, greedy, options)).toEqual(returnFalse);
        });
    });
    
    describe("match", function() {
        it("should return result of regex evaluation", function() {
            var val1 = "foo-is-not-greedy",
                nonGreedy = "^foo$", 
                greedy = "foo", 
                returnTrue = "Success!",
                returnFalse = "Failure!",
                options = {};

            options.fn = function() { return returnTrue; };
            options.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.match(val1, nonGreedy, options)).toEqual(returnFalse);
            expect(Handlebars.helpers.match(val1, greedy, options)).toEqual(returnTrue);
        });
    });

    describe("isSortable", function() {
        it("should return block if isSortable is true in field viewdef", function() {
            var returnVal = 'Yup',
                block = function() {return returnVal; },
                module = "Cases", 
                fieldViewdef = { 
                    name: 'text',
                    sortable: true
                },
                getModuleStub = sinon.stub(app.metadata, 'getModule', function() { 
                    return {
                        fields: {
                            text: { 
                                sortable:false
                            }
                        }
                    };
                });
            expect(Handlebars.helpers.isSortable(module, fieldViewdef, { fn: block })).toEqual(returnVal);
            getModuleStub.restore();
        });

        it("should not return block if isSortable is false in field viewdef but true in vardef", function() {
            var returnVal = 'Yup',
                block = function() {return returnVal; },
                module = "Cases", 

                fieldViewdef = { 
                    name: 'text',
                    sortable: false
                },
                getModuleStub = sinon.stub(app.metadata, 'getModule', function() { 
                    return {
                        fields: {
                            text: { 
                                sortable: true
                            }
                        }
                    };
                });
            expect(Handlebars.helpers.isSortable(module, fieldViewdef, { fn: block })).not.toEqual(returnVal);
            getModuleStub.restore();
        });
        it("should return block if isSortable not defined in either field viewdef or vardef", function() {
            var returnVal = 'Yup',
                block = function() {return returnVal; },
                module = "Cases", 
                fieldViewdef = { 
                    name: 'text'
                },
                getModuleStub = sinon.stub(app.metadata, 'getModule', function() { 
                    return {
                        fields: {
                            text: {} 
                        }
                    };
                });
            expect(Handlebars.helpers.isSortable(module, fieldViewdef, { fn: block })).toEqual(returnVal);
            getModuleStub.restore();
        });
    });
    
    describe("str", function() {
        it("should get a string from language bundle", function() {
            var lang = SugarTest.app.lang;
            app.metadata.set(fixtures.metadata);
            expect(Handlebars.helpers.str("LBL_ASSIGNED_TO_NAME", "Contacts")).toEqual("Assigned to");
        });
    });

    describe("nl2br", function() {
        it("should convert newlines to breaks", function() {
            expect(Handlebars.helpers.nl2br("foo\nbar\r\nbaz\nbang")).toEqual(new Handlebars.SafeString("foo<br>bar<br>baz<br>bang"));
            expect(Handlebars.helpers.nl2br("\nbar\n\rbaz\r")).toEqual(new Handlebars.SafeString("<br>bar<br>baz<br>"));
        });
        it("should escape html entities", function() {
            expect(Handlebars.helpers.nl2br("Paste &copy;")).toEqual(new Handlebars.SafeString("Paste &amp;copy;"));
        });
        it("should convert newlines to breaks", function() {
            expect(Handlebars.helpers.nl2br("\nbar\r\nbaz\n")).toEqual(new Handlebars.SafeString("<br>bar<br>baz<br>"));
        });
        it("should accept input without newlines", function() {
            expect(Handlebars.helpers.nl2br("foo")).toEqual(new Handlebars.SafeString("foo"));
            expect(Handlebars.helpers.nl2br("")).toEqual(new Handlebars.SafeString(""));
            expect(Handlebars.helpers.nl2br("\\n")).toEqual(new Handlebars.SafeString("\\n"));
            expect(Handlebars.helpers.nl2br("\\r\\n")).toEqual(new Handlebars.SafeString("\\r\\n"));
        });
        it("should gracefully handle non-string values", function(){
            expect(Handlebars.helpers.nl2br(undefined)).toEqual(new Handlebars.SafeString(""));
            expect(Handlebars.helpers.nl2br({not: "a string"})).toEqual(new Handlebars.SafeString("[object Object]"));
            expect(Handlebars.helpers.nl2br(3)).toEqual(new Handlebars.SafeString("3"));
        });
        it("should not allow HTML to be injected", function(){
            expect(Handlebars.helpers.nl2br("<b>Boldly</b>")).toEqual(new Handlebars.SafeString("&lt;b&gt;Boldly&lt;/b&gt;"));
            expect(Handlebars.helpers.nl2br("<script type='text/javascript'></script>")).toEqual(new Handlebars.SafeString("&lt;script type=&#x27;text/javascript&#x27;&gt;&lt;/script&gt;"));
        })
    });

    describe("formatCurrency", function() {
        it("should format the value to a currency format", function() {
            user.set('decimal_precision',2);
            user.set('decimal_separator','.');
            user.set('number_grouping_separator',',');
            var amount = 1999.99,
                currencyId = "-99";
            expect(Handlebars.helpers.formatCurrency(amount, currencyId)).toEqual("$1,999.99");
        });
    });

    describe('formatDate', function() {
        it('should format the value to users date and time format', function() {
            var date = '2012-03-27 01:48:32';

            user.setPreference('datepref', 'Y-m-d');
            user.setPreference('timepref', 'h:i a');

            expect(Handlebars.helpers.formatDate(date, {hash: {dateOnly: false}})).toEqual('2012-03-27 01:48 am');
            expect(Handlebars.helpers.formatDate(date, {hash: {dateOnly: true}})).toEqual('2012-03-27');
        });
    });

    describe("firstChars", function() {
        it("should return the first n chars of a string", function() {
            var str = "longstring",
                length = 3;

            expect(Handlebars.helpers.firstChars(str, length)).toEqual("lon");
        });
    });

    describe('getModuleName', function() {
        it('should call app.lang.get with the module and options', function() {
            var getModuleNameStub = sinon.collection.stub(app.lang, 'getModuleName'),
                hbsOptions = {
                    hash: {
                        defaultValue: 'test',
                        plural: true
                    }
                },
                langOptions = {
                    defaultValue: hbsOptions.hash.defaultValue,
                    plural: hbsOptions.hash.plural
                };

            Handlebars.helpers.getModuleName('Cases', hbsOptions);
            expect(getModuleNameStub).toHaveBeenCalledWith('Cases', langOptions);
        });
    });

    describe("partial", function() {
        var options;
        beforeEach(function() {
            SugarTest.seedMetadata(true);
            options = {hash: {}};
        });

        afterEach(function() {
            sinon.collection.restore();
        });

        describe("a layout template", function() {
            var layout;
            beforeEach(function() {
                layout = SugarTest.createComponent("Layout", {
                    type : "detail",
                    module: "Contacts"
                });
            });

            afterEach(function() {
                layout.dispose();
            });

            it("should return a partial template" ,function() {
                var getLayoutStub = sinon.collection.stub(app.template,'getLayout', function(){
                    return function(){return "Layout"};
                })
                var renderedTemplate = Handlebars.helpers.partial.call(layout, 'test', layout, {}, options);
                expect(renderedTemplate.toString()).toEqual('Layout');
                expect(getLayoutStub).toHaveBeenCalledWith('detail.test', 'Contacts');
            });
            it("should return a partial template with a different supplied module" ,function() {
                var getLayoutStub = sinon.collection.stub(app.template,'getLayout', function(){
                    return function(){return 'Layout'};
                })
                options.hash.module = 'Accounts';

                //getViewStub.calledWith("detail.test");
                var renderedTemplate = Handlebars.helpers.partial.call(layout, 'test', layout, {}, options);
                expect(renderedTemplate.toString()).toEqual('Layout');
                expect(getLayoutStub).toHaveBeenCalledWith("detail.test", 'Accounts');
            });
            it("should return a partial template with different supplied data" ,function() {
                var getLayoutStub = sinon.collection.stub(app.template,'getLayout', function(){
                    return function(data){return 'Layout' + data.value};
                })
                var renderedTemplate = Handlebars.helpers.partial.call(layout, 'test', layout, {value: 'Data'}, options);
                expect(renderedTemplate.toString()).toEqual('LayoutData');
                expect(getLayoutStub).toHaveBeenCalledWith('detail.test', 'Contacts');
            });
        });
        describe("a view template", function() {
            var view;
            beforeEach(function(){
                view = SugarTest.createComponent('View', {
                    type: 'detail',
                    module: 'Contacts'
                });
            });

            afterEach(function() {
                view.dispose();
                view = null;
            });

            it("should return a partial template" ,function() {
                var getViewStub = sinon.collection.stub(app.template,'getView', function(){
                    return function(){return "View"};
                })
                var renderedTemplate = Handlebars.helpers.partial.call(view, 'test', view, {}, options);
                expect(renderedTemplate.toString()).toEqual('View');
                expect(getViewStub).toHaveBeenCalledWith('detail.test', 'Contacts');
            });
            it("should return a partial template with a different supplied module" ,function() {
                var getViewStub = sinon.collection.stub(app.template,'getView', function(){
                    return function(){return 'View'};
                })
                options.hash.module = 'Accounts';

                //getViewStub.calledWith("detail.test");
                var renderedTemplate = Handlebars.helpers.partial.call(view, 'test', view, {}, options);
                expect(renderedTemplate.toString()).toEqual('View');
                expect(getViewStub).toHaveBeenCalledWith("detail.test", 'Accounts');
            });
            it("should return a partial template with different supplied data" ,function() {
                var getViewStub = sinon.collection.stub(app.template,'getView', function(){
                    return function(data){return 'View' + data.value};
                })
                var renderedTemplate = Handlebars.helpers.partial.call(view, 'test', view, {value: 'Data'}, options);
                expect(renderedTemplate.toString()).toEqual('ViewData');
                expect(getViewStub).toHaveBeenCalledWith('detail.test', 'Contacts');
            });

            it('should load the partial from where it loaded the original template in the case of no override', function() {
                view.tplName = 'notDetail';
                sinon.collection.stub(app.template, 'getView')
                    .withArgs('notDetail.test').returns(function() {
                        return 'tplName';
                    });

                var renderedTemplate = Handlebars.helpers.partial.call(view, 'test', view, {}, options);
                expect(renderedTemplate.toString()).toEqual('tplName');
            });

            it('should use an overriden template corresponding to its own name if it exists', function() {
                view.tplName = 'notDetail';
                sinon.collection.stub(app.template, 'getView')
                    .withArgs('notDetail.test').returns(function() {
                        return 'tplName';
                    })
                    .withArgs('detail.test').returns(function() {
                        return 'overriden';
                    });

                var renderedTemplate = Handlebars.helpers.partial.call(view, 'test', view, {}, options);
                expect(renderedTemplate.toString()).toEqual('overriden');
            });
        });
        describe("a field template", function() {
            var view, field, context;
            beforeEach(function(){
                context = app.context.getContext();
                context.set('module', 'Contacts')
                view = SugarTest.createComponent('View', {
                    type: 'detail',
                    module: 'Contacts'
                });
                field = SugarTest.createComponent("Field", {
                    def: {
                        type: 'base',
                        name: 'testfield',
                        label: 'testfield'
                    },
                    context: context,
                    view: view
                });
            });

            afterEach(function() {
                field.dispose();
            });

            it("should return a partial template" ,function() {
                var getFieldStub = sinon.collection.stub(app.template,'getField', function(){
                    return function(){return 'Field'};
                })
                var renderedTemplate = Handlebars.helpers.partial.call(field, 'test', field, {}, options);
                expect(renderedTemplate.toString()).toEqual('Field');
                expect(getFieldStub).toHaveBeenCalledWith('base', 'test', 'Contacts');
            });
            it("should return a partial template with a different supplied module" ,function() {
                var getFieldStub = sinon.collection.stub(app.template,'getField', function(){
                    return function(){return 'Field'};
                })

                options.hash.module = 'Accounts';

                var renderedTemplate = Handlebars.helpers.partial.call(field, 'test', field, {}, options);
                expect(renderedTemplate.toString()).toEqual('Field');
                expect(getFieldStub).toHaveBeenCalledWith('base', 'test', 'Accounts');
            });
            it("should return a partial template with different supplied data" ,function() {
                var getFieldStub = sinon.collection.stub(app.template,'getField', function(){
                    return function(data){return 'Field' + data.value};
                })
                var renderedTemplate = Handlebars.helpers.partial.call(field, 'test', field, {value: 'Data'}, options);
                expect(renderedTemplate.toString()).toEqual('FieldData');
                expect(getFieldStub).toHaveBeenCalledWith('base', 'test', 'Contacts');
            });
        });
        describe("generic partial support", function() {
            var view;
            beforeEach(function(){
                view = SugarTest.createComponent('View', {
                    type: 'detail',
                    module: 'Contacts'
                });
            });

            afterEach(function() {
                view.dispose();
            });

            it("should return a compiled partial template defined in the component options" ,function() {
                var getLayoutStub = sinon.collection.spy(app.template,'getLayout');
                var getViewStub = sinon.collection.spy(app.template,'getView');
                var getFieldStub = sinon.collection.spy(app.template,'getField');
                view.setTemplateOption('partials', {'test': function() {return 'View'}});
                var renderedTemplate = Handlebars.helpers.partial.call(view, 'test', view, {}, options);
                expect(renderedTemplate.toString()).toEqual('View');
                expect(getLayoutStub).not.toHaveBeenCalled();
                expect(getViewStub).not.toHaveBeenCalled();
                expect(getFieldStub).not.toHaveBeenCalled();
            });

            it('should work if the parameter `context` is missing', function() {
                sinon.collection.stub(app.template, 'getView').returns(function(data) {
                    expect(data.templateComponent).toEqual(view);
                });

                Handlebars.helpers.partial.call(view, 'test', view, null, options);
            });


            it('should keep the original component as `templateComponent` in the `context`', function() {
                sinon.collection.stub(app.template, 'getView').returns(function(data) {
                    expect(data.templateComponent).toEqual(view);
                });

                Handlebars.helpers.partial.call(view, 'test', view, {}, options);
            });


            it('should merge extra parameters in `options.hash` into the `context`', function() {
                sinon.collection.stub(app.template, 'getView').returns(function(data) {
                    expect(data.catName).toEqual('Meow');
                });

                options.hash.catName = 'Meow';

                Handlebars.helpers.partial.call(view, 'test', view, {}, options);
            });
        });
    });
});
