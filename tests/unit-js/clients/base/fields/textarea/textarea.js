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
describe('Base.Field.TextArea', function() {
    var app, field, template,
        module = 'Bugs',
        fieldName = 'foo';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        template = SugarTest.loadHandlebarsTemplate('textarea', 'field', 'base', 'detail');
        SugarTest.testMetadata.set();
        fieldDef = {
            settings: {
                max_display_chars: 8
            }
        };
        field = SugarTest.createField('base', fieldName, 'textarea', 'detail', fieldDef, module);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.dispose();
        sinon.collection.restore();
    });

    describe('initialize', function() {
        it('should initialize settings to the values in `this.def` appropriately', function() {
            fieldDef.settings.collapsed = false;
            var testField = SugarTest.createField('base', fieldName, 'textarea', 'detail', fieldDef, module);

            expect(testField._settings.max_display_chars).toEqual(fieldDef.settings.max_display_chars);
            expect(testField._settings.collapsed).toEqual(fieldDef.settings.collapsed);
            expect(testField.collapsed).toEqual(testField._settings.collapsed);
        });
    });

    describe('format', function() {
        beforeEach(function() {
            // FIXME will be moved back to action once SC-2608 is done
            field.tplName = 'detail';
        });

        using('various field actions', [
            {
                action: 'list',
                parent: null,
                longExists: true
            },
            {
                action: 'edit',
                parent: null,
                longExists: false
            },
            {
                action: 'disabled',
                parent: null,
                longExists: true
            },
            {
                action: 'disabled',
                parent: {},
                longExists: true
            },
            {
                action: 'disabled',
                parent: {action: 'detail'},
                longExists: true
            },
            {
                action: 'detail',
                parent: null,
                longExists: true
            }
        ], function(value) {
            it('should set a `long` value for all modes except edit', function() {
                field.parent = value.parent;
                // FIXME will be moved back to action once SC-2608 is done
                field.tplName = value.action;
                var returnVal = field.format('testvalue');

                expect(returnVal.hasOwnProperty('long')).toBe(value.longExists);
            });
        });

        // max_display_chars was kept to '8' for this test.
        using('various field values', [
            {
                fieldVal: 'testvalue',
                shortExists: true,
                expectedShortValue: 'testvalu'
            },
            {
                fieldVal: 'testvalu',
                shortExists: false,
                expectedShortValue: undefined
            },
            {
                fieldVal: 'testval',
                shortExists: false,
                expectedShortValue: undefined
            },
            {
                fieldVal: '',
                shortExists: false,
                expectedShortValue: undefined
            },
            {
                fieldVal: '結葉鮮敬。対好速残',
                shortExists: true,
                expectedShortValue: '結葉鮮敬。対好速'
            },
            {
                fieldVal: '結葉鮮敬。対好\n速残',
                shortExists: true,
                expectedShortValue: '結葉鮮敬。対好'
            },
            {
                fieldVal: '結葉鮮敬。対\n好速残',
                shortExists: true,
                expectedShortValue: '結葉鮮敬。対\n好'
            }
        ], function(value) {
            it('should set a proper `short` value if the field value exceeds `max_display_chars`', function() {
                var returnVal = field.format(value.fieldVal);

                expect(returnVal.hasOwnProperty('short')).toBe(value.shortExists);
                expect(returnVal.hasOwnProperty('long')).toBe(true);
                expect(returnVal.long).toEqual(value.fieldVal);
                expect(returnVal.short).toEqual(value.expectedShortValue);
            });
        });
    });

    describe('setMode', function() {
        using('different actions and modes', [
            {action: 'list', mode: 'edit', expectedMode: 'list'},
            {action: 'list', mode: 'disabled', expectedMode: 'list'},
            {action: 'list', mode: 'detail', expectedMode: 'detail'},
            {action: 'list', mode: 'list', expectedMode: 'list'},
            {action: 'detail', mode: 'edit', expectedMode: 'edit'},
            {action: 'detail', mode: 'disabled', expectedMode: 'disabled'},
            {action: 'detail', mode: 'list', expectedMode: 'list'},
            {action: 'detail', mode: 'detail', expectedMode: 'detail'},
            {action: 'edit', mode: 'detail', expectedMode: 'detail'},
            {action: 'edit', mode: 'list', expectedMode: 'list'},
            {action: 'edit', mode: 'disabled', expectedMode: 'disabled'},
            {action: 'edit', mode: 'edit', expectedMode: 'edit'},
            {action: 'disabled', mode: 'detail', expectedMode: 'detail'},
            {action: 'disabled', mode: 'list', expectedMode: 'list'},
            {action: 'disabled', mode: 'edit', expectedMode: 'edit'},
            {action: 'disabled', mode: 'disabled', expectedMode: 'disabled'}
        ], function(value) {
            it('should call the parent `setMode` with the appropriate mode', function() {
                var superStub = sinon.collection.stub(field, '_super');
                // FIXME will be moved back to action once SC-2608 is done
                field.tplName = value.action;
                field.setMode(value.mode);

                expect(superStub).toHaveBeenCalledWith('setMode', [value.expectedMode]);
            });
        });
    });

    describe('toggleCollapsed', function() {
        using('values', [true, false], function(value) {
            it('should toggle the value of `collapsed` and call render', function() {
                var renderStub = sinon.collection.stub(field, 'render');

                field.collapsed = value;
                field.toggleCollapsed();

                expect(field.collapsed).toBe(!value);
                expect(renderStub).toHaveBeenCalled();
            });
        });
    });
});
