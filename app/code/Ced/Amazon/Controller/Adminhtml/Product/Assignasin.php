<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Product;

use Ced\Amazon\Api\Data\ProductInterfaceFactory as AmazonProductFactory;
use Ced\Amazon\Api\Data\Search\ProductInterface;
use Ced\Amazon\Api\Data\Search\ProductInterfaceFactory as AmazonSearchProductFactory;
use Ced\Amazon\Model\ResourceModel\Product as AmazonProductResource;
use Ced\Amazon\Model\ResourceModel\Search\Product as AmazonSearchProductResource;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Assignasin extends Action
{
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /** @var AmazonProductFactory  */
    public $amazonProductFactory;

    /** @var AmazonProductResource  */
    public $productResource;

    /** @var AmazonSearchProductResource  */
    public $amazonSearchProductResource;

    /** @var AmazonSearchProductFactory  */
    public $amazonSearchProductFactory;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        AmazonProductFactory $amazonProductFactory,
        AmazonProductResource $productResource,
        AmazonSearchProductFactory $amazonSearchProductFactory,
        AmazonSearchProductResource $amazonSearchProductResource
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->amazonProductFactory = $amazonProductFactory;
        $this->productResource = $productResource;
        $this->amazonSearchProductFactory = $amazonSearchProductFactory;
        $this->amazonSearchProductResource = $amazonSearchProductResource;
    }

    public function execute()
    {
        $status = false;
        $filter = $this->getRequest()->getParam('filter');
        if (isset($filter['params']['filters']['relation_id'], $filter['selected']['0']) &&
            !empty($filter['params']['filters']['relation_id'])) {
            $relationId = $filter['params']['filters']['relation_id'];

            $searchProductId = $filter['selected']['0'];

            /** @var ProductInterface $searchProduct */
            $searchProduct = $this->amazonSearchProductFactory->create();
            $this->amazonSearchProductResource
                ->load($searchProduct, $searchProductId, \Ced\Amazon\Model\Product::COLUMN_ID);

            if ($searchProduct->getId() > 0) {
                /** @var \Ced\Amazon\Api\Data\ProductInterface $amazonProduct */
                $amazonProduct = $this->amazonProductFactory->create();
                $this->productResource
                    ->load($amazonProduct, $relationId, \Ced\Amazon\Model\Product::COLUMN_RELATION_ID);

                $amazonProduct->setRelationId($relationId);
                $amazonProduct->setAsin($searchProduct->getAsin());
                $amazonProduct->setManuallyAssignedFlag(true);

                $this->productResource->save($amazonProduct);
                $status = true;
            }
        }
        $result = $this->resultJsonFactory->create();
        $result->setData([
            "success" => $status
        ]);

        return $result;
    }
}
