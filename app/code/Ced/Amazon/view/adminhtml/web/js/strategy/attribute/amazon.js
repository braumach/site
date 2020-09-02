define([
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'jquery',
    'rjsResolver',
    'ko'
], function (registry, Select, $, resolver, ko) {
    'use strict';
    return Select.extend({
        onUpdate: function (value) {
            this.updateTooltip(value);
            this.updateDefault(value);

            return this._super();
        },

        updateDefault: function (value) {
            let option = this.getOption(value);
            let defaultValue = registry.get(this.parentName + ".default_value");
            let defaultValueSelect = registry.get(this.parentName + ".default_value_select");
            // TODO: set selected values
            if (option && option.hasOwnProperty("options") && !this.empty(option['options'])) {
                defaultValue.visible(false);
                defaultValueSelect.setOptions(this.prepareOptions(option['options']));
                defaultValueSelect.visible(true);
            } else {
                defaultValue.visible(true);
                defaultValueSelect.visible(false);
            }
        },

        updateTooltip: function (value) {
            // TODO: update tooltip on select change
            if (this.getOption(value)) {
                this.tooltip.description = JSON.stringify(this.getOption(value)['info']);
            }
        },

        prepareOptions: function (options) {
            let result = [];
            $.each(options, function (i, v) {
                result.push({
                    "label" : v,
                    "value" : v,
                });
            });

            return result;
        },
        getOption: function (value) {
            if (this.indexedOptions.hasOwnProperty(value)) {
                return this.indexedOptions[this.value()];
            }

            return false;
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
                    } else if (typeof e === "object" && Object.keys(e).length === 0) {
                        return true;
                    } else {
                        return false;
                    }
            }
        }
    });
});