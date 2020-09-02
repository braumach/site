/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/grid/columns/thumbnail',
    'text!Ced_Amazon/template/grid/cells/asin/preview.html',
], function ($, mageTemplate, Thumbnail, thumbnailPreviewTemplate) {
    'use strict';

    return Thumbnail.extend({
        /**
         * Build preview.
         *
         * @param {Object} row
         */
        preview: function (row) {
            var modalHtml = mageTemplate(
                thumbnailPreviewTemplate,
                {
                    src: this.getOrigSrc(row), alt: this.getAlt(row), link: this.getLink(row),
                    linkText: $.mage.__('Go to Details Page')
                }
                ),
                previewPopup = $('<div/>').html(modalHtml);

            previewPopup.modal({
                title: this.getAlt(row),
                innerScroll: true,
                modalClass: '_image-box',
                buttons: []
            }).trigger('openModal');
        },
    });
});
