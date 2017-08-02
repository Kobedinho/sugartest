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
describe('Core.BeforeEvent', function() {
    var app, view;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
        var Mock = Backbone.View.extend({
            render: function() {
                if (!this.triggerBefore('render')) {
                    return false;
                }
                this.trigger('render');
            },

            hide: function() {
                if (!this.triggerBefore('hide')) {
                    return false;
                }
                this.trigger('hide');
            },

            dispose: function() {}
        });
        _.extend(Mock.prototype, app.beforeEvent);
        view = new Mock();
    });

    afterEach(function() {
        sinon.collection.restore();
        view = null;
    });

    describe('before', function() {
        it('should bind a before callback function', function() {
            var cb = sinon.collection.spy();
            view.before('render', cb);
            view.render();

            expect(cb).toHaveBeenCalled();
        });

        it('should bind a callback with a supplied context', function() {
            var ctx = {
                method: function() {
                    expect(this).toBe(ctx);
                }
            };

            sinon.collection.stub(ctx, 'method');

            view.before('render', ctx.method, ctx);
            view.render();

            expect(ctx.method).toHaveBeenCalled();
        });


        it('should bind a before callback function for multiple events', function() {
            var cb = sinon.collection.spy();
            view.before('render hide', cb);

            view.render();
            expect(cb).toHaveBeenCalledOnce();

            view.hide();
            expect(cb).toHaveBeenCalledTwice();
        });

        it('should accept an event map to bind before callbacks', function() {
            var cb = sinon.collection.spy();
            view.before({
                'render': cb,
                'hide': cb
            });

            view.render();
            expect(cb).toHaveBeenCalledOnce();

            view.hide();
            expect(cb).toHaveBeenCalledTwice();
        });

        it('should bind the before callback with a provided scope', function() {
            var context = {};
            var cb = function() {
                this.foo = 'bar';
            };

            view.before('render', cb, context);
            view.render();

            expect(context.foo).toEqual('bar');
        });

        it('should not render when before render is false, even when one callback returns true', function() {
            var renderSpy = sinon.spy(view, 'trigger').withArgs('render');
            var cb1 = sinon.stub().returns(false);
            var cb2 = sinon.stub().returns(true);

            view.before('render', cb1);
            view.before('render', cb2);
            view.render();

            expect(renderSpy).not.toHaveBeenCalled();
            expect(cb1).toHaveBeenCalled();
            expect(cb2).toHaveBeenCalled();
        });
    });

    describe('triggerBefore', function() {
        it('should execute the before callback', function() {
            var cb = sinon.collection.spy();

            view.before('render', cb);
            var result = view.triggerBefore('render');

            expect(cb).toHaveBeenCalled();
            expect(result).toBeTruthy();
        });

        it('should execute multiple before callbacks prior to multiple events', function() {
            var cb1 = sinon.collection.stub().returns({foo: 'bar'});
            var cb2 = sinon.collection.stub().returns({bar: 'foo'});

            view.before({
                'render': cb1,
                'hide': cb2
            });
            var result = view.triggerBefore('render hide');

            expect(cb1).toHaveBeenCalled();
            expect(cb2).toHaveBeenCalled();
            expect(result).toBeTruthy();
        });

        it('should receive custom arguments in the before callback', function() {
            var cb = sinon.collection.spy();

            view.before('render', cb);
            var result = view.triggerBefore('render', {foo: 'bar'}, {bar: 'foo'}, 0, 'a');

            expect(cb).toHaveBeenCalledWith({foo: 'bar'}, {bar: 'foo'}, 0, 'a');
            expect(result).toBeTruthy();
        });

        it('should execute before callbacks on all events', function() {
            var cb = sinon.collection.spy();

            view.before('all', cb);
            var result = view.triggerBefore('render hide');

            expect(cb).toHaveBeenCalled();
            expect(result).toBeTruthy();
        });

        it('should execute before callbacks on all events, even if a callback returns false', function() {
            var cb = sinon.collection.spy();

            view.before('all', cb);
            view.before('render', function() {
                return false;
            });

            var result = view.triggerBefore('render hide');

            expect(cb).toHaveBeenCalled();
            expect(result).toBeFalsy();
        });

        it('should execute before callbacks on all events, even if the `all` callback returns false', function() {
            var cb = sinon.collection.spy();

            view.before('all', function() {
                return false;
            });
            view.before('render', cb);

            var result = view.triggerBefore('render hide');

            expect(cb).toHaveBeenCalled();
            expect(result).toBeFalsy();
        });
    });

    describe('offBefore', function() {
        it('should remove the specified callback from a before event and nothing else', function() {
            var cb1 = sinon.collection.stub().returns({foo: 'bar'});
            var cb2 = sinon.collection.stub().returns({bar: 'foo'});

            view.before('render', cb1);
            view.before('render', cb2);
            view.offBefore('render', cb1);

            view.render();
            expect(cb1).not.toHaveBeenCalled();
            expect(cb2).toHaveBeenCalled();
        });

        it('should remove all callbacks from a specified context', function() {
            var cb1 = sinon.collection.stub().returns({foo: 'bar'});
            var cb2 = sinon.collection.stub().returns({bar: 'foo'});
            var observer = {};

            view.before('render', cb1, observer);
            view.before('render', cb2);
            view.offBefore(null, null, observer);

            view.render();
            expect(cb1).not.toHaveBeenCalled();
            expect(cb2).toHaveBeenCalled();
        });

        it('should remove all callbacks from a before event if none supplied', function() {
            var cb1 = sinon.collection.stub().returns({foo: 'bar'});
            var cb2 = sinon.collection.stub().returns({bar: 'foo'});

            view.before('render', cb1);
            view.before('render', cb2);
            view.before('hide', cb1);
            view.before('hide', cb2);

            view.offBefore('render');

            // This shouldn't affect the event listeners.
            view.offBefore('blah');

            view.render();
            expect(cb1).not.toHaveBeenCalled();
            expect(cb2).not.toHaveBeenCalled();

            view.hide();
            expect(cb1).toHaveBeenCalled();
            expect(cb2).toHaveBeenCalled();
        });

        it('should remove all callbacks for all events', function() {
            var cb = sinon.collection.spy();
            view.before('render hide dispose', cb);

            view.offBefore();

            view.render();
            view.hide();
            view.dispose();
            expect(cb).not.toHaveBeenCalled();
        });
    });
});
