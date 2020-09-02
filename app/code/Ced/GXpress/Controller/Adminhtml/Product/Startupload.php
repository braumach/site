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
 * @category  Ced
 * @package   Ced_GXpress
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GXpress\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Ced\GXpress\Helper\GXpress;
use Ced\GXpress\Helper\Data;
use Ced\GXpress\Helper\Logger;

/**
 * Class Startupload
 * @package Ced\GXpress\Controller\Adminhtml\Product
 */
class Startupload extends Action
{
    /** @var Logger $logger */
    public $logger;

    /** @var GXpress $gxpressHelper */
    public $gxpressHelper;

    /** @var Data $dataHelper */
    public $dataHelper;

    /** @var \Ced\GXpress\Helper\MultiAccount $multiAccountHelper */
    protected $multiAccountHelper;

    /** @var PageFactory $resultPageFactory */
    public $resultPageFactory;

    /** @var \Magento\Framework\Registry $_coreRegistry */
    protected $_coreRegistry;

    /** @var JsonFactory $resultJsonFactory */
    public $resultJsonFactory;

    /**
     * Startupload constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param GXpress $gxpressHelper
     * @param Data $dataHelper
     * @param Logger $logger
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Ced\GXpress\Helper\MultiAccount $multiAccountHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        GXpress $gxpressHelper,
        Data $dataHelper,
        Logger $logger,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\GXpress\Helper\MultiAccount $multiAccountHelper
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->gxpressHelper = $gxpressHelper;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->_coreRegistry = $coreRegistry;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $message = [];
        $message['error'] = "";
        $message['success'] = "";
        $productError = "";
        $successids = [];
        $childProductIds = [];
        $key = $this->getRequest()->getParam('index');
        $totalChunk = $this->_session->getUploadChunks();

        $index = $key + 1;
        if (count($totalChunk) <= $index) {
            $this->_session->unsUploadChunks();
        }
        try {
            if (isset($totalChunk[$key])) {
                $ids = $totalChunk[$key];
                foreach ($ids as $accountId => $prodIds) {
                    $account = $this->_objectManager->get('\Ced\GXpress\Model\Accounts')->load($accountId);
                    $status = $account->getData('account_status');

                    if($status) {
                        if (!is_array($prodIds)) {
                            $prodIds[] = $prodIds;
                        }
                        if ($this->_coreRegistry->registry('gxpress_account'))
                            $this->_coreRegistry->unregister('gxpress_account');
                        $this->multiAccountHelper->getAccountRegistry($accountId);
                        $itemIdAccAttr = $this->multiAccountHelper->getItemIdAttrForAcc($accountId);
                        $listingErrorAccAttr = $this->multiAccountHelper->getProdListingErrorAttrForAcc($accountId);
                        $prodStatusAccAttr = $this->multiAccountHelper->getProdStatusAttrForAcc($accountId);
                        $this->dataHelper->updateAccountVariable();
                        $this->gxpressHelper->updateAccountVariable();
                        $productErrors = array();
                        $successids[$accountId] = array();
                        foreach ($prodIds as $id) {
                            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);

                            if ($product->getTypeId() == 'configurable') {
                                $productType = $product->getTypeInstance();
                                $configProd = $productType->getUsedProducts($product);

                                foreach ($configProd as $childprod) {
                                    $finaldata = $this->gxpressHelper->prepareData($childprod->getId());
                                    $childProductIds[] = $finaldata;
                                    $listingError = $this->preapareResponse($product->getEntityId(), $product->getSku(), $finaldata['error']);
                                    $productErrors = array_merge($productErrors,json_decode($listingError,true));
                                    $childprod->setData($prodStatusAccAttr, 2);
                                    $childprod->setData($listingErrorAccAttr, $listingError);
                                    $childprod->getResource()->saveAttribute($childprod, $prodStatusAccAttr)->saveAttribute($childprod, $listingErrorAccAttr);

                                    if(isset($finaldata['error']) && sizeof($finaldata['error']) > 0) {
                                        $productError .= "|" . json_encode($finaldata['error'], true);
                                    }
                                }
                                $productError = explode("|", $productError);
                                unset($productError[0]);

                                foreach ($productError as $productErrorJson) {
                                    $error = json_decode($productErrorJson, true);
                                    if(isset($error) & sizeof($error) > 0) {
                                        $message['error'] = json_decode($productErrorJson, true);
                                    }
                                }

                                if(!(isset($productErrors) && sizeof($productErrors) > 0)) {
                                    $listingError = $this->preapareResponse($product->getEntityId(), $product->getSku(), 'valid');
                                    array_push($successids[$accountId],$id);
                                    $product->setData($prodStatusAccAttr, 2);
                                    $product->setData($listingErrorAccAttr, $listingError);
                                    $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr);
                                }
                                $productErrors = json_encode($productErrors);
                                $product->setData($prodStatusAccAttr, 2);
                                $product->setData($listingErrorAccAttr, $productErrors);
                                $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr);
                            } else {
                                $finaldata = $this->gxpressHelper->prepareData($id, 1);
                                if (isset($finaldata['error']) && sizeof($finaldata['error']) > 0) {
                                    $product->setData($prodStatusAccAttr, 2);
                                    $listingError = $this->preapareResponse($product->getEntityId(), $product->getSku(), $finaldata['error']);
                                    $product->setData($listingErrorAccAttr, $listingError);
                                    $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr);
                                    $message['error'] = $finaldata['error'];
                                } else {
                                    $listingError = $this->preapareResponse($product->getEntityId(), $product->getSku(), 'valid');
                                    array_push($successids[$accountId],$id);
                                    $product->setData($prodStatusAccAttr, 2);
                                    $product->setData($listingErrorAccAttr, $listingError);
                                    $product->getResource()->saveAttribute($product, $prodStatusAccAttr)->saveAttribute($product, $listingErrorAccAttr);
                                }

                            }
                        }
                    } else {
                        $message['error'] =  $account->getData('account_code') . " is disabled";
                    }
                }

                if (!empty($successids) && isset($successids[$accountId]) && sizeof($successids[$accountId]) > 0) {
                    $responseData = $this->dataHelper->createProductOnGXpress($successids);
                    if(isset($responseData['success']) && sizeof($responseData['success']) > 0) {
                        $message['success'] = /*"Batch " . $index . ": " .*/ implode(', ', $responseData['success']['sku']) . " successfully uploaded";
                    }

                    if(isset($responseData['error']) && sizeof($responseData['error']) > 0) {
                        $message['error'] = /*"Batch " . $index . ": " .*/ implode(', ', $responseData['error']['sku']) . " not uploaded";
                    }
                }
                $errMsg = '';
                if (!empty($message['error']) && is_array($message['error'])) {
                    foreach ($message['error'] as $key => $value) {
                        $errMsg .= "SKU " . $key . ": " . json_encode($value);
                    }
                    $message['error'] = $errMsg;
                }
            } else {
                $message['error'] = "Batch " . $index . ": " . json_encode($message['error']) . " included Product(s) data not found.";
            }
        } catch (\Exception $e) {
            /*echo "<pre>";
            print_r($e->getMessage());
            die("KOL");*/
            $message['error'] = $e->getMessage();
            $this->logger->addError($message['error'], ['path' => __METHOD__]);
        }

        return $resultJson->setData($message);
    }

    public function getErrors($invPriceSyncOnGXpress)
    {
        $message = [];
        if (!isset($invPriceSyncOnGXpress->LongMessage)) {
            foreach ($invPriceSyncOnGXpress as $errorMessage) {
                $message[] = $errorMessage->LongMessage;
            }
        } else {
            $message[] = $invPriceSyncOnGXpress->LongMessage;
        }
        return implode(', ', $message);
    }

    public function preapareResponse($id = null, $sku, $errors)
    {
        $response = [];
        if (is_array($errors)) {
            foreach ($errors as $key => $error) {
                $response[$key] =
                    array(
                        "id" => $id,
                        "sku" => $sku,
                        "url" => "#",
                        'errors' => array($error)
                    );
            }
        }
        return json_encode($response);
    }
}
