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
describe('Base.Field.Iframe', function() {
    var app, field;

    beforeEach(function() {
        var def = {
            'default': 'http://www.sugarcrm.com/{ONE}'
        };
        app = SugarTest.app;
        field = SugarTest.createField('base', 'iframe', 'iframe', 'detail', def);
        field.model = new Backbone.Model({
            'ONE': '1',
            'TWO': '2',
            'website': 'http://www.google.com'
        });
    });

    afterEach(function() {
        field.dispose();
    });

    describe('unformat', function() {
        using('different URLs', [
            {
                url: 'http://',
                expectedUrl: ''
            },
            {
                url: 'http://www.google.com',
                expectedUrl: 'http://www.google.com'
            }
        ], function(value) {
            it('should unformat properly', function() {
                expect(field.unformat(value.url)).toEqual(value.expectedUrl);
            });
        });
    });

    describe('format', function() {
        using('different URLs', [
            {
                url: 'http://www.google.com',
                expectedUrl: 'http://www.google.com',
                generated: null
            },
            {
                url: 'https://www.google.com',
                expectedUrl: 'https://www.google.com',
                generated: null
            },
            {
                url: 'www.google.com',
                expectedUrl: 'http://www.google.com',
                generated: null
            },
            {
                url: 'http://{ONE}/{TWO}',
                expectedUrl: 'http://{ONE}/{TWO}',
                generated: null
            },
            {
                url: 'http://{ONE}/{TWO}',
                expectedUrl: 'http://1/2',
                generated: '1'
            },
            {
                url: 'https://{ONE}/{TWO}',
                expectedUrl: 'https://1/2',
                generated: '1'
            },
            {
                url: '{website}',
                expectedUrl: 'http://www.google.com',
                generated: '1'
            },
            {
                url: '',
                expectedUrl: 'http://www.sugarcrm.com/1',
                generated: '1'
            },
        ], function(value) {
            it('should format generated and non-generated URLs properly', function() {
                field.def.gen = value.generated;
                expect(field.format(value.url)).toEqual(value.expectedUrl);
            });
        });
    });
});
