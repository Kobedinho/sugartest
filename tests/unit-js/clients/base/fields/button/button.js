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
describe("Base.Field.Button", function() {
    var app, field, Address;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'field', 'button');
        app.routing.start();
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.model = null;
        field._loadTemplate = null;
        field = null;
        Address = null;
        app.routing.stop();
    });

    it("should setDisabled with CSS 'disabled'", function() {
        var def = {
            'events' : {
                'click .btn' : 'function() { this.callback = "stuff excuted"; }',
                'blur .btn' : 'function() { this.callback = "blur excuted"; }'
            }
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        field._loadTemplate = function() {  this.template = function(){ return '<a class="btn" href="javascript:void(0);"></a>'}; };

        expect(field.getFieldElement().hasClass("disabled")).toBeFalsy();
        field.render();
        field.setDisabled(true);
        expect(field.getFieldElement().hasClass("disabled")).toBeTruthy();
        field.setDisabled(false);
        expect(field.getFieldElement().hasClass("disabled")).toBeFalsy();
        field.setDisabled();
        expect(field.getFieldElement().hasClass("disabled")).toBeTruthy();
    });

    it('css_class should contain disable after calling setDisabled(true) and not after setDisabled(false)', function() {
        var def = {
            css_class: 'btn'
        };
        field = SugarTest.createField('base', 'button', 'button', 'edit', def);

        // make sure it doesn't start with it.
        expect(field.def.css_class).not.toContain('disabled');
        field.setDisabled(true);
        // make sure it's added
        expect(field.def.css_class).toContain('disabled');
        field.setDisabled(false);
        // make sure it's removed
        expect(field.def.css_class).not.toContain('disabled');
    });

    it("should show and hide functions must trigger hide and show events, and it should change the isHidden property", function() {

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'edit');
        SugarTest.testMetadata.set();

        var def = {
            'events' : {
                'click .btn' : 'function() { this.callback = "stuff excuted"; }',
                'blur .btn' : 'function() { this.callback = "blur excuted"; }'
            }
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        field.render();

        // we need to hide first, since the render() does the show
        var triggers2 = sinon.spy(field, 'trigger');
        field.hide();
        expect(triggers2.calledOnce).toBe(true);
        expect(triggers2.calledWithExactly('hide')).toBe(true);
        expect(field.isHidden).toBe(true);
        expect(field.isVisible()).toBe(false);
        triggers2.restore();

        // now try and show it
        var triggers = sinon.spy(field, 'trigger');
        field.show();
        expect(triggers.calledOnce).toBe(true);
        expect(triggers.calledWithExactly('show')).toBe(true);
        expect(field.isHidden).toBe(false);
        expect(field.isVisible()).toBe(true);
        triggers.restore();

        SugarTest.testMetadata.dispose();

    });

    it('should not show buttons for BWC modules if allow_bwc is false', function(){
        var bwcStub = sinon.stub(app.metadata, "getModule", function(){
            return {isBwcEnabled: true};
        });
        var def = {
            'acl_action' : 'edit',
            'allow_bwc' : false
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        var stubHasAccess = sinon.stub(app.acl, "hasAccess").returns(true);
        var stubHasAccessToModel = sinon.stub(app.acl, "hasAccessToModel").returns(true);

        var access = field.triggerBefore('render');
        expect(access).toBeFalsy();

        stubHasAccess.restore();
        stubHasAccessToModel.restore();
        bwcStub.restore();
    });

    it('should show buttons for BWC modules if allow_bwc is true', function(){
        var bwcStub = sinon.stub(app.metadata, "getModule", function(){
            return {isBwcEnabled: true};
        });
        var def = {
            'acl_action' : 'edit',
            'allow_bwc' : true
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        var stubHasAccess = sinon.stub(app.acl, "hasAccess").returns(true);
        var stubHasAccessToModel = sinon.stub(app.acl, "hasAccessToModel").returns(true);

        var access = field.triggerBefore('render');
        expect(access).toBeTruthy();

        stubHasAccess.restore();
        stubHasAccessToModel.restore();
        bwcStub.restore();
    });

    it('should call app.acl.hasAccessToModel if acl_module is not specified', function() {
        var def = {
            'acl_action' : 'edit'
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        var stubHasAccess = sinon.stub(app.acl, "hasAccess").returns(true);
        var stubHasAccessToModel = sinon.stub(app.acl, "hasAccessToModel").returns(false);

        var access = field.triggerBefore('render');
        expect(stubHasAccess).not.toHaveBeenCalled();
        expect(stubHasAccessToModel).toHaveBeenCalled();
        expect(access).toBeFalsy();

        stubHasAccess.restore();
        stubHasAccessToModel.restore();
    });

    it('should call app.acl.hasAccess if acl_module is specified', function() {
        var def = {
            'acl_module' : 'Contacts',
            'acl_action' : 'edit'
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        var stubHasAccess = sinon.stub(app.acl, "hasAccess").returns(true);
        var stubHasAccessToModel = sinon.stub(app.acl, "hasAccessToModel").returns(false);

        var access = field.triggerBefore('render');
        expect(stubHasAccess).toHaveBeenCalled();
        expect(stubHasAccessToModel).not.toHaveBeenCalled();
        expect(access).toBeTruthy();

        stubHasAccess.restore();
        stubHasAccessToModel.restore();

    });

    it('should update isHidden if show is called and hasAccess returns false', function() {
        var def = {
            'acl_module' : 'Contacts',
            'acl_action' : 'edit'
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        var accessStub = sinon.stub(field,'hasAccess', function(){
            return false;
        })

        field.show();

        expect(field.isHidden).toBeTruthy();
        expect(field.isVisible()).toBeFalsy();

        accessStub.restore();

    });

    it('should update visibility once it triggers rendering', function() {
        var def = {
            'acl_module' : 'Contacts',
            'acl_action' : 'edit'
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        var accessStub = sinon.stub(field,'hasAccess', function(){
            return true;
        })
        field.render();
        expect(field.isVisible()).toBe(true);
        accessStub.restore();

        accessStub = sinon.stub(field,'hasAccess', function(){
            return false;
        })
        var renderStub = sinon.stub(field, "_render");
        field.render();
        expect(field.isVisible()).toBe(false);
        expect(renderStub).not.toHaveBeenCalled();
        renderStub.restore();
        accessStub.restore();
    });

    it("should differentiate string routes from sidecar route object", function() {
        var def = {
            'route' : {
                'action' : 'edit'
            }
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        field.render();
        expect(field.fullRoute).toBeNull();

        def = {
            'route' : 'custom/route'
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        field.render();
        expect(field.fullRoute).toEqual('custom/route');
    });

    it('should create accessibility label from icon class name', function() {
        var icons = [
            {
                'icon' : 'cog',
                'expectedLabel' : 'cog'
            }, {
                'icon' : 'fa-cog',
                'expectedLabel' : 'cog'
            }, {
                'icon' : 'fa-cog-o',
                'expectedLabel' : 'cog outline'
            }, {
                'icon' : 'fa-cog-o-modify',
                'expectedLabel' : 'cog outline modify'
            }
        ];

        _.each(icons, function(data) {
            field = SugarTest.createField('base', null, 'button', 'edit', data);
            field.render();

            expect(field.ariaLabel).toEqual(data.expectedLabel);
        });
    });

    it("should test hasAccess control before it is rendered", function() {
        field = SugarTest.createField("base","button", "button", "edit");
        var hasAccessStub = sinon.stub(field, 'hasAccess');
        field.triggerBefore("render");
        expect(hasAccessStub).toHaveBeenCalled();
        hasAccessStub.restore();
    });

    it("should update visibility simultaneously once it triggers show and hide", function() {
        field = SugarTest.createField("base","button", "button", "edit");
        field.on("hide", function() {
            expect(this.isVisible()).toBe(false);
        }, field);
        field.on("show", function() {
            expect(this.isVisible()).toBe(true);
        }, field);
        field.show();
        field.hide();
        field.off();
    });

    it('should prevent click when disabled', function() {
        var called = false,
            def = {
                events: {
                    // In the events hash, Backbone is always checking for
                    // _.isFunction, and since sinon stubs are objects, we can't
                    // use one here, so we just use a flag instead.
                    'click .btn': function() {
                        called = true;
                    }
                }
            };

        field = SugarTest.createField('base', 'button', 'button', 'edit', def);
        loadTemplateStub = sinon.stub(field, '_loadTemplate', function() {
            this.template = function() {
                return '<a class="btn" href="javascript:void(0);"></a>'
            };
        });

        field.render();

        field.setDisabled(true);
        field.$('.btn').click();
        expect(called).toBe(false);

        field.setDisabled(false);
        field.$('.btn').click();
        expect(called).toBe(true);
    });
});
