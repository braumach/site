<?php

/**
 *
 * Copyright Â© 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace orderhive\productmodule\Api;

/**
 * @api
 */
interface ProductRepositoryInterface {
	/**
	 * Get product list
	 *
	 * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria        	
	 * @return \orderhive\productmodule\Api\Data\ProductSearchResultsInterface
	 */
	public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
