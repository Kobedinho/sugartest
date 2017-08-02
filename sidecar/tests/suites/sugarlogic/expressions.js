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
describe('SugarCRM expressions logic', function () {
    it('getFieldsFromExpression method. Should return fields marked with $ from expression', function () {
        var getFieldsFromExpression = SUGAR.expressions.ExpressionParser.prototype.getFieldsFromExpression,
            fields = ['field1', 'field2', 'field3'],
            $fields = _.map(fields, function (field) { return '$' + field; }),
            expressionWOFields = 'This formula does not contains any field',
            expressionWithFields = 'This formula contains ' + $fields.join(', ');

        expect(getFieldsFromExpression(expressionWOFields)).toEqual([]);
        expect(getFieldsFromExpression(expressionWithFields)).toEqual(fields);
    });
});
