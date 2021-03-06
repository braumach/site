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
 * @package   Ced_TradeMe
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\TradeMe\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;

/**
 * Class Save
 * @package Ced\TradeMe\Controller\Adminhtml\Profile
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_TradeMe::TradeMe';
    /**
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry;
    /**
     * @var \Ced\TradeMe\Helper\Cache
     */
    public $_cache;

    /**
     * @var \Ced\TradeMe\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    public $logger;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Ced\TradeMe\Helper\Cache $cache
     */
    public function __construct(
        Context $context,
        \Ced\TradeMe\Helper\Logger $logger,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\TradeMe\Helper\MultiAccount $multiAccountHelper
    )
    {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->logger = $logger;
        //$this->_cache = $cache;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @param string $idFieldName
     * @return mixed
     */
    protected function _initProfile($idFieldName = 'pcode')
    {
        $profileCode = $this->getRequest()->getParam($idFieldName);
        $profile = $this->_objectManager->get('Ced\TradeMe\Model\Profile');
        if ($profileCode) {
            $profile->loadByField('profile_code', $profileCode);
        }
        $this->getRequest()->setParam('is_trademe', 1);
        $this->_coreRegistry->register('current_profile', $profile);
        return $this->_coreRegistry->registry('current_profile');
    }

    public function execute()
    {
        $trademeAttribute = $trademeReqOptAttribute = [];
        $data = $this->_objectManager->create('Magento\Config\Model\Config\Structure\Element\Group')->getData();
        $redirectBack = $this->getRequest()->getParam('back', false);
        $pcode = $this->getRequest()->getParam('pcode', false);
        $profileData = $this->getRequest()->getPostValue();
        $accountId = $this->getRequest()->getParam('account_id');
        $category = isset($profileData['level_0']) ? $profileData['level_0'] : "";

        $profileData = json_decode(json_encode($profileData), 1);

        $profileProductsStr = $this->getRequest()->getParam('in_profile_products', null);
        if (strlen($profileProductsStr) > 0) {
            $profileProducts = explode(',', $profileProductsStr);
        } else {
            $profileProducts = [];
        }

        try {
            $profile = $this->_initProfile('pcode');
            if (!$profile->getId() && $pcode) {
                $this->messageManager->addErrorMessage(__('This Profile no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }

            if (isset($profileData['profile_code'])) {
                $pcode = $profileData['profile_code'];
                $profileCollection = $this->_objectManager->get('Ced\TradeMe\Model\Profile')->getCollection()->
                addFieldToFilter('profile_code', $profileData['profile_code']);
                if (count($profileCollection) > 0) {
                    $this->messageManager->addErrorMessage(__('This Profile Already Exist Please Change Profile Code'));
                    $this->_redirect('*/*/new');
                    return;
                }
            }

            $profile->addData($profileData);
            $profile->setProfileCategory(/*json_encode*/($category));

            // save attribute
            $reqAttribute = [];
            $optAttribute = [];
            if (isset($profileData['trademe_attributes'])) {
                $temAttribute = $this->unique_multidim_array($profileData['trademe_attributes'], 'trademe_attribute_name');

                if (!empty($temAttribute)) {
                    $temp1 = $temp2 = [];
                    foreach ($temAttribute as $item) {
                        if ($item['required']) {
                            $temp1['trademe_attribute_name'] = $item['trademe_attribute_name'];
                            $temp1['trademe_attribute_type'] = $item['trademe_attribute_type'];
                            $temp1['magento_attribute_code'] = $item['magento_attribute_code'];
                            if (isset($item['default'])) {
                                $temp1['default'] = $item['default'];
                            }
                            $temp1['required'] = $item['required'];
                            $reqAttribute[] = $temp1;
                        } else {
                            $temp2['trademe_attribute_name'] = $item['trademe_attribute_name'];
                            $temp2['trademe_attribute_type'] = $item['trademe_attribute_type'];
                            $temp2['magento_attribute_code'] = $item['magento_attribute_code'];
                            if (isset($item['default'])) {
                                $temp2['default'] = $item['default'];
                            }
                            $temp2['required'] = $item['required'];
                            $optAttribute[] = $temp2;
                        }
                    }
                    $trademetAttribute['required_attributes'] = $reqAttribute;
                    $trademeAttribute['optional_attributes'] = $optAttribute;


                    $profile->setCatDependAttribute(json_encode($trademetAttribute));
                } else {
                    $this->messageManager->addErrorMessage(__('Please map all trademe attributes.'));
                    $this->_redirect('*/*/new');
                    return;
                }
            }

            // save required and optional attribute
            $reqAttribute1 = [];
            $optAttribute1 = [];
            if (!empty($profileData['required_attributes'])) {
                $temAttribute1 = $this->unique_multidim_array($profileData['required_attributes'], 'trademe_attribute_name');
                $temp3 = $temp4 = [];
                foreach ($temAttribute1 as $item) {
                    if ($item['required']) {
                        $temp3['trademe_attribute_name'] = $item['trademe_attribute_name'];
                        $temp3['trademe_attribute_type'] = $item['trademe_attribute_type'];
                        $temp3['magento_attribute_code'] = $item['magento_attribute_code'];
                        if (isset($item['default'])) {
                            $temp3['default'] = $item['default'];
                        }
                        $temp3['required'] = $item['required'];
                        $reqAttribute1[] = $temp3;
                    } else {
                        $temp4['trademe_attribute_name'] = $item['trademe_attribute_name'];
                        $temp4['trademe_attribute_type'] = $item['trademe_attribute_type'];
                        $temp4['magento_attribute_code'] = $item['magento_attribute_code'];
                        if (isset($item['default'])) {
                            $temp4['default'] = $item['default'];
                        }
                        $temp4['required'] = 0;
                        $optAttribute1[] = $temp4;
                    }
                }
                $trademeReqOptAttribute['required_attributes'] = $reqAttribute1;
                $trademeReqOptAttribute['optional_attributes'] = $optAttribute1;

                $profile->setOptReqAttribute(json_encode($trademeReqOptAttribute));
            } else {
                $profile->setOptReqAttribute('');
            }

            // save category features
            $profile->setAccountId($accountId);
            $profileAttr = $this->multiAccountHelper->getProfileAttrForAcc($profile->getAccountId());
            //save profile
            $profile->save($profile);
            $profile->updateProducts($profileProducts, $profileAttr);

            if ($redirectBack && $redirectBack == 'edit') {
                $this->messageManager->addSuccessMessage(__('
		   		You Saved The Trade Me Profile And Its Products.
		   			'));
                $this->_redirect('*/*/edit', array(
                    'pcode' => $pcode,
                ));
            } else if ($redirectBack && $redirectBack == 'upload') {
                $this->messageManager->addSuccessMessage(__('
		   		You Saved The Trade Me Profile And Its Products. Upload Product Now.
		   			'));
                $this->_redirect('trademe/products/index', array(
                    'profile_id' => $profile->getId()
                ));
            } else {
                $this->messageManager->addSuccessMessage(__('
		   		You Saved The Trade Me Profile And Its Products.
		   		'));
                $this->_redirect('*/*/');
            }
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
            $this->_objectManager->create('Ced\TradeMe\Helper\Logger')->addError('In Save Profile: ' . $e->getMessage(), ['path' => __METHOD__]);
            $this->messageManager->addErrorMessage(__('
		   		Unable to Save Profile Please Try Again.
		   			' . $e->getMessage()));
            $this->_redirect('*/*/new');
        }

        return;
    }

    /**
     * @param $array
     * @param $key
     * @return array
     */
    function unique_multidim_array($array, $key)
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach ($array as $val) {
            if ($val['delete'] == 1)
                continue;

            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }
}