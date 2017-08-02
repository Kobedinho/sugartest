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
describe("Quick Create Dropdown", function() {
    var viewName = 'quickcreate',
        app, view, backupIsSynced, testMeta, testHasCreateAccess;


    var buildQuickCreateMeta = function(module, visible, order) {
        return {menu:{quickcreate:{meta:{module:module,visible:visible,order:order}}}};
    };

    var findModuleInMenuItems = function(menuItems, module) {
        return !!_.find(menuItems, function(menuItem) {
            return menuItem.module === module;
        });
    };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();

        sinon.collection.stub(app.shortcuts, 'registerGlobal');

        view = SugarTest.createView("base",null, viewName, null, null);

        // Fake user is authenticated
        sinon.collection.stub(SugarTest.app.api, 'isAuthenticated', function() {
            return true;
        });
        // Fake app is synced
        backupIsSynced = app.isSynced;
        app.isSynced = true;

        // Test metadata
        testMeta = {
            Accounts: buildQuickCreateMeta('Accounts', true, 0),
            Contacts: buildQuickCreateMeta('Contacts', true, 1),
            Opportunities: buildQuickCreateMeta('Opportunities', true, 2)
        };
        // Acls for the modules in the metadata
        testHasCreateAccess = {
            Accounts: true,
            Contacts: true,
            Opportunities: true
        };

        sinon.collection.stub(app.acl, 'hasAccess', function (action, module) {
            // Sugar.App.acl.hasAccess is called with action=quickcreate as a part of rendering the view beyond
            // determining which modules are accessible. So we assume that TRUE should be returned for those calls
            // and to only be more scrupulous when action=create, which is expected per the
            // BaseQuickcreateView#_renderHtml call.
            if (action !== 'create') {
                return true;
            }
            return testHasCreateAccess[module];
        });
        sinon.collection.stub(SugarTest.app.metadata, 'getModuleNames', function(options) {
            var modules = [];
            _.each(testMeta, function(meta, module) {
                if (app.acl.hasAccess(options.access, module)) {
                    modules.push(module);
                }
            });
            return modules;
        });
        sinon.collection.stub(SugarTest.app.metadata, 'getModule', function(module) {
            return testMeta[module];
        });
    });

    afterEach(function() {
        sinon.collection.restore();
        app.isSynced = backupIsSynced;
        SugarTest.testMetadata.dispose();
        view = null;
    });

    it("Should build create actions for all modules", function() {
        view.render();

        expect(findModuleInMenuItems(view.createMenuItems, 'Accounts')).toBeTruthy();
        expect(findModuleInMenuItems(view.createMenuItems, 'Contacts')).toBeTruthy();
        expect(findModuleInMenuItems(view.createMenuItems, 'Opportunities')).toBeTruthy();
    });

    it("Should not build create actions even if visible meta attribute not specified", function() {
        //Remove visible attribute for Accounts
        delete testMeta.Accounts.menu.quickcreate.meta.visible;
        view.render();

        expect(findModuleInMenuItems(view.createMenuItems, 'Accounts')).toBeFalsy();
        expect(findModuleInMenuItems(view.createMenuItems, 'Contacts')).toBeTruthy();
        expect(findModuleInMenuItems(view.createMenuItems, 'Opportunities')).toBeTruthy();
    });

    it("Should not build modules that don't have quickcreate meta", function() {
        var expectedModules = ['Accounts', 'Contacts', 'Opportunities'];
        testMeta.Foo = {};
        view.render();

        _.each(expectedModules, function(module) {
            expect(findModuleInMenuItems(view.createMenuItems, module)).toBeTruthy();
        });
        expect(findModuleInMenuItems(view.createMenuItems, 'Foo')).toBeFalsy();
    });

    it("Should not build create action for modules user does not have create access to", function() {
        // Remove create ACLs for Accounts
        testHasCreateAccess['Accounts'] = false;
        view.render();

        expect(findModuleInMenuItems(view.createMenuItems, 'Accounts')).toBeFalsy();
        expect(findModuleInMenuItems(view.createMenuItems, 'Contacts')).toBeTruthy();
        expect(findModuleInMenuItems(view.createMenuItems, 'Opportunities')).toBeTruthy();
    });

    it("Should not build create actions that are hidden", function() {
        //Hide Contacts item
        testMeta.Contacts.menu.quickcreate.meta.visible = false;
        view.render();

        expect(findModuleInMenuItems(view.createMenuItems, 'Accounts')).toBeTruthy();
        expect(findModuleInMenuItems(view.createMenuItems, 'Contacts')).toBeFalsy();
        expect(findModuleInMenuItems(view.createMenuItems, 'Opportunities')).toBeTruthy();
    });

    it("Should build create actions based on order attribute", function() {
        view.render();

        expect(view.createMenuItems[0].module).toBe('Accounts');
        expect(view.createMenuItems[1].module).toBe('Contacts');
        expect(view.createMenuItems[2].module).toBe('Opportunities');
    });

    it("Should change the order of create actions if it has been changed from default", function() {
        testMeta.Accounts.menu.quickcreate.meta.order = 2;
        testMeta.Contacts.menu.quickcreate.meta.order = 0;
        testMeta.Opportunities.menu.quickcreate.meta.order = 1;
        view.render();

        expect(view.createMenuItems[0].module).toBe('Contacts');
        expect(view.createMenuItems[1].module).toBe('Opportunities');
        expect(view.createMenuItems[2].module).toBe('Accounts');
    });
});
