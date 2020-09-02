/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'underscore',
    'Magento_Ui/js/grid/listing',
    'Ced_Amazon/js/grid/columns/product/status',
    'Ced_Integrator/js/grid/columns/product/validation'
], function (ko, _, Listing, Status, Validation) {
    'use strict';

    return Listing.extend({
        defaults: {
            template: 'Ced_Amazon/grid/listing',
            listingTemplate: 'Ced_Amazon/grid/profile/listing',
            originalListing: 'ui/grid/listing',
            columnHtml: 'Ced_Amazon/grid/cells/html',
            columnText: 'Ced_Amazon/grid/cells/text',
            headings: ko.observable(
                {
                    product_id: "Product Id",
                    // cap_asin: "ASIN",
                    cap_validation_errors: "Errors",
                    id: "Relation Id",
                    cap_status: "Amazon Status",
                    capf_account_id: "Account Id",
                    capf_marketplace: "Marketplace",
                    capf_profile_category: "Profile Category",
                    capf_store_id: "Store",
                    profile_id: "Profile"
                }
            ),
            additionalData: [],
            currentColumnValue: ' '
        },

        hasData: function () {
            return !!this.rows && !!this.rows.length;
        },

        getVisible: function () {
            let observable = ko.getObservable(this, 'visibleColumns');
            return observable || this.visibleColumns;
        },

        isSecondRow: function (count) {
            return count;
        },

        getProfileTable: function (row) {
            this.additionalData[row.entity_id] = Object.values(row.ced_amazon_additional_data.additional_data.data);
            this.additionalData[row.entity_id].length = row.ced_amazon_additional_data.additional_data.data.length;
            return this.listingTemplate;
        },

        getColumnTemplate: function(attribute) {
            if (attribute === 'cap_status' || attribute === 'cap_validation_errors') {
                return this.columnHtml;
            }

            return this.columnText;
        },

        getAttributeValue: function (attribute, product) {
            if (attribute === "capf_profile_category") {

                return product['capf_profile_category'] + "/" + product['capf_profile_sub_category'];
            }

            if (attribute === "profile_id") {
                return product['profile_id']+ "|" +product['capf_profile_name'];
            }

            if (attribute === "capf_marketplace") {
                return this.marketplaceOptions.options[product["capf_marketplace"]].code;
            }

            if (attribute === "cap_status") {
               let amazonStatus =  Status.extend({index : "cap_status"});
               return amazonStatus().getLabel(product);
            }

            if (attribute === 'cap_validation_errors') {
                return this.setErrors(product);

            }

            return product[attribute];
        },

        setErrors: function(product){
         //   let errors = JSON.parse(product['cap_validation_errors']);
            let feedErrors = JSON.stringify([]);
            let feedValid = true;
            let fieldName = 'cap_validation_errors';
            if (product.hasOwnProperty('cap_feed_errors') && product.cap_feed_errors) {
                feedErrors = product.cap_feed_errors;
                feedValid = false;
            }
            if (product.hasOwnProperty('cap_validation_errors') && product.cap_validation_errors) {
                product[fieldName + '_show_heading'] = true;
                product[fieldName + '_product_feed_errors'] = feedErrors;
                product[fieldName + '_product_feed_label'] =
                    "<tr><td class='cedcommerce errors feed' title='View Feed Errors'>F:</td><td class='cedcommerce errors'><div class='grid-severity-notice'><span>Valid</span></div></td></tr>";
                if (!feedValid) {
                    product[fieldName + '_product_feed_label'] =
                        "<tr><td class='cedcommerce errors feed' title='View Feed Errors'>F:</td><td class='cedcommerce errors'><div class='grid-severity-critical'><span>Invalid</span></div></td></tr>";
                }

                if (product[fieldName] === '["valid"]') {
                    product[fieldName + '_html'] =
                        "<tr><td class='cedcommerce errors validation' title='View Validation Errors'>V:</td><td class='cedcommerce errors'><div class='grid-severity-notice'><span>valid</span></div></td></tr>";
                    product[fieldName + '_title'] = 'Errors';
                    product[fieldName + '_productid'] = product['product_id'];
                } else {
                    product[fieldName + '_html'] =
                        "<tr><td class='cedcommerce errors validation' title='View Validation Errors'>V:</td><td class='cedcommerce errors'><div class='grid-severity-critical'><span>invalid</span></div></td></tr>";
                    product[fieldName + '_title'] = 'Errors';
                    product[fieldName + '_productid'] = product['product_id'];
                    product[fieldName + '_productvalidation'] = product[fieldName];
                }
            } else {
                product[fieldName + '_html'] =
                    "<tr><td class='cedcommerce errors validation' title='View Validation Errors'>V:</td><td class='cedcommerce errors'><div class='grid-severity-notice'><span>NA</span></div></td></tr>";
                product[fieldName + '_title'] = 'Errors';
                product[fieldName + '_productid'] = product['product_id'];
            }
           let validate = Validation.extend({
                index : fieldName,
            });

            return validate().getLabel(product);
        },

        getHtmlClickHandler: function(attribute, product) {
            if (attribute === 'cap_validation_errors') {
                let validateFieldHandler = Validation.extend({
                    index : 'cap_validation_errors',
                });
                validateFieldHandler().startView(product);
            }
        },

        childIndex: function (row) {
            return 'childTable-'+row.entity_id;
        },
        parentIndex: function (row) {
                return 'collapsible-' + row.entity_id;
        },

    });

});
