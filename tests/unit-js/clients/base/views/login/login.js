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
describe("Login View", function() {

    var view, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('login', {
            'panels': [
                {
                    'fields': [
                        {
                            'name': 'username',
                            'type': 'text',
                            'required': true
                        },
                        {
                            'name': 'password',
                            'type': 'password',
                            'required': true
                        }
                    ]
                }
            ]
        });
        SugarTest.testMetadata.set();
        view = SugarTest.createView("base", "Login", "login");
        app = SUGAR.App;
    });

    afterEach(function() {
        view.dispose();
        app.cache.cutAll();
        app.view.reset();
        sinon.collection.restore();
        Handlebars.templates = {};
        view = null;
    });

    describe("Declare Login Bean", function() {

        //Internet Explorer
        it("should have declared a Bean with the fields metadata", function() {
            expect(view.model.fields).toBeDefined();
            expect(_.size(view.model.fields)).toBeGreaterThan(0);
            expect(_.size(view.model.fields.username)).toBeDefined();
            expect(_.size(view.model.fields.password)).toBeDefined();
        });
    });

    describe("Browser support", function() {

        var alertStub;
        var originalBrowser;
        var currentNavigator = {};

        beforeEach(function() {
            alertStub = sinon.stub(app.alert, "show");
            originalBrowser = $.browser;
        });

        afterEach(function() {
            $.browser = originalBrowser;
            alertStub.restore();
        });
        //Internet Explorer
        it("should deem IE8 as an unsupported browser", function() {
            $.browser = {
                'version': '8',
                'msie': true
            };
            expect(view._isSupportedBrowser(currentNavigator)).toBeFalsy();
        });
        it("should deem IE9 as a supported browser", function() {
            $.browser = {
                'version': '9',
                'msie': true
            };
            expect(view._isSupportedBrowser(currentNavigator)).toBeTruthy();

        });
        it("should deem IE10 as a supported browser", function() {
            $.browser = {
                'version': '10',
                'msie': true
            };
            expect(view._isSupportedBrowser(currentNavigator)).toBeTruthy();

        });
        it("should deem IE11 as a supported browser", function() {
            $.browser = {
                'version': '11',
                'msie': true
            };
            expect(view._isSupportedBrowser(currentNavigator)).toBeTruthy();
        });

        //Mozilla Firefox
        it("should deem Firefox 40 as an unsupported browser", function () {
            $.browser = {
                'version': '40',
                'mozilla': true
            };
            expect(view._isSupportedBrowser(currentNavigator)).toBeFalsy();
        });
        it("should deem Firefox 41 as a supported browser", function () {
            $.browser = {
                'version': '41',
                'mozilla': true
            };
            expect(view._isSupportedBrowser(currentNavigator)).toBeTruthy();
        });
        //Safari
        it("should deem Safari 6 as an unsupported browser", function() {
            $.browser = {
                'version': '536',
                'safari': true,
                'webkit': true
            };
            expect(view._isSupportedBrowser(currentNavigator)).toBeFalsy();
        });
        it("should deem Safari 7 as a supported browser", function() {
            $.browser = {
                'version': '537',
                'safari': true,
                'webkit': true
            };
            expect(view._isSupportedBrowser(currentNavigator)).toBeTruthy();
        });
        //Chrome
        it("should deem Chrome 26 as an unsupported browser", function() {
            $.browser = {
                'version': '26',
                'chrome': true,
                'webkit': true
            };
            expect(view._isSupportedBrowser(currentNavigator)).toBeFalsy();
        });
        it("should deem Chrome 27 as an unsupported browser", function() {
            $.browser = {
                'version': '27',
                'chrome': true,
                'webkit': true
            };
            expect(view._isSupportedBrowser(currentNavigator)).toBeFalsy();

        });
        it("should deem Chrome 47 as a supported browser", function () {
            $.browser = {
                'version': '537.36',
                'chrome': true,
                'webkit': true
            };
            expect(view._isSupportedBrowser(currentNavigator)).toBeTruthy();
        });
    });

    describe('handle keypress', function() {
        it('should trigger login if `ENTER` key is pressed', function() {
            sinon.collection.stub(view, 'login');
            var evt = $.Event('keypress', {keyCode: 13}),    //ENTER key
                evt2 = $.Event('keypress', {keyCode: 16});   //SHIFT key
            view.handleKeypress(evt);

            expect(view.login).toHaveBeenCalled();

            view.handleKeypress(evt2);

            expect(view.login.calledOnce).toBeTruthy();
        });
    });

    describe('postLogin', function() {
        beforeEach(function() {
            sinon.collection.stub(app.user, 'get')
                .withArgs('show_wizard').returns(false);
        });

        it('should only refresh additional components when wizard is not shown', function() {
            sinon.collection.spy(view, 'refreshAdditionalComponents');
            view.postLogin();

            expect(view.refreshAdditionalComponents).toHaveBeenCalled();

            app.user.get.withArgs('show_wizard').returns(true);
            view.postLogin();

            expect(view.refreshAdditionalComponents.calledOnce).toBeTruthy();
        });

        it('should only pop alert of different timezone if timezones do not match', function() {
            sinon.collection.spy(app.alert, 'show');
            sinon.collection.stub(Date.prototype, 'getTimezoneOffset').returns(420);
            sinon.collection.stub(app.user, 'getPreference')
                .withArgs('tz_offset_sec').returns(420 * (-30));
            view.postLogin();

            expect(app.alert.show).toHaveBeenCalledWith(view._alertKeys.offsetProblem);

            app.user.getPreference.withArgs('tz_offset_sec').returns(420 * (-60));
            view.postLogin();

            expect(app.alert.show.calledOnce).toBeTruthy();
        });
    });

    describe('fields patching', function() {
        //FIXME: Enforce with `required` => false in metadata once it is implemented (SC-3106)
        it('should enforce that `username` and `password` fields are required', function() {
            _.each(view.meta.panels[0].fields, function(field) {
                expect(field.required).toEqual(true);
            });
        });
    });

    describe('logging in', function() {
        //FIXME: Login fields should trigger model change (SC-3106)
        it('should set the username and password in the model', function() {
            sinon.collection.stub(view, '$')
                .withArgs('input[name=password]').returns({
                    val: function() {
                        return 'pass';
                    }
                })
                .withArgs('input[name=username]').returns({
                    val: function() {
                        return 'user';
                    }
                });
            sinon.collection.stub(view.model, 'doValidate');

            view.login();

            expect(view.model.get('password')).toEqual('pass');
            expect(view.model.get('username')).toEqual('user');
        });

        it('should pass exact username and password to the API', function() {
            sinon.collection.stub(view.model, 'doValidate', function(fields, callback) {
                callback(true);
            });
            //FIXME: Use field values instead (SC-3106)
            sinon.collection.stub(view, '$')
                .withArgs('input[name=password]').returns({
                    val: function() {
                        return 'pass';
                    }
                })
                .withArgs('input[name=username]').returns({
                    val: function() {
                        return 'user';
                    }
                });
            sinon.collection.stub(app, 'login');

            view.login();

            expect(app.login).toHaveBeenCalledWith({password: 'pass', username: 'user'});
        });

        describe('successful login', function() {
            beforeEach(function() {
                sinon.collection.stub(view.model, 'doValidate', function(fields, callback) {
                    callback(true);
                });
                sinon.collection.spy(app.alert, 'show');
                sinon.collection.spy(app.alert, 'dismiss');
                sinon.collection.stub(app, 'login', function(credentials, info, callbacks) {
                    callbacks.success();
                    callbacks.complete();
                });
            });

            it('should only show `loading...` alert while processing the login', function() {
                view.login();

                expect(app.alert.show).toHaveBeenCalledWith(view._alertKeys.login);
                expect(app.alert.dismiss).toHaveBeenCalledWith(view._alertKeys.login);
            });

            it('should dismiss login alerts upon successfully logging in', function() {
                view.login();

                expect(app.alert.dismiss).toHaveBeenCalledWith(view._alertKeys.needLogin);
                expect(app.alert.dismiss).toHaveBeenCalledWith(view._alertKeys.invalidGrant);
            });

            it('should handle post login events once successfully logged in', function() {
                sinon.collection.stub(view, 'postLogin');
                view.login();
                app.events.trigger('app:sync:complete');
                expect(view.postLogin).toHaveBeenCalled();
            });
        });

        describe('unsuccessful login', function() {
            it('should not do anything if model is not valid', function() {
                sinon.collection.stub(view.model, 'doValidate', function(fields, callback) {
                    return callback(false);
                });
                sinon.collection.spy(app.alert, 'show');

                view.login();

                expect(app.alert.show).not.toHaveBeenCalled();
            });
        });
    });

    describe('refreshAdditionalComponents', function() {
        it('should render each additional component', function() {
            var originalComponents = app.additionalComponents;
            app.additionalComponents = [
                {'render' : $.noop},
                {'render' : $.noop}
            ];
            sinon.collection.spy(app.additionalComponents[0], 'render');
            sinon.collection.spy(app.additionalComponents[1], 'render');

            view.refreshAdditionalComponents();

            expect(app.additionalComponents[0].render).toHaveBeenCalled();
            expect(app.additionalComponents[1].render).toHaveBeenCalled();

            app.additionalComponents = originalComponents;
        });
    });

    describe('render', function() {
        it('should set logoUrl as the one from metadata', function() {
            sinon.collection.stub(app.metadata, 'getLogoUrl', function() {
                return 'LOGO_URL';
            });

            view.render();

            expect(view.logoUrl).toEqual('LOGO_URL');
        });

        it('should render additional components', function() {
            sinon.collection.spy(view, 'refreshAdditionalComponents');

            view.render();

            expect(view.refreshAdditionalComponents).toHaveBeenCalled();
        });

        it('should only show `unsupported browser` alert if browser is unsupported', function() {
            sinon.collection.spy(app.alert, 'show');
            sinon.collection.stub(view, '_isSupportedBrowser', function() {
                return false;
            });

            view.render();

            expect(app.alert.show).toHaveBeenCalledWith(view._alertKeys.unsupportedBrowser);

            view._isSupportedBrowser.restore();
            sinon.collection.stub(view, '_isSupportedBrowser', function() {
                return true;
            });

            view.render();
            expect(app.alert.show.calledOnce).toBeTruthy();
        });

        it('should show `admin only` alert if `admin_only` is set in the config', function() {
            sinon.collection.spy(app.alert, 'show');
            sinon.collection.stub(app.metadata, 'getConfig', function() {
                return {'system_status': {'level': 'admin_only'}};
            });

            view.render();

            expect(app.alert.show).toHaveBeenCalledWith(view._alertKeys.adminOnly);
        });
    });
});
