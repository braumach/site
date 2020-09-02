/**
 * @api
 */
define(
    [
        'Magento_Ui/js/grid/columns/actions',
        'jquery',
        'uiRegistry'
    ],
    function (Actions, $, registry) {
        'use strict';

        return Actions.extend({
            defaults: {
                bodyTmpl: 'Ced_Amazon/grid/cells/asin/actions',
            },

            /**
             * Applies specified action.
             *
             * @param   {String} actionIndex - Actions' identifier.
             * @param   {Number} rowIndex - Index of a row.
             * @returns {ActionsColumn} Chainable.
             */
            applyAction: function (actionIndex, rowIndex) {
                var action = this.getAction(rowIndex, actionIndex),
                    callback = this._getCallback(action);
                if (action.modal) {
                    // Opening popup with search product grid
                    var modal = registry.get('amazon_product_listing.amazon_product_listing.assign_asin_modal');
                    modal.openModal();

                    registry.get("amazon_search_product_listing.amazon_search_product_listing.listing_top.listing_filters", function (filter) {
                        // Applying filter for relation_id
                        filter.setData({relation_id: action.modal.relation_id});
                        filter.apply();
                    });
                }
                return this;
            },
        });
    }
);