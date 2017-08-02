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

describe('modules.KBContents.clients.base.view.CreateView', function() {
    var app;
    var moduleName = 'KBContents';
    var viewName = 'create';
    var sinonSandbox;
    var view;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();

        SugarTest.loadFile(
            '../modules/KBContents/clients/base/plugins',
            'KBNotify',
            'js',
            function(d) {
                app.events.off('app:init');
                eval(d);
                app.events.trigger('app:init');
            });
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'view', viewName, moduleName);
        view = SugarTest.createView('base', moduleName, viewName, null, null, true);
    });

    afterEach(function() {
        delete app.plugins.plugins.view.KBNotify;
        sinonSandbox.restore();
        view.dispose();
        app.view.reset();
    });

    it('Success created callback should trigger kb:collection:updated', function() {
        var callbackStub = sinonSandbox.stub();
        var eventCallbackStub = sinonSandbox.stub();
        view.on('kb:collection:updated', eventCallbackStub);

        var options = {'success': callbackStub};
        var customSaveOptions = view.getCustomSaveOptions(options);
        customSaveOptions.success();

        expect(callbackStub).toHaveBeenCalled();
        expect(eventCallbackStub).toHaveBeenCalled();
    });
});
