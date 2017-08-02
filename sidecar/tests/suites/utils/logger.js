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
describe('Logger', function() {
    var sandbox;
    var config;
    var logger;
    var mockConsole;

    beforeEach(function() {
        sandbox = sinon.sandbox.create();
        logger = SugarTest.app.logger;

        var stubConfig = {
            logger: {
                level: 'FATAL'
            },
            logFormatter: 'SimpleFormatter',
            logWriter: 'ConsoleWriter'
        };

        sandbox.stub(SugarTest.app, 'config', stubConfig);
        config = SugarTest.app.config;
        mockConsole = sandbox.mock(console);
    });

    afterEach(function() {
        sandbox.restore();
        config = null;
    });

    it('should be able to log a message', function() {
        var date = new Date(Date.UTC(2012, 2, 3, 6, 15, 32));
        sandbox.useFakeTimers(date.getTime());

        config.logger.level = 'ERROR';

        mockConsole.expects('error').once().withArgs('ERROR[2012-3-3 6:15:32]: Test message');
        logger.error('Test message');
        mockConsole.verify();
    });

    it('should be able to log with a function', function() {
        var mockConsoleExpect = mockConsole.expects('log').once();
        var testMsg = 'foo';

        config.logger.level = 'INFO';
        logger.info(function() {
            return 'Test message ' + testMsg;
        });

        expect(mockConsoleExpect.args[0]).toMatch(/INFO\[.{14,20}\]: Test message foo/);
        mockConsole.verify();
    });

    it('should be able to log an object', function() {
        var mockConsoleExpect = mockConsole.expects('log').once();
        var testMsg = { bar: 'some bar'};

        config.logger.level = 'TRACE';
        logger.trace(testMsg);
        expect(mockConsoleExpect.args[0]).toMatch(/TRACE\[.{14,20}\]: \{"bar":"some bar"\}/);
        mockConsole.verify();
    });

    it('should not log a message if log level is below the configured one', function() {
        mockConsole.expects('log').never();
        config.logger.level = 'INFO';
        logger.debug('');
        mockConsole.verify();
    });

    it('should be able to log a message with a given log level', function() {
        config.logger.level = 'TRACE';

        var mockLogger = sandbox.mock(logger);

        // FIXME: SC-5468 Perhaps it should be split up into separate specs.
        mockLogger.expects('trace').once();
        mockLogger.expects('debug').once();
        mockLogger.expects('info').once();
        mockLogger.expects('warn').once();
        mockLogger.expects('error').once();
        mockLogger.expects('fatal').once();

        logger.trace('');
        logger.debug('');
        logger.info('');
        logger.warn('');
        logger.error('');
        logger.fatal('');

        mockLogger.verify();
    });

    describe('getLevel', function() {
        var deprecationMessage = 'The property `app.config.logLevel` has been ' +
            'deprecated since 7.7.0.0 and will be removed in 7.9.0.0. ' +
            'Use `app.config.logger.level` instead.';
        var errorMessage = 'Your logger level is set to an invalid value. ' +
            'Please redefine it in Administration > System Settings. ' +
            'If you continue to see this warning, please ' +
            'contact your Admin.';

        // FIXME: SC-5469 Remove this test.
        it('should fallback to logLevel settings since logger is undefined', function() {
            delete config.logger;
            config.logLevel = 'INFO';

            mockConsole.expects('warn').once().withArgs(deprecationMessage);
            expect(logger.getLevel()).toEqual(logger.levels.INFO);
            mockConsole.verify();
        });

        // FIXME: SC-5469 Remove this test.
        it('should fallthrough to ERROR even though logLevel is defined', function() {
            delete config.logger;
            config.logLevel = 'BS';

            mockConsole.expects('warn').once().withArgs(deprecationMessage);
            // FIXME this needs to be done after SC-5483 is implemented
            // mockConsole.expects('error').once().withArgs(errorMessage);
            expect(logger.getLevel()).toEqual(logger.levels.ERROR);
            mockConsole.verify();
        });

        it('should fallthrough to ERROR and not throw any warning', function() {
            delete config.logger;
            expect(logger.getLevel()).toEqual(logger.levels.ERROR);
            mockConsole.verify();
        });

        it('should fallthrough to ERROR and throw an error warning', function() {
            config.logger.level = 'NOTSURE';
            // FIXME this needs to be done after SC-5483 is implemented
            // mockConsole.expects('error').once().withArgs(errorMessage);
            expect(logger.getLevel()).toEqual(logger.levels.ERROR);
            mockConsole.verify();
        });

        // FIXME: SC-5469 Remove this test.
        it('should not throw deprecation warning since it uses logger.level', function() {
            config.logLevel = 'INFO';
            expect(logger.getLevel()).toEqual(logger.levels.FATAL);
            mockConsole.verify();
        });
    });
});
