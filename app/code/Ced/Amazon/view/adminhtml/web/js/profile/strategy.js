define([
    'jquery',
    'underscore',
    'uiRegistry',
    'rjsResolver',
    'Magento_Ui/js/form/element/ui-select',
    'Magento_Ui/js/modal/modal'
], function ($, _, uiRegistry, resolver, Select, modal) {
    'use strict';

    return Select.extend({
        /**
         * Parse data and set it to options.
         *
         * @param {Object} data - Response data object.
         * @returns {Object}
         */
        setParsed: function (data) {
            var option = this.parseData(data);
            if (data.error) {
                return this;
            }

            this.cacheOptions.tree.push(option);
            this.cacheOptions.plain.push(option);
            this.options(this.cacheOptions.tree);
            this.setOption(option);
            this.set('newOption', option);

        },

        /**
         * Normalize option object.
         *
         * @param {Object} data - Option object.
         * @returns {Object}
         */
        parseData: function (data) {
            return {
                is_active: '1',
                level: 0,
                value: data.strategy['id'],
                label: data.strategy['name'],
                parent: 0
            };
        },

        empty: function (e) {
            switch (e) {
                case "":
                case 0:
                case "0":
                case null:
                case false:
                    return true;
                default:
                    if (typeof e === "undefined") {
                        return true;
                    } else if (typeof e === "object" && Object.keys(e).length === 0){
                        return true;
                    } else {
                        return false;
                    }
            }
        }
    });
});