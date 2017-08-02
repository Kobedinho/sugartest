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
describe('EmailClientLaunch Plugin', function() {
    var app, field, originalDrawer, setUseSugarClient, userPrefs;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base', 'label', 'label');
        field.plugins = ['EmailClientLaunch'];
        SugarTest.loadPlugin('EmailClientLaunch');
        SugarTest.app.plugins.attach(field, 'field');

        originalDrawer = app.drawer;
        app.drawer = {
            open: sinon.stub()
        };
        userPrefs = app.user.get('preferences');
    });

    afterEach(function() {
        field.dispose();
        app.drawer = originalDrawer;
        app.user.set('preferences', userPrefs);
    });

    setUseSugarClient = function(useSugarClient) {
        app.user.set('preferences', {email_client_preference: {type: useSugarClient ? 'sugar' : 'mailto'}});
    };

    describe('Launch Email Client', function() {
        var retrieveValidRecipientsStub;

        beforeEach(function() {
            retrieveValidRecipientsStub = sinon.stub(field, '_retrieveValidRecipients', function(recipients) {
                return recipients;
            });
        });

        afterEach(function() {
            retrieveValidRecipientsStub.restore();
        });

        it('should launch the Sugar Email Client if user profile says internal', function() {
            setUseSugarClient(true);
            field.launchEmailClient({});
            expect(app.drawer.open.callCount).toBe(1);
        });

        it('should not launch the Sugar Email Client if user profile says external', function() {
            setUseSugarClient(false);
            field.launchEmailClient({});
            expect(app.drawer.open.callCount).toBe(0);
        });

        it('should clean to, cc, and bcc recipient lists before launching Sugar Email Client', function() {
            field.launchSugarEmailClient({
                to_addresses: [{email: 'bar1@baz.com'}],
                cc_addresses: [{email: 'bar2@baz.com'}],
                bcc_addresses: [{email: 'bar3@baz.com'}]
            });
            expect(retrieveValidRecipientsStub.callCount).toBe(3);
        });
    });

    describe('Retrieve Valid Recipients', function() {
        it('should not return bean recipients that do not have a valid email address', function() {
            var emails = [
                    {email_address: 'foo1@bar.com', invalid_email: true},
                    {email_address: 'foo2@bar.com', opt_out: true}
                ],
                bean = new Backbone.Model({email: emails}),
                recipients = [{bean: bean}],
                validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients).toEqual([]);
        });

        it('should specify valid email address for bean recipients the in list', function() {
            var emails = [
                    {email_address: 'foo1@bar.com', invalid_email: true},
                    {email_address: 'foo2@bar.com', opt_out: true},
                    {email_address: 'foo3@bar.com'}
                ],
                bean = new Backbone.Model({email: emails}),
                recipients = [{bean: bean}],
                validRecipients = field._retrieveValidRecipients(recipients);

            expect(_.first(validRecipients).email).toEqual('foo3@bar.com');
            expect(_.first(validRecipients).bean).toBe(bean);
        });

        it('should leave bean recipients that have email address specified', function() {
            var emails = [
                    {email_address: 'foo1@bar.com'},
                    {email_address: 'foo2@bar.com'}
                ],
                bean = new Backbone.Model({email: emails}),
                recipients = [{
                    email: 'abc@bar.com',
                    bean: bean
                }],
                validRecipients = field._retrieveValidRecipients(recipients);

            expect(_.first(validRecipients).email).toEqual('abc@bar.com');
            expect(_.first(validRecipients).bean).toBe(bean);
        });

        it('should leave plain recipients that just have email address specified', function() {
            var recipients = {
                    email: 'abc@bar.com'
                },
                validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients).toEqual([recipients]);
        });
    });

    describe('Should Add Email Options', function() {
        it('should set a copy of the related model in email options', function() {
            var createBeanStub = sinon.stub(app.data, 'createBean', function() {
                    var bean = new Backbone.Model();
                    bean.copy = function(copyFrom) {
                        bean.set('foo', copyFrom.get('foo'));
                    };
                    return bean;
                }),
                module = 'Contacts',
                model = app.data.createBean(module);

            model.set('id', '123');
            model.set('foo', 'bar');
            model.module = module;
            field.addEmailOptions({related: model});
            expect(field.emailOptions.related).not.toBe(model);
            expect(field.emailOptions.related.toJSON()).toEqual(model.toJSON());
            createBeanStub.restore();
        });

        it('should not specify related on email options if model specified has no module', function() {
            field.addEmailOptions({related: new Backbone.Model()});
            expect(field.emailOptions.related).toBeUndefined();
        });

        it('should overlay email options with new values, not replace the whole set', function() {
            field.emailOptions = {foo: 'bar', bar: 'foo'};
            field.addEmailOptions({bar: 'yes', baz: 'no'});
            expect(field.emailOptions).toEqual({
                foo: 'bar',
                bar: 'yes',
                baz: 'no'
            });
        });
    });

    describe('Build mailto: Url', function() {
        it('should return an empty mailto if no options passed', function() {
            var url = field._buildMailToURL({});
            expect(url).toBe('mailto:');
        });

        it('should return mailto with only to address', function() {
            var email1 = 'foo@bar.com',
                email2 = 'foo2@bar.com',
                url = field._buildMailToURL({
                    to_addresses: [
                        {email: email1},
                        {email: email2}
                    ]
                });
            expect(url).toEqual('mailto:' + email1 + ',' + email2);
        });

        it('should return mailto with cc and bcc addresses in querystring', function() {
            var email1 = 'foo@bar.com',
                email2 = 'foo2@bar.com',
                url = field._buildMailToURL({
                    cc_addresses: [
                        {email: email1}
                    ],
                    bcc_addresses: [
                        {email: email2}
                    ]
                }),
                expectedParams = {
                    cc: email1,
                    bcc: email2
                };
            expect(url).toEqual('mailto:?' + $.param(expectedParams));
        });

        it('should return mailto with subject and text body in querystring', function() {
            var expectedParams = {
                    subject: 'Foo',
                    body: 'Bar!'
                },
                url = field._buildMailToURL({
                    subject: expectedParams.subject,
                    text_body: expectedParams.body,
                    html_body: '<b>' + expectedParams.body + '</b>'
                });
            expect(url).toEqual('mailto:?' + $.param(expectedParams));
        });
    });

    describe('Format Recipients To String', function() {
        it('should return an empty string if no recipients', function() {
            var actual = field._formatRecipientsToString([]);
            expect(actual).toEqual('');
        });

        it('should return a single address if only email string passed in', function() {
            var expected = 'foo@bar.com',
                actual = field._formatRecipientsToString(expected);
            expect(actual).toEqual(expected);
        });

        it('should return emails passed in different forms', function() {
            var email1 = 'foo1@bar.com',
                email2 = 'foo2@bar.com',
                email3 = 'foo3@bar.com',
                bean = new Backbone.Model({email: [{email_address: email3}]}),
                actual = field._formatRecipientsToString([
                    email1,
                    {email: email2},
                    {bean: bean}
                ]);
            expect(actual).toEqual(email1 + ',' + email2 + ',' + email3);
        });

        it('should not return emails in bean form if no valid email on bean', function() {
            var email1 = 'foo1@bar.com',
                email2 = 'foo2@bar.com',
                bean = new Backbone.Model({email: [{
                    email_address: email2,
                    invalid_email: true
                }]}),
                actual = field._formatRecipientsToString([
                    email1,
                    {bean: bean}
                ]);
            expect(actual).toEqual(email1);
        });
    });

    describe('Retrieving Email Address From Model', function() {
        it('should return undefined if no email field on the model', function() {
            var actual = field._retrieveEmailAddressFromModel(new Backbone.Model());
            expect(actual).toBeUndefined();
        });

        it('should return primary email address from model when valid', function() {
            var email1 = 'foo1@bar.com',
                email2 = 'foo2@bar.com',
                model = new Backbone.Model({email: [
                    {
                        email_address: email1
                    },
                    {
                        email_address: email2,
                        primary_address: true
                    }
                ]}),
                actual = field._retrieveEmailAddressFromModel(model);
            expect(actual).toEqual(email2);
        });

        it('should return first valid email address from model when primary is invalid', function() {
            var email1 = 'foo1@bar.com',
                email2 = 'foo2@bar.com',
                model = new Backbone.Model({email: [
                    {
                        email_address: email1
                    },
                    {
                        email_address: email2,
                        primary_address: true,
                        opt_out: true
                    }
                ]}),
                actual = field._retrieveEmailAddressFromModel(model);
            expect(actual).toEqual(email1);
        });

        it('should return undefined if no valid emails on the model', function() {
            var email1 = 'foo1@bar.com',
                email2 = 'foo2@bar.com',
                model = new Backbone.Model({email: [
                    {
                        email_address: email1,
                        invalid_email: true
                    },
                    {
                        email_address: email2,
                        primary_address: true,
                        opt_out: true
                    }
                ]}),
                actual = field._retrieveEmailAddressFromModel(model);
            expect(actual).toBeUndefined();
       });
    });

    describe('Retrieving Email Options', function() {
        it('should return empty object if no options on link or controller', function() {
            var actual,
                $link = $('<a href="#">Foo!</a>');

            field.emailOptions = undefined;
            actual = field._retrieveEmailOptions($link);
            expect(actual).toEqual({});
        });

        it('should return options from controller combined with options from link', function() {
            var actual,
                $link = $('<a href="#">Foo!</a>');

            $link.data({
                to_addresses: 'foo@bar.com',
                subject: 'Bar!!!'
            });
            field.emailOptions = {
                cc_addresses: 'foo2@bar.com',
                subject: 'Bar'
            };
            actual = field._retrieveEmailOptions($link);
            expect(actual).toEqual({
                to_addresses: 'foo@bar.com',
                cc_addresses: 'foo2@bar.com',
                subject: 'Bar!!!'
            });

        });
    });

    describe('Setting email links on attach', function() {
        it('should set href to mailto link on render if client is external', function() {
            setUseSugarClient(false);
            field.$el = $('<div><a href="#" data-action="email">Foo</a></div>');
            field.trigger('render');
            expect(field.$('a').attr('href')).toEqual('mailto:');
        });

        it('should set href to void link on render if client is internal', function() {
            setUseSugarClient(true);
            field.$el = $('<div><a href="#" data-action="email">Foo</a></div>');
            field.trigger('render');
            expect(field.$('a').attr('href')).toEqual('javascript:void(0)');
        });
    });
});
