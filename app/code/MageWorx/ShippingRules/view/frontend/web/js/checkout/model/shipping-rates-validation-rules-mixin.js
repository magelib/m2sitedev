/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'mage/utils/wrapper'
],function ($, Wrapper) {
    "use strict";

    var additionalFields = [
        'region',
        'region_id',
        'street',
        'city'
    ];

    return function (origRules) {
        origRules.getObservableFields = Wrapper.wrap(
            origRules.getObservableFields,
            function (originalAction) {
                var fields = originalAction();

                additionalFields.forEach(function (field) {
                    fields.push(field);
                });

                return fields;
            }
        );

        return origRules;
    };
});