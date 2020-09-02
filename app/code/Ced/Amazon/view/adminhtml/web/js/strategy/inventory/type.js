define([
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'jquery',
    'rjsResolver'
], function (registry, Select, $, resolver) {
    'use strict';
    return Select.extend({
        initialize: function () {
            this._super();

            resolver(this.initType, this);
        },

        onUpdate: function (value) {
            this.show(value);

            return this._super();
        },

        initType: function () {
            var flag = this.value();
            this.show(flag);
        },

        show: function (type) {
            if (!this.empty(type)) {
                var fulfillment_attribute = registry.get('index = fulfillment_center_id_attribute');
                var fulfillment = registry.get('index = fulfillment_center_id');

                var available_attribute = registry.get('index = available_attribute');
                var available = registry.get('index = available');

                var quantity_attribute = registry.get('index = quantity_attribute');
                var quantity = registry.get('index = quantity');

                switch (type) {
                    case 'quantity': {
                        fulfillment_attribute.visible(false);
                        fulfillment.visible(false);
                        available_attribute.visible(false);
                        available.visible(false);
                        quantity_attribute.visible(true);
                        if (quantity_attribute.value() == "default_value") {
                            quantity.visible(true);
                        } else {
                            quantity.visible(false);
                        }
                        break;
                    }
                    case 'lookup': {
                        fulfillment_attribute.visible(true);
                        if (fulfillment_attribute.value() == "default_value") {
                            fulfillment.visible(true);
                        } else {
                            fulfillment.visible(false);
                        }

                        available_attribute.visible(false);
                        available.visible(false);

                        quantity_attribute.visible(true);
                        if (quantity_attribute.value() == "default_value") {
                            quantity.visible(true);
                        } else {
                            quantity.visible(false);
                        }
                        break;
                    }
                    case 'available': {
                        fulfillment_attribute.visible(false);
                        fulfillment.visible(false);
                        available_attribute.visible(true);
                        if (available_attribute.value() == "default_value") {
                            available.visible(true);
                        } else {
                            available.visible(false);
                        }
                        quantity_attribute.visible(false);
                        quantity.visible(false);
                        break;
                    }
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
