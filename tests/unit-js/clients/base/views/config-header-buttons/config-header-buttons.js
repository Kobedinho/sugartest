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
describe('Base.View.ConfigHeaderButtons', function() {
    var app,
        view,
        module;

    beforeEach(function() {
        app = SugarTest.app;
        module = 'Opportunities';

        sinon.collection.stub(app.lang, 'getModuleName').withArgs(module, {plural: true}).returns('Opps');

        view = SugarTest.createView('base', module, 'config-header-buttons');
        app.routing.start();
    });

    afterEach(function() {
        sinon.collection.restore();
        view = null;
        app.routing.stop();
    });

    it('will have custom module name in moduleLangObj', function() {
        expect(view.moduleLangObj.module).toBe('Opps');
    });

    describe('cancelConfig()', function() {
        beforeEach(function() {
            sinon.collection.stub(app.router, 'goBack', function() {});
        });
        it('should close the drawer if there is one', function() {
            app.drawer = {
                close: $.noop,
                count: function() {
                    return 1;
                }
            };
            sinon.collection.spy(app.drawer, 'close');
            view.cancelConfig();

            expect(app.drawer.close).toHaveBeenCalled();
            delete app.drawer;
        });

        it('should navigate to the module if there is no drawer', function() {
            app.drawer = {
                count: function() {
                    return 0;
                }
            };
            sinon.collection.spy(app.router, 'navigate');
            view.cancelConfig();

            expect(app.router.navigate).toHaveBeenCalledWith(module, {trigger: true});
            delete app.drawer;
        });
    });

    describe('saveConfig()', function() {
        var button;
        beforeEach(function() {
            button = SugarTest.createField({
                client: 'base',
                name: 'save_button',
                type: 'button',
                viewName: 'detail',
                fieldDef: {
                    label: 'LBL_SAVE_BUTTON_LABEL'
                }
            });

            sinon.collection.stub(button, 'setDisabled').withArgs(true).returns(true);
            view.fields['save_button'] = button;
        });

        afterEach(function() {
            button = null;
        });

        it('will disable the save button', function() {
            sinon.collection.stub(view, '_saveConfig');
            view.saveConfig();
            expect(button.setDisabled).toHaveBeenCalledWith(true);
        });

        it('will not disable if beforeSave returns false', function() {
            sinon.collection.stub(view, 'triggerBefore').returns(false);
            view.saveConfig();
            expect(button.setDisabled).not.toHaveBeenCalled();
        });
    });

    describe('_saveConfig()', function() {
        var button;
        beforeEach(function() {
            button = SugarTest.createField({
                client: 'base',
                name: 'save_button',
                type: 'button',
                viewName: 'detail',
                fieldDef: {
                    label: 'LBL_SAVE_BUTTON_LABEL'
                }
            });

            sinon.collection.stub(button, 'setDisabled').withArgs(false).returns(true);
            view.fields['save_button'] = button;
        });

        afterEach(function() {
            button = null;
        });

        it('on xhr error will enable the button', function() {
            sinon.collection.stub(app.api, 'call', function(method, url, data, callbacks) {
                callbacks.error({});
            });

            view._saveConfig();
            expect(button.setDisabled).toHaveBeenCalledWith(false);
        });
    });
});
