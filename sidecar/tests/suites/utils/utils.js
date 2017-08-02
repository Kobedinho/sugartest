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
describe('Utils', function() {

    var utils = SUGAR.App.utils;
    var user = SUGAR.App.user;

    var app;
    beforeEach(function() {
        app = SugarTest.app;
    });

    describe('strings', function() {
        it('should capitalize a string', function() {
            var result = utils.capitalize('abc');
            expect(result).toEqual('Abc');
            result = utils.capitalize('a');
            expect(result).toEqual('A');
            result = utils.capitalize('aBC');
            expect(result).not.toEqual('Abc');
            expect(result).toEqual('ABC');//preserves subsequent chars
        });

        it('should return empty string from capitalize for falsy input', function() {
            var result = utils.capitalize(undefined);
            expect(result).toEqual('');
            result = utils.capitalize(null);
            expect(result).toEqual('');
            result = utils.capitalize();
            expect(result).toEqual('');
        });

        it('should capitalize hyphenated strings', function() {
            var result = utils.capitalizeHyphenated('abc-def');
            expect(result).toEqual('AbcDef');
            result = utils.capitalizeHyphenated('a');
            expect(result).toEqual('A');
            result = utils.capitalizeHyphenated('aBC-dEF');
            expect(result).not.toEqual('AbcDef');
            expect(result).toEqual('ABCDEF');//preserves subsequent chars
        });

        it('should return empty string from capitalizeHyphenated for falsy input', function() {
            var result = utils.capitalizeHyphenated(undefined);
            expect(result).toEqual('');
            result = utils.capitalizeHyphenated(null);
            expect(result).toEqual('');
            result = utils.capitalizeHyphenated();
            expect(result).toEqual('');
        });
    });

    describe('string formatter', function() {
        it('should insert string substitutions', function() {
            var string = utils.formatString('Hello {0}, would you like to look at {1}?', ['User', 'an Account']);
            expect(string).toEqual('Hello User, would you like to look at an Account?');
        });

        it('should allow unused and partial arguments', function() {
            var string = utils.formatString('Hello World', ['User', 'an Account']);
            expect(string).toEqual('Hello World');
            string = utils.formatString('Hello {0}, would you like to look at {1}?', ['User']);
            expect(string).toEqual('Hello User, would you like to look at {1}?');
        });

        it('should allow null or undefined arguments', function() {
            var string = utils.formatString('Hello computer?', null);
            expect(string).toEqual('Hello computer?');
            string = utils.formatString('Computer?  Are you there?');
            expect(string).toEqual('Computer?  Are you there?');
        });

    });

    describe('number formatter', function() {
        it('should round up numbers', function() {
            var value = 2.3899;
            var round = 2;
            var precision = 2;
            var number_group_separator = ',';
            var decimal_separator = '.';
            var result = utils.formatNumber(value, round, precision, number_group_separator, decimal_separator);

            expect(result).toEqual('2.39');
        });

        it('should round down numbers', function() {
            var value = 2.3822;
            var round = 2;
            var precision = 2;
            var number_group_separator = ',';
            var decimal_separator = '.';
            var result = utils.formatNumber(value, round, precision, number_group_separator, decimal_separator);
            expect(result).toEqual('2.38');
        });

        it('should set precision on numbers', function() {
            var value = 2.3828;
            var round = 4;
            var precision = 2;
            var number_group_separator = ',';
            var decimal_separator = '.';
            var result = utils.formatNumber(value, round, precision, number_group_separator, decimal_separator);
            expect(result).toEqual('2.38');
        });

        it('should add the correct number group separator', function() {
            var value = 2123.3828;
            var round = 4;
            var precision = 2;
            var number_group_separator = ' ';
            var decimal_separator = '.';
            var result = utils.formatNumber(value, round, precision, number_group_separator, decimal_separator);
            expect(result).toEqual('2 123.38');
        });

        it('should return non-number objects without modification', function() {
            var value = [1, 2, 3];
            var round = 2;
            var precision = 2;
            var number_group_separator = '';
            var decimal_separator = '.';
            var result = utils.formatNumber(value, round, precision, number_group_separator, decimal_separator);
            expect(result).toEqual([1, 2, 3]);
            value = undefined;
            result = utils.formatNumber(value, round, precision, number_group_separator, decimal_separator);
            expect(result).toBeUndefined();
            value = null;
            result = utils.formatNumber(value, round, precision, number_group_separator, decimal_separator);
            expect(result).toEqual(null);
            value = NaN;
            result = utils.formatNumber(value, round, precision, number_group_separator, decimal_separator);
            expect(_.isNaN(result)).toBeTruthy();
        });

        it('should add the correct decimal separator', function() {
            var value = 2123.3828;
            var round = 4;
            var precision = 2;
            var number_group_separator = '';
            var decimal_separator = ',';
            var result = utils.formatNumber(value, round, precision, number_group_separator, decimal_separator);
            expect(result).toEqual('2123,38');
        });

        it('should format number strings to formatted number strings', function() {
            var value = '2123.3828';
            var round = 4;
            var precision = 2;
            var number_group_separator = '';
            var decimal_separator = '.';
            var result = utils.formatNumber(value, round, precision, number_group_separator, decimal_separator);
            expect(result).toEqual('2123.38');
        });

        it('should return any invalid number strings without modification', function() {
            var value = '$2123.3828';
            var round = 4;
            var precision = 2;
            var number_group_separator = '';
            var decimal_separator = '.';
            var result = utils.formatNumber(value, round, precision, number_group_separator, decimal_separator);
            expect(result).toEqual('$2123.3828');
            value = '..54';
            result = utils.formatNumber(value, round, precision, number_group_separator, decimal_separator);
            expect(result).toEqual('..54');
            value = 'abcdefg';
            result = utils.formatNumber(value, round, precision, number_group_separator, decimal_separator);
            expect(result).toEqual('abcdefg');
        });

        it('should unformat number strings to unformatted number strings', function() {
            var value = '2,123 3828';
            var number_group_separator = ',';
            var decimal_separator = ' ';
            var toFloat = false;
            var result = utils.unformatNumberString(value, number_group_separator, decimal_separator, toFloat);
            expect(result).toEqual('2123.3828');
        });

        it('should unformat number strings to floats', function() {
            var value = '2,123 3828';
            var number_group_separator = ',';
            var decimal_separator = ' ';
            var toFloat = true;
            var result = utils.unformatNumberString(value, number_group_separator, decimal_separator, toFloat);
            expect(result).toEqual(2123.3828);
        });

        it("should return an empty value for ''", function() {
            var value = '';
            var number_group_separator = ',';
            var decimal_separator = ' ';
            var toFloat = true;
            var result = utils.unformatNumberString(value, number_group_separator, decimal_separator, toFloat);
            expect(result).toEqual('');
        });

        it('should return an empty value for null', function() {
            var value = null;
            var number_group_separator = ',';
            var decimal_separator = '.';
            var toFloat = false;
            var result = utils.unformatNumberString(value, number_group_separator, decimal_separator, toFloat);
            expect(result).toEqual('');
        });

        it('should return an empty value for undefined', function() {
            var value;
            var number_group_separator = ',';
            var decimal_separator = '.';
            var toFloat = false;
            var result = utils.unformatNumberString(value, number_group_separator, decimal_separator, toFloat);
            expect(result).toEqual('');
        });

        it('should return an empty value for NaN', function() {
            var value = NaN;
            var number_group_separator = ',';
            var decimal_separator = '.';
            var toFloat = false;
            var result = utils.unformatNumberString(value, number_group_separator, decimal_separator, toFloat);
            expect(result).toEqual('');
        });

        it('should strip not strip out invalid chars and return original value', function() {
            var value = '135abc456.ab23';
            var number_group_separator = ',';
            var decimal_separator = '.';
            var toFloat = true;
            var result = utils.unformatNumberString(value, number_group_separator, decimal_separator, toFloat);
            expect(result).toEqual('135abc456.ab23');
        });
    });

    describe('formatting with locale', function() {
        it('should format a number respecting user locale', function() {
            user.set('decimal_precision', 2);
            user.set('decimal_separator', '.');
            user.set('number_grouping_separator', ',');
            var amount = '1000';
            var result = utils.formatNumberLocale(amount, false);
            expect(result).toEqual('1,000.00');
        });

        it('should unformat a number respecting user locale', function() {
            user.set('decimal_precision', 2);
            user.set('decimal_separator', '.');
            user.set('number_grouping_separator', ',');
            var amount = '1,000.00';
            var result = utils.unformatNumberStringLocale(amount, false);
            expect(result).toEqual('1000.00');
        });
    });

    describe('Name formatter', function() {
        var params = {
            first_name: 'foo',
            last_name: 'boo',
            salutation: 'Mr.'
        };
        using('possible name formats', [{
            format: 'f s l',
            expected: 'foo Mr. boo'
        },{
            format: 's f l',
            expected: 'Mr. foo boo'
        },{
            format: 'f l',
            expected: 'foo boo'
        },{
            format: 's l',
            expected: 'Mr. boo'
        },{
            format: 'l, f',
            expected: 'boo, foo'
        },{
            format: 's l, f',
            expected: 'Mr. boo, foo'
        },{
            format: 'l s f',
            expected: 'boo Mr. foo'
        },{
            format: 'l f s',
            expected: 'boo foo Mr.'
        }], function(value) {
            it('should follow the naming format with name parts', function() {
                var result = utils.formatName(params, value.format);
                expect(result).toEqual(value.expected);
            });
        });

        describe('comma separator', function() {
            var params = {
                    first_name: 'foo',
                    last_name: 'boo',
                    salutation: 'Dr.'
                };
            using('possible name formats', [{
                format: 'l, f',
                expected: 'boo, foo'
            },{
                format: 's l, f',
                expected: 'Dr. boo, foo'
            }], function(value) {
                it('should print the format with comma separator when the format is provided', function() {
                    var result = utils.formatName(params, value.format);
                    expect(result).toEqual(value.expected);
                });
            });

            var params2 = {
                first_name: 'foo',
                last_name: ''
            };
            using('possible name formats', [{
                format: 'l, f',
                expected: 'foo'
            },{
                format: 's l, f',
                expected: 'foo'
            }], function(value) {
                it('should print only first name when last name is not provided', function() {
                    var result = utils.formatName(params2, value.format);
                    expect(result).toEqual(value.expected);
                });
            });
        });

        describe('trim', function() {
            var params = {
                first_name: '',
                last_name: 'boo',
                salutation: 'Dr.'
            };
            using('possible name formats', [{
                format: 'f s l',
                expected: 'Dr. boo'
            },{
                format: 's f l',
                expected: 'Dr. boo'
            },{
                format: 'f l',
                expected: 'boo'
            },{
                format: 's l',
                expected: 'Dr. boo'
            },{
                format: 'l, f',
                expected: 'boo'
            },{
                format: 's l, f',
                expected: 'Dr. boo'
            },{
                format: 'l s f',
                expected: 'boo Dr.'
            },{
                format: 'l f s',
                expected: 'boo Dr.'
            }], function(value) {
                it('should trim the space when some name parts are not provided', function() {
                    var result = utils.formatName(params, value.format);
                    expect(result).toEqual(value.expected);
                });
            });
        });
    });

    describe('formatting name with locale', function() {
        it('should format a number respecting user locale', function() {
            user.setPreference('default_locale_name_format', 's l, f');
            var params = {
                    first_name: 'foo',
                    last_name: 'boo',
                    salutation: 'Dr.'
                };
            var result = utils.formatNameLocale(params);
            expect(result).toEqual('Dr. boo, foo');

            user.setPreference('default_locale_name_format', 'f s l');
            result = utils.formatNameLocale(params);
            expect(result).toEqual('foo Dr. boo');
        });
    });

    describe('regex escape', function() {
        it('should escape string for use in regex', function() {
            var string = 'abc*123';
            var result = utils.regexEscape(string);
            expect(result).toEqual('abc\\*123');
            string = '/.*+?|()[]{}\\-.^$#';
            result = utils.regexEscape(string);
            expect(result).toEqual('\\/\\.\\*\\+\\?\\|\\(\\)\\[\\]\\{\\}\\\\\\-\\.\\^\\$\\#');
        });
    });

    describe('cookie', function() {
        it('should set cookie values', function() {
            var result = '';
            var cName, value, i, x, y;
            var ARRcookies = document.cookie.split(';');
            cName = 'sidecarCookie';
            value = 'asdf';
            SUGAR.App.utils.cookie.setCookie(cName, value, 1);

            ARRcookies = document.cookie.split(';');
            for (i = 0; i < ARRcookies.length; i++) {
                x = ARRcookies[i].substr(0, ARRcookies[i].indexOf('='));
                y = ARRcookies[i].substr(ARRcookies[i].indexOf('=') + 1);
                x = x.replace(/^\s+|\s+$/g, '');
                if (x === cName) {
                    result = unescape(y);
                }
            }
            expect(result).toEqual(value);
            SUGAR.App.utils.cookie.setCookie(cName, '', 1);
        });

        it('should get cookie values', function() {
            var result = '';
            var cName = 'sidecarCookie';
            var value = 'asdfasdf';
            var exdays = 1;
            var exdate = new Date(), c_value;
            exdate.setDate(exdate.getDate() + exdays);
            c_value = escape(value) + ((exdays === null) ? '' : '; expires=' + exdate.toUTCString());
            document.cookie = cName + '=' + c_value;
            result = SUGAR.App.utils.cookie.getCookie(cName);
            expect(result).toEqual(value);
            value = '';
            c_value = escape(value) + ((exdays === null) ? '' : '; expires=' + exdate.toUTCString());
        });
    });

    describe('isValidEmailAddress', function() {
        it('should accept e-mail addresses with capitals (bug55676)', function() {
            var result = utils.isValidEmailAddress('aBc@abc.com');
            expect(result).toEqual(true);
            result = utils.isValidEmailAddress('abc@aBc.com');
            expect(result).toEqual(true);
            result = utils.isValidEmailAddress('abc@abc.cOm');
            expect(result).toEqual(true);
            result = utils.isValidEmailAddress('ABC@ABC.COM');
            expect(result).toEqual(true);
        });

        it('should reject invalid e-mail addresses', function() {
            var result = utils.isValidEmailAddress('@abc.com');
            expect(result).toEqual(false);
            result = utils.isValidEmailAddress('abc@');
            expect(result).toEqual(false);
            result = utils.isValidEmailAddress('');
            expect(result).toEqual(false);
            result = utils.isValidEmailAddress('abc');
            expect(result).toEqual(false);
            result = utils.isValidEmailAddress('abc @aBc.com');
            expect(result).toEqual(false);
            result = utils.isValidEmailAddress('abc@ aBc.com');
            expect(result).toEqual(false);
            result = utils.isValidEmailAddress('no spaces allowed in local part unless quoted@aBc.com');
            expect(result).toEqual(false);
            result = utils.isValidEmailAddress('abc@aBc nospaceindomain.com');
            expect(result).toEqual(false);
            result = utils.isValidEmailAddress('abc @ aBc.com');
            expect(result).toEqual(false);
        });

        it('should accept valid e-mail addresses', function() {
            var result = utils.isValidEmailAddress('abc@abc.com');
            expect(result).toEqual(true);
            result = utils.isValidEmailAddress('abc@def');
            expect(result).toEqual(true);
            result = utils.isValidEmailAddress('abfc@blaha.netso');
            expect(result).toEqual(true);
            result = utils.isValidEmailAddress('blah.blah@blah.blah.blah.net');
            expect(result).toEqual(true);
            result = utils.isValidEmailAddress('this.with+symbol@blah.com');
            expect(result).toEqual(true);
            result = utils.isValidEmailAddress('"this local part can have embedded spaces if quoted"@aBc.com');
            expect(result).toEqual(true);
            result = utils.isValidEmailAddress('"()<>[]:,;@\\\"!#$%&-/=?^_`{}| ~.a"@blah.com');
            expect(result).toEqual(true);
            result = utils.isValidEmailAddress('#!$%&\'*+-/=?^_`{}|~@blah.com');
            expect(result).toEqual(true);
            result = utils.isValidEmailAddress('" "@example.com');
            expect(result).toEqual(true);
            result = utils.isValidEmailAddress('"very.unusual.@.unusual.com"@example.com');
            expect(result).toEqual(true);
            result = utils.isValidEmailAddress('"Here.(),:;<>[]\".IS-A.\"crazy@\\ \"butvalid\".address"@blah.com');
            expect(result).toEqual(true);
            result = utils.isValidEmailAddress('user@[192.168.25.36]');
            expect(result).toEqual(true);
            result = utils.isValidEmailAddress('ipv6user@[IPv6:2001:0db8:85a3:0000:0000:8a2e:0370:7334]');
            expect(result).toEqual(true);
        });

    });

    describe('doWhen', function() {
        it('should accept strings as a condition', function() {
            var fired = false;
            utils.doWhen('SUGAR.TEST_GLOBAL_VARIABLE', function() {
                fired = true;
            });
            utils._doWhenCheck();
            expect(fired).toBeFalsy();
            SUGAR.TEST_GLOBAL_VARIABLE = true;
            // force the doWhen check since we can't rely on timing.
            utils._doWhenCheck();
            expect(fired).toBeTruthy();
            delete SUGAR.TEST_GLOBAL_VARIABLE;
        });

        it('should accept a condition function', function() {
            var go = false;
            var fired = false;
            utils.doWhen(function() { return go; }, function() {
                fired = true;
            });
            utils._doWhenCheck();
            expect(fired).toBeFalsy();
            go = true;
            utils._doWhenCheck();
            expect(fired).toBeTruthy();
        });

        it('should pass parameters to the callback', function() {
            var params = {foo: true};
            var fired = false;

            utils.doWhen('true', function(p) {
                fired = p.foo;
            }, params);
            utils._doWhenCheck();
            expect(fired).toBeTruthy();
        });

        it('should set the context correct', function() {
            var params = {foo: true};
            var fired = false;

            utils.doWhen('true', function(p) {
                expect(p).toBeNull();
                fired = this.foo;
            }, null, params);
            utils._doWhenCheck();
            expect(fired).toBeTruthy();
        });

        it("should use params as the context when context is 'true' ", function() {
            var params = {foo: true};
            var fired = false;

            utils.doWhen('true', function(p) {
                // ensure the p is still set even though it is also the context
                expect(p).toBeTruthy();
                fired = this.foo;
            }, params, true);
            utils._doWhenCheck();
            expect(fired).toBeTruthy();
        });
    });

    describe('deepCopy', function() {
        it('should return an object with same values', function() {
            var input = {
                foo: 'foo',
                bar: 'bar',
                test: {
                    foo: 'foo',
                    bar: 'bar'
                }
            };
            var output = utils.deepCopy(input);

            expect(output.foo).toBe(input.foo);
            expect(output.bar).toBe(input.bar);
            expect(output.test.foo).toBe(input.test.foo);
            expect(output.test.bar).toBe(input.test.bar);
        });

        it('should return an object that has a different reference', function() {
            var input = {
                foo: 'foo',
                bar: 'bar',
                test: {
                    foo: 'foo',
                    bar: 'bar'
                }
            };

            expect(utils.deepCopy(input)).not.toBe(input);
        });

        it('should return copied object attributes with a different reference', function() {
            var input = {
                foo: 'foo',
                bar: 'bar',
                test: {
                    foo: 'foo',
                    bar: 'bar'
                }
            };
            var output = utils.deepCopy(input);

            expect(output.test).not.toBe(input.test);
        });

        it('should return a copy of the parameter if not an object', function() {
            var input = 'foo';
            var output = utils.deepCopy(input);
            expect(output).toBe(input);
        });
    });

    describe('compareBeans', function() {
        var module = 'Cases';

        beforeEach(function() {
            SugarTest.seedMetadata();
            SugarTest.app.data.declareModel(module, SugarTest.metadata.modules[module]);
        });

        it('should return the names of fields that have different values', function() {
            var actual;
            var bean1 = SugarTest.app.data.createBean(module, {
                account_name: 'foo'
            });
            var bean2 = SugarTest.app.data.createBean(module, {
                account_name: 'bar'
            });

            actual = utils.compareBeans(bean1, bean2);

            expect(_.size(actual)).toBe(1);
            expect(actual[0]).toBe('account_name');
        });

        it('should consider objects to be equal when just the order of the attributes differ', function() {
            var actual;
            var bean1 = SugarTest.app.data.createBean(module, {
                account_name: 'foo',
                opportunity_role: {
                    id: 123,
                    label: 'LBL_TEST'
                }
            });
            var bean2 = SugarTest.app.data.createBean(module, {
                account_name: 'bar',
                opportunity_role: {
                    label: 'LBL_TEST',
                    id: 123
                }
            });

            actual = utils.compareBeans(bean1, bean2);

            expect(_.size(actual)).toBe(1);
        });

        it('should not compare id field', function() {
            var actual;
            var bean1 = SugarTest.app.data.createBean(module, {
                id: 'foo'
            });
            var bean2 = SugarTest.app.data.createBean(module, {
                id: 'bar'
            });

            actual = utils.compareBeans(bean1, bean2);

            expect(_.size(actual)).toBe(0);
        });

        it('should not compare fields that starts with underscore', function() {
            var actual;
            var bean1 = SugarTest.app.data.createBean(module, {
                _foo: 'foo'
            });
            var bean2 = SugarTest.app.data.createBean(module, {
                _foo: 'bar'
            });

            bean1.fields._foo = {
                name: '_foo'
            };

            actual = utils.compareBeans(bean1, bean2);

            expect(_.size(actual)).toBe(0);
        });

        it('should not compare default values if it has not been changed', function() {
            var actual;
            var bean1 = SugarTest.app.data.createBean(module, {
                name: 'test'
            });
            var bean2 = SugarTest.app.data.createBean(module, {
                name: '123'
            });

            bean1._defaults = {
                name: 'test'
            };

            actual = utils.compareBeans(bean1, bean2);

            expect(_.size(actual)).toBe(0);
        });

        it('should compare default values if it has changed', function() {
            var actual;
            var bean1 = SugarTest.app.data.createBean(module, {
                name: 'test123'
            });
            var bean2 = SugarTest.app.data.createBean(module, {
                name: '123'
            });

            bean1._defaults = {
                name: 'test'
            };

            actual = utils.compareBeans(bean1, bean2);

            expect(_.size(actual)).toBe(1);
        });
    });

    describe('hasDefaultValueChanged', function() {
        var module = 'Cases';

        beforeEach(function() {
            SugarTest.seedMetadata();
            SugarTest.app.data.declareModel(module, SugarTest.metadata.modules[module]);
        });

        it('should return true if the value has changed from the default value', function() {
            var actual;
            var bean = SugarTest.app.data.createBean(module, {
                name: 'bar'
            });

            bean._defaults = {
                name: 'test'
            };

            actual = utils.hasDefaultValueChanged('name', bean);

            expect(actual).toBe(true);
        });

        it('should return false if the value has not changed from the default value', function() {
            var actual;
            var bean = SugarTest.app.data.createBean(module, {
                name: 'test'
            });

            bean._defaults = {
                name: 'test'
            };

            actual = utils.hasDefaultValueChanged('name', bean);

            expect(actual).toBe(false);
        });

        it('should return true if the value has been set but no default value is given', function() {
            var actual;
            var bean = SugarTest.app.data.createBean(module, {
                name: 'test'
            });

            actual = utils.hasDefaultValueChanged('name', bean);

            expect(actual).toBe(true);
        });

        it('should return false if the value has not been set and no default value is given', function() {
            var actual;
            var bean = SugarTest.app.data.createBean(module);

            actual = utils.hasDefaultValueChanged('name', bean);

            expect(actual).toBe(false);
        });
    });

    describe('areBeanValuesEqual', function() {
        it('should return true if two strings are equal', function() {
            var actual = utils.areBeanValuesEqual('foo', 'foo');
            expect(actual).toBe(true);
        });

        it('should return true if two numbers are equal', function() {
            var actual = utils.areBeanValuesEqual(123, 123);
            expect(actual).toBe(true);
        });

        it('should return true if two booleans are equal', function() {
            expect(utils.areBeanValuesEqual(true, true)).toBe(true);
            expect(utils.areBeanValuesEqual(false, false)).toBe(true);
        });

        it('should return true if two arrays are equal', function() {
            var actual = utils.areBeanValuesEqual(['1', '2'], ['1', '2']);
            expect(actual).toBe(true);
        });

        it('should return true if two objects are equal', function() {
            var actual = utils.areBeanValuesEqual({one: 'one', two: 'two'}, {one: 'one', two: 'two'});
            expect(actual).toBe(true);
        });

        it('should return true if two values are null', function() {
            var actual = utils.areBeanValuesEqual(null, null);
            expect(actual).toBe(true);
        });

        it('should return true if the values are undefined and empty string', function() {
            var actual = utils.areBeanValuesEqual(undefined, '');
            expect(actual).toBe(true);
        });

        it('should return true if the values are an empty object and an empty array', function() {
            var actual = utils.areBeanValuesEqual({}, []);
            expect(actual).toBe(true);
        });

        it('should return false if two strings are different', function() {
            var actual = utils.areBeanValuesEqual('foo', 'bar');
            expect(actual).toBe(false);
        });

        it('should return false if two numbers are different', function() {
            var actual = utils.areBeanValuesEqual(123, 345);
            expect(actual).toBe(false);
        });

        it('should return false if two booleans are different', function() {
            var actual = utils.areBeanValuesEqual(true, false);
            expect(actual).toBe(false);
        });

        it('should return false if two arrays are different', function() {
            var actual = utils.areBeanValuesEqual(['1', '2'], ['1', '3']);
            expect(actual).toBe(false);
        });

        it('should return false if two objects are different', function() {
            var actual = utils.areBeanValuesEqual({one: 'one', two: 'two'}, {one: 'one', three: 'three'});
            expect(actual).toBe(false);
        });

        it('should return false if the values are undefined and a string', function() {
            var actual = utils.areBeanValuesEqual(undefined, 'foo');
            expect(actual).toBe(false);
        });

        it('should return false if the values are 0 and a string 0', function() {
            var actual = utils.areBeanValuesEqual(0, '0');
            expect(actual).toBe(false);
        });

        it('should return false if the values are 0 and false', function() {
            var actual = utils.areBeanValuesEqual(0, false);
            expect(actual).toBe(false);
        });

        it('should return false if the values are true and 1', function() {
            var actual = utils.areBeanValuesEqual(true, 1);
            expect(actual).toBe(false);
        });

        it('should return false if the values are true and string true', function() {
            var actual = utils.areBeanValuesEqual(true, 'true');
            expect(actual).toBe(false);
        });
    });


    describe('building urls', function() {
        var originalSiteUrl;

        beforeEach(function() {
            originalSiteUrl = app.config.siteUrl;
        });

        afterEach(function() {
            app.config.siteUrl = originalSiteUrl;
        });

        using('possible siteUrls', [{
            siteUrl: 'http://sugarcrm.com',
            url: 'my-path/example.png',
            expected: 'http://sugarcrm.com/my-path/example.png'
        },{
            siteUrl: 'http://sugarcrm.com/with-context',
            url: 'my-path/example.png',
            expected: 'http://sugarcrm.com/with-context/my-path/example.png'
        },{
            siteUrl: 'https://sugarcrm.com/with-context',
            url: 'my-path/example.png',
            expected: 'https://sugarcrm.com/with-context/my-path/example.png'
        },{
            siteUrl: 'http://sugarcrm.com/with-slash-context/',
            url: 'path/example.png',
            expected: 'http://sugarcrm.com/with-slash-context/path/example.png'
        },{
            siteUrl: 'http://sugarcrm.com/',
            url: 'http://example.com/my-path/example.png',
            expected: 'http://example.com/my-path/example.png'
        },{
            siteUrl: 'https://sugarcrm.com/',
            url: 'https://example.com/my-path/example.png',
            expected: 'https://example.com/my-path/example.png'
        },{
            siteUrl: 'https://sugarcrm.com/portal',
            url: '../my-path/example.png',
            expected: 'https://sugarcrm.com/portal/../my-path/example.png'
        }], function(value) {
            it('should build a correct url', function() {
                app.config.siteUrl = value.siteUrl;
                expect(app.utils.buildUrl(value.url)).toEqual(value.expected);
            });
        });
    });

    describe('formatNameModel', function() {

        var module = 'Contacts';
        var model = {
            'first_name': 'Brendan',
            'last_name': 'Eich',
            'salutation': 'Mr.'
        };

        beforeEach(function() {
            sinon.collection.stub(app.metadata, 'getModule', function() {
                return {
                    fields: {
                        salutation: {
                            type: 'enum',
                            options: 'salutation_dom'
                        }
                    },
                    nameFormat: {
                        s: 'salutation',
                        f: 'first_name',
                        l: 'last_name',
                        y: 'undefined_field'
                    }
                };
            });

            sinon.collection.stub(app.lang, 'getAppListStrings', function() {
                return {
                    'Mr.': 'Mister',
                    'Ms.': 'Miss'
                };
            });
        });

        afterEach(function() {
            sinon.collection.restore();
        });

        using('different formats', [
            {
                format: 's f l',
                expected: 'Mister Brendan Eich'
            },
            {
                format: 's l f',
                expected: 'Mister Eich Brendan'
            },
            {
                format: 'f s l',
                expected: 'Brendan Mister Eich'
            },
            {
                format: 'f l s',
                expected: 'Brendan Eich Mister'
            },
            {
                format: 'f y l s',
                expected: 'Brendan Eich Mister'
            },
            {
                format: ' ,f l s, ',
                expected: 'Brendan Eich Mister'
            },
            {
                format: 'f z l s',
                expected: 'Brendan Eich Mister'
            }
        ], function(value) {
            it('should format full name correctly', function() {
                expect(utils.formatNameModel(module, model, value.format)).toBe(value.expected);
            });
        });

    });

    describe('getChangedProps', function () {

        var getChangedProps = SUGAR.App.utils.getChangedProps;

        it("tests plain objects", function () {
            var o1 = {
                    'param1': 'value1',
                    'param2': 'value2',
                    'param3': '3'
                },
                o2 = {
                    'param1': 'value2',
                    'param2': 'value2',
                    'param3': 3
                };

            expect(getChangedProps(o1, o2, true)).toEqual({ param1 : 'value1', param3 : '3' });
            expect(getChangedProps(o1, o2, false)).toEqual({ param1 : 'value1' });

            var d1 = {a: 1, b: '2'},
                d2 = {a: 2, b: 2};

            var result = getChangedProps(d1, d2, false);
            expect(result).toEqual({ a : 1 });
        });

        it("tests comparison of objects with equal structure", function () {
            var o1 = {
                    'param1': {
                        'param1': '1',
                        'param2': '2'
                    },
                    'param2': 'value2',
                    'param3': '3'
                },
                o2 = {
                    'param1': {
                        'param1': 1,
                        'param2': 2
                    },
                    'param2': 'value2',
                    'param3': '3'
                };

            expect(getChangedProps(o1, o2, true)).toEqual({ param1 : o1.param1 });
            expect(getChangedProps(o1, o2, false)).toEqual({});
        });

        it("tests comparison of objects with different nested structure", function () {
            var o1 = {
                    'param1': {
                        'param1': '1',
                        'param2': '2'
                    },
                    'param2': 'value2',
                    'param3': '3'
                },
                o2 = {
                    'param1': {
                        'param1': '1',
                        'param2': '2',
                        'param3': '3'
                    },
                    'param2': 'value2',
                    'param3': '3'
                };

            expect(getChangedProps(o1, o2, true)).toEqual({ param1 : o1.param1 });
            expect(getChangedProps(o1, o2, false)).toEqual({ param1 : o1.param1 });
        });

        it("tests comparison of objects with arrays in property", function () {
            var o1 = {
                    'param1': [ {}, {} ]
                },
                o2 = {
                    'param1': [ {}, {}, {} ]
                };

            expect(getChangedProps(o1, o2, true)).toEqual({ param1 : o1.param1 });
            expect(getChangedProps(o1, o2, false)).toEqual({ param1 : o1.param1 });
        });

        it("tests comparing of arrays", function () {
            var o1 = {
                    'param1': [
                        { 'param1': '1', 'param2': '2' },
                        { 'param1': '3', 'param2': '4' }
                    ]
                },
                o2 = {
                    'param1': [
                        { 'param1': 1, 'param2': 2 },
                        { 'param1': 3, 'param2': 4 }
                    ]
                };

            expect(getChangedProps(o1, o2, true)).toEqual({ param1 : o1.param1 });
            expect(getChangedProps(o1, o2, false)).toEqual({});

            o1 = {
                'param1': [
                    { 'param1': '1', 'param2': '2' },
                    { 'param1': '3', 'param2': '4' },
                    { 'param1': '5', 'param2': '6' }
                ]
            };
            o2 = {
                'param1': [
                    { 'param1': 1, 'param2': 2 },
                    { 'param1': 3, 'param2': 4 }
                ]
            };

            expect(getChangedProps(o1, o2, true)).toEqual({ param1 : o1.param1 });
            expect(getChangedProps(o1, o2, false)).toEqual({ param1 : o1.param1 });

            o1 = {
                'param1': [
                    { 'param1': '1', 'param2': '2', 'param3': '3' }
                ]
            };
            o2 = {
                'param1': [
                    { 'param1': 1, 'param2': 2 }
                ]
            };

            expect(getChangedProps(o1, o2, true)).toEqual({ param1 : o1.param1 });
            expect(getChangedProps(o1, o2, false)).toEqual({ param1 : o1.param1 });
        });

        it("tests comparing different forms of booleans", function () {
            expect(getChangedProps({a: true}, {a: '1'}, false)).toEqual({});
            expect(getChangedProps({a: true}, {a: 1}, false)).toEqual({});
        });

    });

    describe('formatNameModel last_name only', function() {

        var module = 'Contacts';
        var model = {
            'last_name': 'Eich'
        };

        beforeEach(function() {
            sinon.collection.stub(app.metadata, 'getModule', function() {
                return {
                    fields: {
                        salutation: {
                            type: 'enum',
                            options: 'salutation_dom'
                        }
                    },
                    nameFormat: {
                        s: 'salutation',
                        f: 'first_name',
                        l: 'last_name',
                        y: 'undefined_field'
                    }
                };
            });

            sinon.collection.stub(app.lang, 'getAppListStrings', function() {
                return {
                    'Mr.': 'Mister',
                    'Ms.': 'Miss'
                };
            });
        });

        afterEach(function() {
            sinon.collection.restore();
        });

        using('different formats', [
            {
                format: 's f l',
                expected: 'Eich'
            },
            {
                format: 'f l',
                expected: 'Eich'
            },
            {
                format: 's l',
                expected: 'Eich'
            },
            {
                format: 'l, s f',
                expected: 'Eich'
            },
            {
                format: 'l, f',
                expected: 'Eich'
            },
            {
                format: 's l, f',
                expected: 'Eich'
            },
            {
                format: 'l s f',
                expected: 'Eich'
            },
            {
                format: 'l f s',
                expected: 'Eich'
            }
        ], function(value) {
            it('should format full name correctly', function() {
                expect(utils.formatNameModel(module, model, value.format)).toBe(value.expected);
            });
        });

    });

    describe('extendClass', function() {
        var app;

        beforeEach(function() {
            app = SugarTest.app;
        });

        it('should extend a class', function() {
            var cache = {};
            var baseClass = Backbone.View;
            cache.BaseMyController = baseClass;
            var controllerClass = {name: 'controllerClass'};
            var extendedClass = app.utils.extendClass(cache, baseClass, 'MyController', controllerClass, 'Base');
            var extendedObject = new extendedClass();
            expect(extendedObject.name).toBe('controllerClass');
            expect(extendedObject.el).not.toBe(null);
            expect(cache.MyController).not.toBe(null);
        });
    });

    describe('isDirectionRTL', function() {
        using('different values',
            [
                {value: '', isRTL: false},
                {value: '1234', isRTL: false},
                {value: 'abc', isRTL: false},
                {value: 'שנב', isRTL: true},
                {value: 'شسيز', isRTL: true},
                {value: '123 שנב', isRTL: true},
                {value: 'abc شزذ', isRTL: false},
                {value: 'abc 123 שנב', isRTL: false}
            ], function(pair) {
                it('should return whether the direction of the string is RTL', function() {
                    expect(utils.isDirectionRTL(pair.value)).toEqual(pair.isRTL);
                });
            }
        );
    });
});
