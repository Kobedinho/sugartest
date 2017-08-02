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
describe("enum field", function() {
    var app, field, stub_appListStrings,
        module = 'Contacts',
        fieldName = 'test_enum',
        model;

    beforeEach(function() {
        Handlebars.templates = {};
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'list');
        SugarTest.testMetadata.set();
        SugarTest.testMetadata._addDefinition(fieldName, 'fields', {
        }, module);

        SugarTest.app.data.declareModels();
        model = app.data.createBean(module);

        stub_appListStrings = sinon.stub(app.lang, 'getAppListStrings', function() {
            return {"":"","Defect":"DefectValue","Feature":"FeatureValue"};
        });

        if (!$.fn.select2) {
            $.fn.select2 = function(options) {
                var obj = {
                    on : function() {
                        return obj;
                    }
                };
                return obj;
            };
        }
    });

    afterEach(function() {
        sinon.collection.restore();
        if (field) {
            field.dispose();
        }
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
        stub_appListStrings.restore();
        SugarTest.testMetadata.dispose();
    });

    it("should format a labeled select and have option selected on edit template", function() {
        field = SugarTest.createField("base", fieldName, "enum", "edit", {options: "bugs_type_dom"});
        var original = 'Defect',
            expected = 'DefectValue';
        field.model.set(fieldName, original);
        field.render();
        var actual = field.$('input').select2('data');
        expect(actual.id).toEqual(original);
        expect(actual.text).toEqual(expected);
    });

    it("should format a labeled string for detail template", function() {
        field = SugarTest.createField("base", fieldName, "enum", "detail", {options: "bugs_type_dom"});
        var original = 'Defect',
            expected = 'DefectValue';
        field.model.set(fieldName, original);
        field.render();
        var actual = field.$el.text().replace(/(\r\n|\n|\r)/gm,"");
        expect($.trim(actual)).toEqual(expected);
    });

    it("should call loadEnumOptions and set items during render", function() {
        var field = SugarTest.createField("base", fieldName, "enum", "edit", {options: "bugs_type_dom"});
        var loadEnumSpy = sinon.spy(field, "loadEnumOptions");
        field.render();
        expect(loadEnumSpy.called).toBe(true);
        expect(field.items).toEqual(app.lang.getAppListStrings());
        loadEnumSpy.restore();
    });

    it("should default the value of the field to the first option if undefined", function() {
        var field = SugarTest.createField('base', fieldName, 'enum', 'edit', {options: "bugs_type_dom"}, module, model);
        field.items = {'first': 'first', 'second': 'second'};
        var loadEnumSpy = sinon.spy(field, "loadEnumOptions");
        field.render();
        loadEnumSpy.restore();
        expect(field.model.get(field.name)).toEqual('first');
    });

    it("should not default the value of the field to the first option if multi select", function() {
        var field = SugarTest.createField("base", fieldName, "enum", "edit", {isMultiSelect: true, options: "bugs_type_dom"});
        field.items = {'first': 'first', 'second': 'second'};
        var loadEnumSpy = sinon.spy(field, "loadEnumOptions");
        field.render();
        loadEnumSpy.restore();
        expect(field.model.get(field.name)).toBeUndefined();
    });

    describe('enum API', function() {
        it('should load options from enum API if options is undefined or null', function() {
            var callStub = sinon.stub(app.api, 'enumOptions', function(module, field, callbacks) {
                expect(field).toEqual('test_enum');
                //Call success callback
                callbacks.success(app.lang.getAppListStrings());
            });
            field = SugarTest.createField('base', fieldName, 'enum', 'detail', {/* no options */});
            var renderSpy = sinon.spy(field, '_render');
            field.render();

            expect(callStub).toHaveBeenCalled();
            expect(renderSpy.calledTwice).toBe(true);
            expect(field.items).toEqual(app.lang.getAppListStrings());

            var field2 = SugarTest.createField('base', fieldName, 'enum', 'detail', {options: null}),
                renderSpy2 = sinon.spy(field2, '_render');
            field2.render();

            expect(callStub.calledTwice).toBe(true);
            expect(renderSpy2.calledTwice).toBe(true);
            expect(field2.items).toEqual(app.lang.getAppListStrings());

            callStub.restore();
            renderSpy.restore();
            renderSpy2.restore();
            field2.dispose();
        });
        it('should avoid duplicate enum api call', function() {
            var apiSpy = sinon.spy(app.api, 'enumOptions');
            var field = SugarTest.createField('base', fieldName, 'enum', 'detail', {}, module, model);
            var field2 = SugarTest.createField('base', fieldName, 'enum', 'detail', {}, module, model, field.context);
            var expected = {
                    aaa: 'bbb',
                    fake1: 'fvalue1',
                    fake2: 'fvalue2'
                };
            sinon.stub(field.model, 'setDefault');
            //setup fake REST end-point for enum
            SugarTest.seedFakeServer();
            SugarTest.server.respondWith('GET', /.*rest\/v10\/Contacts\/enum\/test_enum.*/,
                [200, { 'Content-Type': 'application/json'}, JSON.stringify(expected)]);
            field.render();
            SugarTest.server.respond();
            field2.render();
            field.render();

            expect(apiSpy.calledOnce).toBe(true);
            //second field should be ignored, once first ajax called is being called
            expect(apiSpy.calledTwice).toBe(false);
            _.each(expected, function(value, key) {
                expect(field.items[key]).toBe(value);
                expect(field2.items[key]).toBe(value);
            });
            apiSpy.restore();
            field.dispose();
            field2.dispose();
        });
    });

    describe("getSelect2Options", function() {
        it("should allow separator to be configured via metadata", function(){
            field = SugarTest.createField("base", fieldName, "enum", "detail", {isMultiSelect: true, separator: '|', options: "bugs_type_dom"});
            var select2opts = field.getSelect2Options([]);
            expect(select2opts.separator).toEqual('|');
            expect(select2opts.multiple).toBe(true);
        });
        it("should allow multiselect to be configured via metadata", function(){
            field = SugarTest.createField("base", fieldName, "enum", "detail", {isMultiSelect: true, options: "bugs_type_dom"});
            var select2opts = field.getSelect2Options([]);
            expect(select2opts.multiple).toBe(true);
        });
    });

    describe("multi select enum", function() {

        it("should display a labeled comma list for detail template", function() {
            field = SugarTest.createField("base", fieldName, "enum", "detail", {isMultiSelect: true, options: "bugs_type_dom"});
            var original = ["Defect", "Feature"],
                expected = 'DefectValue, FeatureValue';
            field.model.set(fieldName, original);
            field.render();
            var actual = field.$el.text().replace(/(\r\n|\n|\r)/gm,"");
            expect($.trim(actual)).toEqual(expected);
        });

        it("should display a labeled comma list for list template", function() {
            field = SugarTest.createField("base", fieldName, "enum", "list", {isMultiSelect: true, options: "bugs_type_dom"});
            var original = ["Defect", "Feature"],
                expected = 'DefectValue, FeatureValue';
            field.model.set(fieldName, original);
            field.render();
            var actual = field.$el.text().replace(/(\r\n|\n|\r)/gm,"");
            expect($.trim(actual)).toEqual(expected);
        });

        it("should format server's default value into a string array", function(){
            field = SugarTest.createField("base", fieldName, "enum", "list", {isMultiSelect: true, options: "bugs_type_dom"});
            var original = "^Weird^";
            var expected = ["Weird"];
            var original2 = "^Very^,^Weird^";
            var expected2 = ["Very", "Weird"];
            expect(field.format(original)).toEqual(expected);
            expect(field.format(original2)).toEqual(expected2);
        });

        it("should unformat nulls into server equivalent format of array with empty string", function(){
            // Backbone.js won't sync null values so server doesn't pick up on change and clear multi-select field
            field = SugarTest.createField("base", fieldName, "enum", "list", {isMultiSelect: true, options: "bugs_type_dom"});
            var original = null;
            var expected = [];
            expect(field.unformat(original)).toEqual(expected);
        });

        it("should format the model's value into a string array when model is updated", function() {
            var value = "^1^,^2^",
                actual,
                expected = ["1", "2"];
            field = SugarTest.createField("base", fieldName, "enum", "edit", {isMultiSelect: true});
            field.items = {
                '1': 'Foo',
                '2': 'Bar',
                '3': 'Baz'
            };
            field.render();
            field.model.set(field.name, value);
            actual = field.$('input').select2('val');
            expect(actual).toEqual(expected);
        });

        describe("blank value on multi select", function() {
            it('should transform the empty key on render', function() {
                field = SugarTest.createField("base", fieldName, "enum", "list", {isMultiSelect: true, options: "bugs_type_dom"});
                field.render();
                expect(field.items['']).toBeUndefined();
            });
            it('should prevent focus otherwise the dropdown is opened and it\'s impossible to remove an item', function() {
                field = SugarTest.createField("base", fieldName, "enum", "detail", {isMultiSelect: true, options: "bugs_type_dom"});
                var jQueryStub = sinon.stub(field, '$');
                field.focus();
                expect(jQueryStub).not.toHaveBeenCalled();
                jQueryStub.restore();
            });
        });
    });

    describe('_sortResults', function() {
        var getAppListKeysStub, _sortBySpy, results, _order, getEditableDropdownFilterStub;

        var _expectOrder = function(results, order) {
            _.each(order, function(key, i) {
                expect(results[i].id).toEqual(key + '');
            });
        };

        beforeEach(function() {
            field = SugarTest.createField('base', fieldName, 'enum', 'edit');
            field.items = {};
            field.items['90'] = 90;
            field.items['100'] = 100;
            field.items['1'] = 'One';
            field.items[''] = '';
            field.items['Defect'] = 'DefectValue';
            field.items['Feature'] = 'FeatureValue';
            results = _.map(field.items, function(label, key) {
                return {id: key, text: label};
            });
            getAppListKeysStub = sinon.collection.stub(app.lang, 'getAppListKeys', function() {
                return _order;
            });
            _sortBySpy = sinon.collection.spy(_, 'sortBy');
        });

        using('undefined `app_list_keys` or already filtered results',
            [
                [{}],
                [[90, 100, '1', '', 'Defect', 'Feature']]
            ],
            function(values) {

                it('should not sort the results', function() {
                    _order = false;

                    results = field._sortResults(results);
                    expect(_sortBySpy).not.toHaveBeenCalled();

                    results = field._sortResults(results);
                    expect(_sortBySpy).not.toHaveBeenCalled();
                });

                it('should not sort already filtered results', function() {
                    _order = ['', 'Defect', 100, 'Feature', 90];
                    field.items = values;
                    field.filteredOptions = true;
                    results = field._sortResults(results);
                    expect(_sortBySpy).not.toHaveBeenCalled();
                });
            }
        );

        using('different order',
            [
                [['', 'Feature', 90, '1', 'Defect', 100]],
                [['', 'Defect', 100, '1', 'Feature', 90]]
            ],
            function(values) {

                it('should sort the results', function() {
                    _order = values;

                    results = field._sortResults(results);
                    _expectOrder(results, _order);
                    expect(_sortBySpy).toHaveBeenCalled();

                    results = field._sortResults(results);
                    _expectOrder(results, _order);
                    expect(_sortBySpy).toHaveBeenCalled();

                    expect(field._keysOrder).not.toEqual({});
                });
            });

        using('role-specific ordering or no-role',
            [
                {
                    order: [{'': '', 'Defect': 'DefectValue', '90': 90}],
                    expected: ['', 90, 'Defect'],
                    _keysOrder: {
                        '': 0,
                        90: 1,
                        'Defect': 2
                    }
                }
            ],
            function(values) {
                it('should sort the results', function() {
                    var expected = values.expected;
                    var ddFilterResult = [['', true], ['Feature', false], [90, true], ['1', false], ['Defect', true], [100, false]];
                    getEditableDropdownFilterStub = sinon.stub(app.metadata, 'getEditableDropdownFilter').returns(ddFilterResult);
                    field._keysOrder = values._keysOrder;
                    field.isFiltered = true;
                    _order = values.order;
                    results = _.map(_order[0], function(label, key) {
                        return {id: key, text: label};
                    });
                    results = field._sortResults(results);
                    _expectOrder(results, expected);
                    expect(_sortBySpy).toHaveBeenCalled();
                    expect(field._keysOrder).not.toEqual({});
                    getEditableDropdownFilterStub.restore();
                });
            });

        using('visibility grid',
            [
                {
                    order: [{'1': 'One', '90': '90', '': ''}],
                    expected: ['90', '', '1'],
                    targetField: 'testField',
                    targetValue: 'testValue',
                    visibility_grid: {
                        values: {}
                    },
                    modelValues: {
                        'testField': 'testValue'
                    }
                },
                {
                    order: [{'1': 'One', '90': '90', '': ''}],
                    expected: ['1', '90', ''],
                    targetField: '',
                    targetValue: 'testValue',
                    visibility_grid: {
                        values: {}
                    },
                    modelValues: {
                    }
                }
            ],
            function(provider) {
                it('should correctly order the values', function() {
                    var expected = provider.expected;
                    field.def.visibility_grid = provider.visibility_grid;
                    field.def.visibility_grid.trigger = provider.targetField;
                    field.def.visibility_grid.values[provider.targetValue] = provider.expected;

                    _.each(provider.modelValues, function(value, key) {
                        field.model.set(key, value);
                    });

                    _order = provider.order;
                    results = _.map(_order[0], function(label, key) {
                        return {id: key, text: label};
                    });
                    results = field._sortResults(results);
                    _expectOrder(results, expected);
                });
            });
    });

    describe('massupdate', function() {
        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'massupdate');
            SugarTest.testMetadata.set();
        });

        describe('render', function() {
            it('should render with the appendValues checkbox only if it is multiselect', function() {
                field = SugarTest.createField('base', fieldName, 'enum', 'massupdate',
                    {isMultiSelect: false, options: 'bugs_type_dom'});
                field.render();

                expect(field.$(field.appendValueTag)).not.toExist();

                field.dispose();
                field = SugarTest.createField('base', fieldName, 'enum', 'massupdate',
                    {isMultiSelect: true, options: 'bugs_type_dom'});
                field.render();

                expect(field.$(field.appendValueTag)).toExist();
            });
        });

        describe('bindDomChange', function() {
            it('should update the model on append_value checkbox change when enum is multiselect', function() {
                field = SugarTest.createField('base', fieldName, 'enum', 'massupdate',
                    {isMultiSelect: true, options: 'bugs_type_dom'});
                field.render();

                expect(field.appendValue).toBeUndefined();
                expect(field.model.get(fieldName + '_replace')).toBeUndefined();

                field.$(field.appendValueTag).prop('checked', true).trigger('change');

                expect(field.appendValue).toBeTruthy();
                expect(field.model.get(fieldName + '_replace')).toBe('1');

                field.$(field.appendValueTag).prop('checked', false).trigger('change');

                expect(field.appendValue).toBeFalsy();
                expect(field.model.get(fieldName + '_replace')).toBe('0');
            });
        });
    });
});
