define([
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'jquery',
    'rjsResolver'
], function (registry, Select, $, resolver) {
    'use strict';
    return Select.extend({
        onUpdate: function (value) {
            this.show(value);

            return this._super();
        },

        show: function (type) {
            var available = registry.get('index = available');
            if (!this.empty(available)) {
                if (!this.empty(type) && type == "default_value") {
                    available.visible(true);
                } else {
                    available.visible(false);
                }
            }
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