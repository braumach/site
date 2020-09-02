/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/lib/view/utils/async',
    'uiRegistry',
    'underscore',
    'Magento_Ui/js/form/components/insert-listing'
], function ($, registry, _, InsertListing) {
    'use strict';

    return InsertListing.extend({
        defaults: {
            addAsinUrl: '',
            gridProvider: '',
            modules: {
                grid: '${ $.gridProvider }',
                modal: '${ $.parentName }'
            },
        },

        /**
         * Render attribute
         */
        render: function () {
            this._super();
        },

        /**
         * Save attribute
         */
        save: function () {
            this.assignAsin();
            this._super();
        },

        /**
         * Add selected attributes
         */
        assignAsin: function () {
            $.ajax({
                url: this.addAsinUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    filter: this.selections().getSelections(),
                },
                success: function () {
                    this.grid().source.reload();
                    this.reload();
                }.bind(this)
            });
        }
    });
});
