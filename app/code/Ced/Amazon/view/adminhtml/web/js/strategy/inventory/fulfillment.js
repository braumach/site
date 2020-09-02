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
            var fulfillment = registry.get('index = fulfillment_center_id');
            if (!this.empty(fulfillment)) {
                if (!this.empty(type) && type == "default_value") {
                    fulfillment.visible(true);
                } else {
                    fulfillment.visible(false);
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