/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

/**
 * Initialization custom checkbox component
 *
 * @method initConfig()
 * @method onStateChange(newChecked)
 */
define([
    'jquery',
    'Magento_Ui/js/form/element/single-checkbox'
], function ($, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            saveButton: '[data-ui-id=save-button]',
            scheduleButton: '[data-ui-id=schedule-button]',
            publishButton: '[data-ui-id=publish-button]',
            saveAndContinueButton: '[data-ui-id=save-and-continue-button]',
            imports: {
                onStateChange: 'value'
            },
            listens: {
                'checked': 'onStateChange'
            }
        },

        /**
         * Initialize config
         */
        initConfig: function () {
            this._super();
            this.allSaveButtons = [
                this.saveButton,
                this.scheduleButton,
                this.publishButton,
                this.saveAndContinueButton
            ].join(',');

            return this;
        },

        /**
         * Change elements visibility
         */
        onStateChange: function (newChecked) {
            var isScheduledPost = this.source.get('data.is_scheduled_post');

            this.onCheckedChanged(newChecked);
            $(this.allSaveButtons).hide();
            if (newChecked) {
                if (isScheduledPost) {
                    $(this.saveButton).show();
                    $(this.saveAndContinueButton).show();
                } else {
                    $(this.scheduleButton).show();
                }
            } else {
                $(this.publishButton).show();
            }
        }
    });
});
