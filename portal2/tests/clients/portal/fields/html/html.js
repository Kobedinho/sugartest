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
describe('Portal.Field.Html', function() {
    var app;
    var field;
    var _oConfigSiteUrl;

    beforeEach(function() {
        app = SugarTest.app;
        _oConfigSiteUrl = app.config.siteUrl;
        app.config.siteUrl = '/the/site/url';
        SugarTest.loadComponent('portal', 'field', 'html');
    });

    afterEach(function() {
        app.config.siteUrl = _oConfigSiteUrl;
        app.cache.cutAll();
        app.view.reset();
    });

    describe('format', function() {

        beforeEach(function() {
            field = SugarTest.createField('portal', 'testfield', 'html', 'detail', {});
        });

        it('should prepend the site url to the embeded images', function() {
            var inputValue = '<img src="path/to/local/filename.jpg"><img src="path/to/local/filename1.jpg">';
            var expectedValue = '<img src="' + app.config.siteUrl +
                '/path/to/local/filename.jpg"><img src="' + app.config.siteUrl + '/path/to/local/filename1.jpg">';
            var formattedValue = field.format(inputValue);
            expect(formattedValue).toEqual(expectedValue);
        });

        it('should not prepend the site url to the external images', function() {
            var externalLink = '<img src="http://example.com/external/filename.jpg">';
            var formattedValue = field.format(externalLink);
            expect(formattedValue).toEqual(externalLink);
        });

        it('Should not prepend the HTTPS site url to the external images.', function() {
            var secureExternalLink = '<img src="https://example.com/external/filename.jpg">';
            var formattedValue = field.format(secureExternalLink);
            expect(formattedValue).toEqual(secureExternalLink);
        });
    });

});
