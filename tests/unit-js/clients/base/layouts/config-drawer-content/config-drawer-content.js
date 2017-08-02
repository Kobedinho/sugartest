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
describe('Base.Layout.ConfigDrawerContent', function() {
    var app,
        context,
        layout,
        options;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());

        options = {
            context: context
        };

        var meta = {
            panels: [{
                fields: []
            }]
        };
        layout = SugarTest.createLayout('base', null, 'config-drawer-content', meta, context);
    });

    afterEach(function() {
        sinon.collection.restore();
        layout = null;
    });

    describe('initialize()', function() {
        var initHowToSpy;

        beforeEach(function() {
            initHowToSpy = sinon.collection.spy(layout, '_initHowTo');
        });

        it('should call _initHowTo', function() {
            layout.initialize(options);
            expect(initHowToSpy).toHaveBeenCalled();
        });
    });
});
