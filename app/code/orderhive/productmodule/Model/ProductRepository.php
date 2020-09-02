<?php

/**
 *
 * Copyright Â© 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace orderhive\productmodule\Model;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\SortOrder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProductRepository implements \orderhive\productmodule\Api\ProductRepositoryInterface {
	
	/**
	 *
	 * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory
	 */
	protected $searchResultsFactory;
	
	/**
	 *
	 * @var \Magento\Framework\Api\SearchCriteriaBuilder
	 */
	protected $searchCriteriaBuilder;
	
	/**
	 *
	 * @var \Magento\Framework\Api\FilterBuilder
	 */
	protected $filterBuilder;
	
	/**
	 *
	 * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
	 */
	protected $collectionFactory;
	
	/**
	 *
	 * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
	 */
	protected $metadataService;
	
	/**
	 *
	 * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
	 */
	protected $extensionAttributesJoinProcessor;
	
	/**
	 *
	 * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory        	
	 * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory        	
	 * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder        	
	 * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface        	
	 * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
	 *        	@SuppressWarnings(PHPMD.ExcessiveParameterList)
	 */
	public function __construct(\Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory, \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder, \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface, \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor) {
		$this->collectionFactory = $collectionFactory;
		$this->searchResultsFactory = $searchResultsFactory;
		$this->searchCriteriaBuilder = $searchCriteriaBuilder;
		$this->metadataService = $metadataServiceInterface;
		$this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
	}
	
	/**
	 *
	 * {@inheritdoc}
	 *
	 */
	public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria) {
		/** @var \Inchoo\Hello\Model\ResourceModel\Product\Collection $collection */
		$collection = $this->collectionFactory->create ();
		$this->extensionAttributesJoinProcessor->process ( $collection );
		
		foreach ( $this->metadataService->getList ( $this->searchCriteriaBuilder->create () )->getItems () as $metadata ) {
			$collection->addAttributeToSelect ( $metadata->getAttributeCode () );
		}
		
		$collection->joinAttribute ( 'status', 'catalog_product/status', 'entity_id', null, 'inner' );
		$collection->joinAttribute ( 'visibility', 'catalog_product/visibility', 'entity_id', null, 'inner' );
		$collection->joinField ( 'qty', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', null, 'left' );
		
		// Add filters from root filter group to the collection
		foreach ( $searchCriteria->getFilterGroups () as $group ) {
			
			$this->addFilterGroupToCollection ( $group, $collection );
		}
		/** @var SortOrder $sortOrder */
		foreach ( ( array ) $searchCriteria->getSortOrders () as $sortOrder ) {
			
			$field = $sortOrder->getField ();
			$collection->addOrder ( $field, ($sortOrder->getDirection () == SortOrder::SORT_ASC) ? 'ASC' : 'DESC' );
		}
		$collection->setCurPage ( $searchCriteria->getCurrentPage () );
		$collection->setPageSize ( $searchCriteria->getPageSize () );
		$collection->load ();
		
		$searchResult = $this->searchResultsFactory->create ();
		$searchResult->setSearchCriteria ( $searchCriteria );
		$searchResult->setItems ( $collection->getItems () );
		$searchResult->setTotalCount ( $collection->getSize () );
		
		return $searchResult;
	}
	
	/**
	 * Helper function that adds a FilterGroup to the collection.
	 *
	 * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup        	
	 * @param Collection $collection        	
	 * @return void
	 */
	protected function addFilterGroupToCollection(\Magento\Framework\Api\Search\FilterGroup $filterGroup, Collection $collection) {
		$fields = [ ];
		$categoryFilter = [ ];
		
		foreach ( $filterGroup->getFilters () as $filter ) {
			$conditionType = $filter->getConditionType () ? $filter->getConditionType () : 'eq';
			
			if ($filter->getField () == 'category_id') {
				$categoryFilter [$conditionType] [] = $filter->getValue ();
				continue;
			}
			$fields [] = [ 
					'attribute' => $filter->getField (),
					$conditionType => $filter->getValue () 
			];
		}
		
		if ($categoryFilter) {
			$collection->addCategoriesFilter ( $categoryFilter );
		}
		
		if ($fields) {
			$collection->addFieldToFilter ( $fields );
		}
	}
}
