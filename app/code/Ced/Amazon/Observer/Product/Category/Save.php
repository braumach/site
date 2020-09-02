<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Observer\Product\Category;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Ced\Amazon\Api\ProfileRepositoryInterface;
use Ced\Amazon\Api\Strategy\AssignmentRepositoryInterface;
use Ced\Amazon\Api\Profile\ProductRepositoryInterface;
use Ced\Amazon\Helper\Config;
use Ced\Amazon\Helper\Logger;

class Save implements ObserverInterface
{
    /**
     * Amazon Logger
     * @var \Ced\Amazon\Helper\Logger
     */
    private $logger;

    /** @var SerializerInterface  */
    private $serializer;

    /** @var Config */
    private $config;

    /** @var AssignmentRepositoryInterface  */
    private $assignmentRepository;

    /** @var ProfileRepositoryInterface  */
    private $profileRepository;

    /** @var ProductRepositoryInterface  */
    private $productRepository;

    public function __construct(
        SerializerInterface $serializer,
        AssignmentRepositoryInterface $assignmentRepository,
        ProfileRepositoryInterface $profileRepository,
        ProductRepositoryInterface $productRepository,
        Logger $logger,
        Config $config
    ) {
        $this->serializer = $serializer;
        $this->assignmentRepository = $assignmentRepository;
        $this->profileRepository = $profileRepository;
        $this->productRepository = $productRepository;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
//        try {
//            /** @var \Magento\Catalog\Model\Category $category */
//            $category = $observer->getData('category');
//            if ($category instanceof \Magento\Catalog\Api\Data\CategoryInterface &&
//                $category->dataHasChangedFor('category_products') &&
//                $this->config->getStrategyAutoAssignProducts()
//            ) {
//                $categoryId = $category->getId();
//                /** @var \Ced\Amazon\Api\Data\Strategy\AssignmentInterface $strategy */
//                $strategy = $this->assignmentRepository->getAssignmentStrategyByCategoryId($categoryId);
//
//                if ($strategy->getActive()) {
//                    $profiles = $this->profileRepository->getProfileIdsByStrategyId($strategy->getStrategyId());
//                    if (!empty($profiles)) {
//                        $ids = array_keys($this->serializer->unserialize($category->getData('category_products')));
//                        foreach ($profiles as $profileId) {
//                            $this->productRepository->addProductsIdsWithProfileId($ids, $profileId);
//                        }
//                    }
//                }
//            }
//        } catch (\Exception $e) {
//            $this->logger->error($e->getMessage(), ["path" => __METHOD__]);
//        }
    }
}
