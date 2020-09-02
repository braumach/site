<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog layered navigation view block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magebees\Finder\Block;

class Navigation extends \Magento\LayeredNavigation\Block\Navigation
{
    

    /**
     * Get all layer filters
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = $this->filterList->getFilters($this->_catalogLayer);
        foreach ($filters as $filter) {
            if ($filter instanceof \Magento\Catalog\Model\Layer\Filter\Category) {
                /*disable category filter*/
                $cat_key = array_search($filter, $filters);
                unset($filters[$cat_key]);
            }
        }

        return $filters;
    }
}
