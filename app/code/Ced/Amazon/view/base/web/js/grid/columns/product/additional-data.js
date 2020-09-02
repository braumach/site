/**
 * @api
 */
define(
    [
        'Magento_Ui/js/grid/columns/actions',
        'jquery',
        'uiRegistry',
    ],
    function (Actions, $, registry) {
        'use strict';

        return Actions.extend({
            defaults: {
                bodyTmpl: 'Ced_Amazon/grid/cells/additional-data/actions',
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
                if (action.data) {
                    //let id = event.target.id;
                    let explodeId = action.data[0].product_id;
                    let collapsible= $('#collapsible-'+explodeId);
                    let id=action.data[0].id;
                    let element = $('#childTable-'+explodeId);
                    let display = element.css('display');
                    if (display === 'none') {
                        collapsible.removeClass('downarrow').addClass('uparrow');
                        element.css('display', '');
                    } else {
                        collapsible.removeClass('uparrow').addClass('downarrow');
                        element.css('display', 'none');
                    }
                }
                return this;
            },

            /**
             * Creates 'async' wrapper for the specified child
             * using uiRegistry 'async' method and caches it
             * in a '_requested' components  object.
             *
             * @param {String} index - Index of a child.
             * @returns {Function} Async module wrapper.
             */
            requestChild: function (index) {
                var name = this.formChildName(index);
                return this.requestModule(name);
            },

            /**
             * Creates complete child name based on a provided index.
             *
             * @param {String} index - Index of a child.
             * @returns {String}
             */
            formChildName: function (index) {
                return this.name + '.' + index;
            },

        });
    }
);