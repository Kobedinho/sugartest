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
describe('alert', function() {
    var app, alert;

    beforeEach(function() {
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.collection.restore();
        app.alert.dismissAll();
    });

    it('should render alert view', function() {
        sinon.collection.stub(app.view.AlertView.prototype, 'render');
        var dismissStub = sinon.collection.stub(app.alert, 'dismiss', function() {
            SugarTest.setWaitFlag();
        });

        var alert = app.alert.show('fubar', {level: 'info', title: 'foo', messages: 'message', autoClose: true});

        SugarTest.wait();

        runs(function() {
            expect(app.alert.getAll()).toBeDefined();
            expect(app.alert.get('fubar')).toBeDefined();
            expect(app.alert.get('fubar').key).toEqual('fubar');
            expect(alert.options).toEqual({level: 'info', title: 'foo', messages: ['message'], autoClose: true});
            expect(app.view.AlertView.prototype.render).toHaveBeenCalled();
            expect(dismissStub).toHaveBeenCalled();
        });
    });

    it('should execute callback on autoclose', function() {
        var autoCloseSpy = sinon.spy(),
            dismissStub = sinon.collection.stub(app.alert, 'dismiss', function() {
                SugarTest.setWaitFlag();
            });

        app.alert.show('fubar', {
            level: 'info',
            title: 'foo',
            messages: 'message',
            autoClose: true,
            onAutoClose: autoCloseSpy
        });

        SugarTest.wait();

        runs(function() {
            expect(autoCloseSpy).toHaveBeenCalled();
            expect(autoCloseSpy).toHaveBeenCalledWith(app.alert.get('fubar').key);
            expect(dismissStub).toHaveBeenCalled();
        });
    });

    it('should dismiss alerts', function() {
        var alert,
            spy1,
            spy2,
            clearSpy = sinon.collection.spy(window, 'clearTimeout'),
            setTimeoutSpy = sinon.collection.spy(window, 'setTimeout'),
            autoCloseDelayOverride = 2000;
        app.config.alertAutoCloseDelay = 10000;
        app.alert.show('mykey', {level: 'info', title: 'foo', messages: 'message', autoClose: true});
        app.alert.show('mykey2', {
            level: 'info',
            title: 'foo',
            messages: 'message',
            autoClose: true,
            autoCloseDelay: autoCloseDelayOverride
        });
        app.alert.show('mykey3', {level: 'info', title: 'foo', messages: 'message', autoClose: false});

        alert = app.alert.get('mykey');
        spy1 = sinon.collection.spy(alert, 'close');

        alert = app.alert.get('mykey2');
        spy2 = sinon.collection.spy(alert, 'close');

        app.alert.dismiss('mykey');
        app.alert.dismiss('mykey');
        app.alert.dismiss('mykey2');

        expect(spy1).toHaveBeenCalledOnce();
        expect(spy2).toHaveBeenCalledOnce();
        expect(app.alert.get('fubar')).toBeUndefined();
        expect(clearSpy).toHaveBeenCalledTwice();
        expect(setTimeoutSpy.firstCall.args[1]).toEqual(app.config.alertAutoCloseDelay);
        expect(setTimeoutSpy.lastCall.args[1]).toEqual(autoCloseDelayOverride);
    });

    it('should clear timeout if it already exists', function() {
        var clearSpy = sinon.collection.spy(window, 'clearTimeout');

        app.alert.show('mykey', {level: 'info', title: 'foo', messages: 'message', autoClose: true});
        expect(clearSpy).not.toHaveBeenCalled();
        app.alert.show('mykey', {level: 'info', title: 'foo', messages: 'message', autoClose: true});
        expect(clearSpy).toHaveBeenCalled();
    });

    it('should dismiss all with the given level', function() {
        var alert, s1, s2, s3;

        app.alert.show('mykey2', {level: 'error', title: 'bar', message: 'message2', autoClose: false});
        app.alert.show('mykey1', {level: 'info', title: 'foo', message: 'message1', autoClose: false});
        app.alert.show('mykey3', {level: 'error', title: 'axe', message: 'message3', autoClose: false});

        alert = app.alert.get('mykey1');
        s1 = sinon.collection.spy(alert, 'close');

        alert = app.alert.get('mykey2');
        s2 = sinon.collection.spy(alert, 'close');

        alert = app.alert.get('mykey3');
        s3 = sinon.collection.spy(alert, 'close');

        app.alert.dismissAll('error');

        expect(s1).not.toHaveBeenCalled();
        expect(s2).toHaveBeenCalled();
        expect(s3).toHaveBeenCalled();

        expect(app.alert.get('mykey1')).toBeDefined();
        expect(app.alert.get('mykey2')).toBeUndefined();
        expect(app.alert.get('mykey3')).toBeUndefined();

    });

    it('should dismiss all', function() {
        app.alert.show('mykey2', {level:'error', title:'bar', message:'message2', autoClose: false});
        app.alert.show('mykey1', {level: 'info', title: 'foo', message: 'message1', autoClose: false});
        app.alert.show('mykey3', {level: 'error', title: 'axe', message: 'message3', autoClose: false});

        app.alert.dismissAll();

        expect(app.alert.get('mykey1')).toBeUndefined();
        expect(app.alert.get('mykey2')).toBeUndefined();
        expect(app.alert.get('mykey3')).toBeUndefined();

    });

    describe('displaying multiple alerts', function() {
        var renderStub;
        beforeEach(function() {
            renderStub = sinon.collection.stub(app.view.AlertView.prototype, 'render');
        });
        it('should allow to display multiple alerts', function() {
            app.alert.show('mykey2', {level: 'error', title: 'bar', message: 'message2', autoClose: false});
            expect(renderStub).toHaveBeenCalledOnce();
            expect(app.alert.preventAnyAlert).toBeFalsy();
            app.alert.show('mykey1', {level: 'info', title: 'foo', message: 'message1', autoClose: false});
            expect(renderStub).toHaveBeenCalledTwice();
            expect(app.alert.preventAnyAlert).toBeFalsy();
            app.alert.show('mykey3', {level: 'error', title: 'axe', message: 'message3', autoClose: false});
            expect(renderStub).toHaveBeenCalledThrice();
            expect(app.alert.preventAnyAlert).toBeFalsy();
        });
        it('should prevent other alerts while confirmation is shown', function() {
            //Should show confirmation alert
            app.alert.show('fubar', {level: 'confirmation', title: 'foo', messages: 'message', autoClose: true});
            expect(app.alert.get('fubar')).toBeDefined();
            expect(renderStub).toHaveBeenCalledOnce();
            expect(app.alert.preventAnyAlert).toBeTruthy();

            //Should prevent this alert to be shown
            app.alert.show('test', {level: 'info', title: 'foo', messages: 'message', autoClose: true});
            expect(app.alert.get('test')).toBeUndefined();
            expect(renderStub).not.toHaveBeenCalledTwice();
            expect(app.alert.preventAnyAlert).toBeTruthy();

            // Test dismiss resets flag
            app.alert.dismissAll();
            expect(app.alert.preventAnyAlert).toBeFalsy();
        });
    });

    describe('test initialization', function() {
        var _alertsEl = SUGAR.App.config.alertsEl;

        afterEach(function() {
            SUGAR.App.config.alertsEl = _alertsEl;
        });

        it('should return null when there are no alerts', function() {
            SUGAR.App.config.alertsEl = '';
            app.alert.init();
            expect(app.alert.show()).toBeNull();;
        });

        it('should create alert if it does not exist', function() {
            SUGAR.App.config.alertsEl = '<html><body><div><span class="alert-wrapper">Test</span></div></body></html>';

            expect(_.keys(app.alert.getAll()).length).toEqual(0);
            app.alert.init();
            expect(_.keys(app.alert.getAll()).length).toEqual(1);
        });
    });

});
