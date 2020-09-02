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
 * @category  Ced
 * @package   Ced_MPCatch
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\MPCatch\Controller\Adminhtml\Profile;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use Magento\Framework\DataObject;

/**
 * Class Save
 *
 * @package Ced\MPCatch\Controller\Adminhtml\Profile
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    public $registory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $catalogCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    public $categoryCollection;

    /**
     * @var \Ced\MPCatch\Model\ProfileProductFactory
     */
    public $profileProduct;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    public $moduleDataSetup;

    /**
     * @var \Ced\MPCatch\Model\ProfileFactory
     */
    public $profileFactory;

    /**
     * @var \Ced\MPCatch\Helper\Profile
     */
    public $profileHelper;

    /**
     * @var DataObject
     */
    public $data;

    public $logger;
    public $mpcatchCache;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registory,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogCollection,
        \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory $configurable,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\DataObject $data,
        \Psr\Log\LoggerInterface $logger,
        \Ced\MPCatch\Model\ProfileProductFactory $profileProduct,
        \Ced\MPCatch\Model\ProfileFactory $profileFactory,
        \Ced\MPCatch\Helper\Cache $mpcatchCache,
        \Ced\MPCatch\Helper\Profile $profileHelper
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->configStructure = $configStructure;
        $this->registory = $registory;
        $this->configFactory = $configFactory;
        $this->productConfigFactory = $configurable;
        $this->catalogCollection = $catalogCollection;
        $this->categoryCollection = $categoryCollection;
        $this->profileHelper = $profileHelper;
        $this->profileFactory = $profileFactory;
        $this->profileProduct = $profileProduct;
        $this->mpcatchCache = $mpcatchCache;
        $this->data = $data;
        $this->logger = $logger;
    }

    public function execute()
    {
        $this->logger->info('Saving Started');
        $profileId = null;
        $returnToEdit = true;

        if ($this->validate()) {
            try {
                $profile = $this->profileFactory->create()->load($this->data->getProfileId());
                $profile->addData($this->data->getData());
                
                $profile->save();
               
                $profile->removeProducts($profile->getMagentoCategory());
                $profile->addProducts($profile->getMagentoCategory());
                $profileId = $profile->getId();
                if($profileId) {
                    $this->mpcatchCache->removeValue(\Ced\MPCatch\Helper\Cache::PROFILE_CACHE_KEY . $profileId);
                }
                $this->messageManager->addSuccessMessage(__('Profile save successfully.'));
            } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
                $this->messageManager->addErrorMessage(__('Profile code already exists. '.$e->getMessage()));
            }    
            catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
            }
            
        }


        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($profileId) {
                $resultRedirect->setPath(
                    'mpcatch/profile/edit',
                    ['id' => $profileId, '_current' => true]
                );
            } else {
                $resultRedirect->setPath(
                    'mpcatch/profile/edit',
                    ['_current' => true]
                );
            }
        } else {
            $resultRedirect->setPath('mpcatch/profile/index');
        }
        $this->logger->info('Saving Ended');
        return $resultRedirect;
    }

    private function validate()
    {
  
        $generalInformation = $this->getRequest()->getParam('general_information');
        $offer_information = $this->getRequest()->getParam('offer_information');
        $mpcatch = $this->getRequest()->getParam('mpcatch');
        $store_categories = $this->getRequest()->getParam('store_categories');
        $mpcatchAttributes = $this->getRequest()->getParam('mpcatch_attributes');

        if (!empty($mpcatchAttributes)) {
            $mpcatchAttributes = $this->mergeAttributes($mpcatchAttributes, 'name');

            $requiredAttributes = $optionalAttributes = [];

            foreach ($mpcatchAttributes as $mpcatchAttribute_key => $mpcatchAttribute_value) {
                if (isset($mpcatchAttribute_value['delete']) and $mpcatchAttribute_value['delete']) {
                     continue;   
                }        
                if (isset($mpcatchAttribute_value['isMandatory']) and $mpcatchAttribute_value['isMandatory'] == 'true') {
                    $requiredAttributes[$mpcatchAttribute_key] = $mpcatchAttribute_value;
                } else {
                    $optionalAttributes[$mpcatchAttribute_key] = $mpcatchAttribute_value;
                    $optionalAttributes[$mpcatchAttribute_key]['isMandatory'] = 0;
                }
            }

            $this->data->setData('profile_required_attributes', json_encode($requiredAttributes));
            $this->data->setData('profile_optional_attributes', json_encode($optionalAttributes));
        }
        //$this->data->addData($offer_information);
        $this->data->addData($generalInformation);

        if (isset($mpcatch)) {
            $this->data->setData('profile_categories', json_encode($mpcatch));
            $this->data->setData('profile_category', end($mpcatch));
        }


        if (isset($store_categories['magento_category'])) {
            $this->data->setData('magento_category', json_encode($store_categories['magento_category']));
        }

        if (isset($generalInformation['profile_name'])) {
            $this->data->addData($generalInformation);
        }


        if (!$this->data->getProfileCode() or !$this->data->getProfileName()) {
            return false;
        }

        return true;
        
    }


    /**
     * @param $array
     * @param $key
     * @return array
     */
    private function mergeAttributes($attributes, $key)
    {

        $tempArray = [];
        $i = 0;
        $keyArray = [];

        if (!empty($attributes) and is_array($attributes)) {
            foreach ($attributes as $val) {
                if (isset($val['delete']) and $val['delete']  == 1) {
                    continue;
                }
                if (!in_array($val[$key], $keyArray)) {
                    $keyArray[$val[$key]] = $val[$key];
                    $tempArray[$val[$key]] = $val;
                }
                $i++;
            }
        }

        return $tempArray;
    }

}
