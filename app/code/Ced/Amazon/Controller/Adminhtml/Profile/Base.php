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

namespace Ced\Amazon\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action;
use Ced\Amazon\Api\Service\ConfigServiceInterface;
use Ced\Amazon\Api\Data\ProfileInterface;

abstract class Base extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Amazon::profile';

    const PROFILE_ATTRIBUTES = "amazon_attributes";

    /** @var \Magento\Ui\Component\MassAction\Filter */
    public $filter;

    public $session;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $catalog;

    /** @var \Ced\Amazon\Repository\Profile\Product */
    public $product;

    /** @var \Ced\Amazon\Helper\Product */
    public $productHelper;

    /**
     * @var ProfileInterface
     */
    public $profile;

    /**
     * @var \Ced\Amazon\Repository\Profile
     */
    public $repository;

    /**
     * @var \Ced\Amazon\Repository\Strategy\Assignment
     */
    public $strategyRepository;

    /** @var ConfigServiceInterface */
    public $config;

    /** @var \Ced\Amazon\Helper\Logger */
    public $logger;

    /** @var \Magento\Framework\DataObject */
    public $data;

    /** @var \Magento\Framework\DataObject */
    public $validation;

    /** @var \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory $queueDataFactory */
    public $queueDataFactory;

    /** @var \Ced\Amazon\Api\QueueRepositoryInterface $queue */
    public $queue;

    /**
     * TODO: move validate to repository
     * Base constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\DataObjectFactory $data
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogCollection
     * @param \Ced\Amazon\Repository\Profile\Product $productRepository
     * @param \Ced\Amazon\Repository\Profile $repository
     * @param \Ced\Amazon\Repository\Strategy\Assignment $assignmentRepository
     * @param ProfileInterface $profile
     * @param \Ced\Amazon\Helper\Product $product
     * @param ConfigServiceInterface $config
     * @param \Ced\Amazon\Helper\Logger $logger
     * @param \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory $queueDataFactory
     * @param \Ced\Amazon\Api\QueueRepositoryInterface $queue
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\DataObjectFactory $data,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogCollection,
        \Ced\Amazon\Repository\Profile\Product $productRepository,
        \Ced\Amazon\Repository\Profile $repository,
        \Ced\Amazon\Repository\Strategy\Assignment $assignmentRepository,
        ProfileInterface $profile,
        \Ced\Amazon\Helper\Product $product,
        ConfigServiceInterface $config,
        \Ced\Amazon\Helper\Logger $logger,
        \Ced\Amazon\Api\Data\Queue\DataInterfaceFactory $queueDataFactory,
        \Ced\Amazon\Api\QueueRepositoryInterface $queue
    )
    {
        parent::__construct($context);
        $this->filter = $filter;
        $this->catalog = $catalogCollection;
        $this->config = $config;
        $this->logger = $logger;

        $this->repository = $repository;
        $this->strategyRepository = $assignmentRepository;
        $this->profile = $profile;

        $this->product = $productRepository;
        $this->productHelper = $product;
        $this->data = $data->create();
        $this->validation = $data->create();

        $this->queueDataFactory = $queueDataFactory;
        $this->queue = $queue;
        $this->session = $context->getSession();
    }

    /**
     * Validating post profile data.
     * @param bool $setErrors
     * @return bool
     */
    public function validate($setErrors = false)
    {
        $status = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_STATUS, 0);
        $status = ($status === 'true' || $status == '1') ? 1 : 0;
        $this->data->setData(
            \Ced\Amazon\Model\Profile::COLUMN_STATUS,
            $status
        );

        $this->data->setData(
            \Ced\Amazon\Model\Profile::COLUMN_NAME,
            $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_NAME)
        );

        $category = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_CATEGORY);
        if (!empty($category)) {
            $category = explode('_', $category);
            if (isset($category[0]) && !empty($category[0])) {
                $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_CATEGORY, $category[0]);
            }
        }

        // Saving sub_category array
        $subCategory = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_SUB_CATEGORY);
        if (isset($subCategory)) {
            if (isset($subCategory[0]) && is_array($subCategory)) {
                $subCategory = explode('_', $subCategory[0], 2);
                // On changing category
                $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_SUB_CATEGORY, $subCategory[1]);
            } else {
                $subCategory = explode('_', $subCategory, 2);
                // Saving already saved category
                $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_SUB_CATEGORY, $subCategory[1]);
            }
        }

        // Saving marketplace array
        $regions = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_MARKETPLACE);
        if (!empty($regions)) {
            if (is_array($regions)) {
                $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_MARKETPLACE, implode(',', $regions));

            } else {
                $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_MARKETPLACE, $regions);
            }
        }
        $appId = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_ACCOUNT_ID);
        if (isset($appId) && !empty($appId)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_ACCOUNT_ID, $appId);
        }

        $filter = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_FILTER);

        if (isset($filter) && !empty($filter)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_FILTER, $filter);
        }

        $id = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_ID);
        if (!empty($id)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_ID, $id);
        }

        $storeId = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_STORE_ID);
        if (isset($storeId)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_STORE_ID, $storeId);
        }

        $barcode = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_BARCODE_EXEMPTION, 0);
        if (isset($barcode)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_BARCODE_EXEMPTION, $barcode);
        }

        $strategy = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_STRATEGY, 0);
        if (isset($strategy)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_STRATEGY, $strategy);
        }

        $strategyInventory = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_STRATEGY_INVENTORY, null);
        if (isset($strategyInventory)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_STRATEGY_INVENTORY, $strategyInventory);
        }

        $strategyPrice = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_STRATEGY_PRICE, null);
        if (isset($strategyPrice)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_STRATEGY_PRICE, $strategyPrice);
        }
        $magentoCategory = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_MAGENTO_CATEGORY, 0);
        if (isset($magentoCategory)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_MAGENTO_CATEGORY, $magentoCategory);
        }

        $strategyShipping = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_STRATEGY_SHIPPING, null);
        if (isset($strategyShipping)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_STRATEGY_SHIPPING, $strategyShipping);
        }

        $strategyAttribute = $this->getRequest()->getParam(\Ced\Amazon\Model\Profile::COLUMN_STRATEGY_ATTRIBUTE, null);
        if (isset($strategyAttribute)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_STRATEGY_ATTRIBUTE, $strategyAttribute);
        }

        $categoryId = $this->getRequest()
            ->getParam(\Ced\Amazon\Api\Data\Strategy\AssignmentInterface::COLUMN_CATEGORY_ID, null);
        if (isset($categoryId) && !empty($categoryId) &&
            $categoryId != \Ced\Amazon\Model\Source\Strategy\Assignment\Category::CATEGORY_EMPTY) {
            // Get or create assignment strategy and set the profile category
            /** @var \Ced\Amazon\Api\Data\Strategy\AssignmentInterface $strategyAssignment */
            $strategyAssignment = $this->strategyRepository->getOrCreateAssignmentStrategyByCategoryId($categoryId);
            if (isset($strategyAssignment) && $strategyAssignmentId = $strategyAssignment->getStrategyId()) {
                $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_STRATEGY_ASSIGNMENT, $strategyAssignmentId);
            }
        }

        $type = $this->getRequest()->getParam(
            \Ced\Amazon\Model\Profile::COLUMN_TYPE,
            \Ced\Amazon\Model\Source\Profile\Type::TYPE_AUTO
        );
        if (isset($type)) {
            $this->data->setData(\Ced\Amazon\Model\Profile::COLUMN_TYPE, $type);
        }

        $messages = [];
        $valid = true;
        // Validating required values
        foreach (\Ced\Amazon\Model\Profile::COLUMN_REQUIRED as $column) {
            if (empty($this->data->getData($column))) {
                $valid = false;
                $error = "Invalid data provided: {$column}.";
                if ($setErrors == false) {
                    $this->messageManager->addErrorMessage($error);
                } else {
                    $messages[] = $error;
                }
            }
        }

        $this->validation->setData('messages', $messages);

        return $valid;
    }

    /**
     * Add attribute mapping
     */
    public function addAttributes()
    {
        // TODO: continue; FIX NAME SET AS ATTRIBUTE CODE
        $attributes = $this->getRequest()->getParam(self::PROFILE_ATTRIBUTES);
        if (!empty($attributes) && is_array($attributes)) {
            $attributes = $this->merge($attributes, 'value');
            $requiredAttributes = $optionalAttributes = [];
            foreach ($attributes as $attributeId => $attribute) {
                if (isset($attribute['minOccurs']) && $attribute['minOccurs'] == 1) {
                    $requiredAttributes[$attributeId] = $attribute;
                } else {
                    $optionalAttributes[$attributeId] = $attribute;
                    $optionalAttributes[$attributeId]['minOccurs'] = 0;
                }
            }

            // Fixing barcode in exemption
            if (isset($requiredAttributes["StandardProductID_Value"]) &&
                $this->data->getData(\Ced\Amazon\Model\Profile::COLUMN_BARCODE_EXEMPTION)) {
                $optionalAttributes["StandardProductID_Value"] = $requiredAttributes["StandardProductID_Value"];
                $optionalAttributes["StandardProductID_Value"]['minOccurs'] = 0;
                unset($requiredAttributes["StandardProductID_Value"]);
            }

            $this->data->setData(
                \Ced\Amazon\Model\Profile::COLUMN_REQUIRED_ATTRIBUTES,
                $requiredAttributes
            );

            $this->data->setData(
                \Ced\Amazon\Model\Profile::COLUMN_OPTIONAL_ATTRIBUTES,
                $optionalAttributes
            );
        }
    }

    /**
     * Merging attribute mapping.
     * @param $attributes
     * @param $key
     * @return array
     */
    private function merge($attributes, $key)
    {
        $tempArray = [];
        $i = 0;
        $keyArray = [];

        if (!empty($attributes) && is_array($attributes)) {
            foreach ($attributes as $attribute) {
                if ((isset($val['delete']) && $attribute['delete'] == 1) || empty($attribute['value'])) {
                    continue;
                }

                if (!in_array($attribute[$key], $keyArray)) {
                    // decoding attribute options
                    if (isset($attribute['restriction']['optionValues']) &&
                        !empty($attribute['restriction']['optionValues'])) {
                        $data = htmlspecialchars_decode($attribute['restriction']['optionValues']);
                        $data = json_decode($data, true);
                        if (!empty($data) && is_array($data)) {
                            $options = $data;
                        } else {
                            $options = [];
                        }

                        $attribute['restriction']['optionValues'] = $options;
                    }

                    $keyArray[$attribute[$key]] = $attribute[$key];
                    $tempArray[$attribute[$key]] = $attribute;
                }
                $i++;
            }
        }

        return $tempArray;
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Amazon::profile');
    }
}
