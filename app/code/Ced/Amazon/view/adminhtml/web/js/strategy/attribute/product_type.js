define([
    'uiRegistry',
    'Magento_Ui/js/form/element/ui-select',
    'jquery',
    'underscore'
], function (uiRegistry, Select, $, _) {
    'use strict';

    return Select.extend({
        defaults: {
            optgroupTmpl: 'Ced_Amazon/grid/filters/elements/ui-select-optgroup',
            multiple: true
        },

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

            this.options([]);
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
                'is_active': data.category['is_active'],
                level: data.category.level,
                value: data.category['entity_id'],
                label: data.category.name,
                parent: data.category.parent
            };
        },

        /**
         * Toggle activity list element
         *
         * @param {Object} data - selected option data
         * @returns {Object} Chainable
         */
        toggleOptionSelected: function (data) {
            var isSelected = this.isSelected(data.value);

            if (this.lastSelectable && data.hasOwnProperty(this.separator)) {
                return this;
            }

            if (!isSelected) {
                this.value(data.value);
                var type = data.value;
                if (!this.empty(type)) {
                    this.update(type);
                }
            } else {
                this.value(_.without(this.value(), data.value));
            }
            this.listVisible(true);
            return this;
        },

        update: function (type) {
            var self = this;
            var parameters = {
                'strategy_id': STRATEGY_ID,
                "product_type": type,
                'form_key': window.FORM_KEY
            };

            $.ajax({
                url: STRATEGY_ATTRIBUTE_UPDATE_URL,
                type: 'POST',
                data: parameters,
                dataType: 'json',
                showLoader: true,
                success: function (response) {
                    self.updateRows(response);
                }
            });

        },

        updateRows: function (attributes) {
            var records = [];
            var options = [];
            var required = [];
            var optional = [];
            var recordId = 0;

            $.each(attributes["required"], function (i, v) {
                var code = "";
                var defaultValue = "";
                var values = [];

                if (v.hasOwnProperty("restriction") && v["restriction"].hasOwnProperty("optionValues")) {
                    values = v['restriction']['optionValues'];
                }

                if (v.hasOwnProperty('magento_attribute_code')) {
                    code = v['magento_attribute_code'];
                }

                if (v.hasOwnProperty('default_value')) {
                    defaultValue = v['default_value'];
                }

                var option = {
                    "label": v['name'],
                    "value": i,
                    "options": values
                };
                var attribute = {
                    "record_id": recordId.toString(),
                    "mp_attribute": i,
                    "code": code,
                    "default_value": defaultValue,
                    "initialize": "true"
                };

                records.push(attribute);
                required.push(option);
                recordId++;
            });

            $.each(attributes["optional"], function (i, v) {
                var values = [];
                if (v.hasOwnProperty("restriction") && v["restriction"].hasOwnProperty("optionValues")) {
                    values = v['restriction']['optionValues'];
                }

                var option = {
                    "label": v['name'],
                    "value": i,
                    "options": values
                };

                optional.push(option);
            });

            options.push({
                "label": "Required Attributes",
                "value": required
            });

            options.push({
                "label": "Optional Attributes",
                "value": optional
            });

            var container = uiRegistry.get('index = additional_attributes');
            container.childTemplate.children.mp_attribute.config.options = options;

            if (this.empty(records)) {
                records.push({
                    "record_id": 0,
                    "mp_attribute": '',
                    "code": '',
                    "default_value": '',
                    "initialize": "false"
                });
            }

            container.recordData(records);
            container.reload();
        },

        /**
         * Check selected option
         *
         * @param {String} value - option value
         * @return {Boolean}
         */
        isSelected: function (value) {
            return this.value() == value;
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
