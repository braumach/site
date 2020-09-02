<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace orderhive\productmodule\Api\Data;

/**
 * @api
 */
interface ProductSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \orderhive\productmodule\Api\Data\ProductInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \orderhive\productmodule\Api\Data\ProductInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
