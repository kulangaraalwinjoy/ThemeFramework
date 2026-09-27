/**
 * WordPress Theme Framework - Conditional Fields Engine
 */
(function ($) {
    'use strict';

    function evaluateCondition(actual, operator, expected) {
        switch (operator.toUpperCase()) {
            case '==':
                return String(actual) === String(expected);
            case '!=':
                return String(actual) !== String(expected);
            case '>':
                return Number(actual) > Number(expected);
            case '<':
                return Number(actual) < Number(expected);
            case '>=':
                return Number(actual) >= Number(expected);
            case '<=':
                return Number(actual) <= Number(expected);
            case 'IN':
                return Array.isArray(expected) && expected.map(String).includes(String(actual));
            case 'NOT IN':
                return Array.isArray(expected) && !expected.map(String).includes(String(actual));
            default:
                return String(actual) === String(expected);
        }
    }

    function getFieldValue(fieldId) {
        var $input = $('[name="' + fieldId + '"], [id="' + fieldId + '"]');
        if (!$input.length) {
            return '';
        }

        if ($input.is(':checkbox')) {
            var $checked = $('[name="' + fieldId + '"]:checked');
            return $checked.length ? $checked.val() : '0';
        }

        if ($input.is(':radio')) {
            var $radio = $('[name="' + fieldId + '"]:checked');
            return $radio.length ? $radio.val() : '';
        }

        return $input.val();
    }

    function checkFieldConditions($fieldRow) {
        var conditionsAttr = $fieldRow.attr('data-conditions');
        if (!conditionsAttr) {
            return;
        }

        var conditions;
        try {
            conditions = JSON.parse(conditionsAttr);
        } catch (e) {
            return;
        }

        if (!Array.isArray(conditions) || !conditions.length) {
            return;
        }

        var allMet = true;

        for (var i = 0; i < conditions.length; i++) {
            var cond = conditions[i];
            var depField = cond.field;
            var operator = cond.compare || '==';
            var expected = cond.value;
            var actual = getFieldValue(depField);

            if (!evaluateCondition(actual, operator, expected)) {
                allMet = false;
                break;
            }
        }

        if (allMet) {
            $fieldRow.show();
        } else {
            $fieldRow.hide();
        }
    }

    function initConditions() {
        var $conditionalFields = $('.theme-framework-field[data-conditions]');
        if (!$conditionalFields.length) {
            return;
        }

        // Collect all dependent fields to listen to
        var watchedFields = {};

        $conditionalFields.each(function () {
            var $row = $(this);
            var conditionsAttr = $row.attr('data-conditions');
            try {
                var conditions = JSON.parse(conditionsAttr);
                if (Array.isArray(conditions)) {
                    conditions.forEach(function (c) {
                        if (c.field) {
                            watchedFields[c.field] = true;
                        }
                    });
                }
            } catch (e) {}

            // Initial check
            checkFieldConditions($row);
        });

        // Attach listeners to watched fields
        Object.keys(watchedFields).forEach(function (fieldId) {
            $(document).on('change input', '[name="' + fieldId + '"], [id="' + fieldId + '"]', function () {
                $conditionalFields.each(function () {
                    checkFieldConditions($(this));
                });
            });
        });
    }

    $(function () {
        initConditions();
        $(document).on('theme_framework_init', initConditions);
    });
})(jQuery);
