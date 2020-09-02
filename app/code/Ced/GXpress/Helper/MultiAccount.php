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

namespace Ced\GXpress\Helper;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class MultiAccount extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Ced\GXpress\Model\Accounts
     */
    protected $accountModel;

    /**
     * @var \Ced\GXpress\Model\Profile
     */
    protected $profileModel;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public $eavAttribute;

    /** @var EavSetup $eavSetup */
    public $eavSetup;

    /**
     * @var \Ced\GXpress\Model\ResourceModel\Accounts\Colection
     */
    protected $accountsCollectionFactory;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\GXpress\Model\AccountsFactory $accounts,
        \Ced\GXpress\Model\ProfileFactory $profile,
        \Ced\GXpress\Model\ResourceModel\Accounts\CollectionFactory $accountsCollectionFactory,
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute
    )
    {
        parent::__construct($context);
        $this->accountModel = $accounts;
        $this->accountsCollectionFactory = $accountsCollectionFactory;
        $this->profileModel = $profile;
        $this->_coreRegistry = $coreRegistry;
        $this->eavAttribute = $eavAttribute;
        $this->eavSetup = $eavSetupFactory->create(['setup' => $setup]);
    }

    public function createProfileAttribute($accId = null, $accName = null) {
        $attributeCode = 'gxpress_profile_'.$accId;
        $attributeLabel = 'GXpress ' . $accName . ' Profile';
        if (!$this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
            $this->eavSetup->addAttribute(
                'catalog_product',
                $attributeCode,
                [
                    'group' => 'gxpress',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => $attributeLabel,
                    'backend' => '',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'user_defined' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }

        $attributeCode = 'gxpress_item_'.$accId;
        $attributeLabel = 'GXpress ' . $accName . ' Item ID';
        if (!$this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
            $this->eavSetup->addAttribute(
                'catalog_product',
                $attributeCode,
                [
                    'group' => 'gxpress',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => $attributeLabel,
                    'backend' => '',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'user_defined' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }

        $attributeCode = 'gxpress_prod_status_'.$accId;
        $attributeLabel = 'GXpress ' . $accName . ' Product Status';
        if (!$this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
            $this->eavSetup->addAttribute(
                'catalog_product',
                $attributeCode,
                [
                    'group' => 'gxpress',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => $attributeLabel,
                    'backend' => '',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'user_defined' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }

        $attributeCode = 'gxpress_listing_error_'.$accId;
        $attributeLabel = 'GXpress ' . $accName . ' Product Validation';
        if (!$this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
            $this->eavSetup->addAttribute(
                'catalog_product',
                $attributeCode,
                [
                    'group' => 'gxpress',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => $attributeLabel,
                    'backend' => '',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'user_defined' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );
        }
    }

    public function getAccountRegistry($accId = null) {
        /** @var \Ced\GXpress\Model\Accounts $account */
        $account = $this->accountModel->create();

        if (isset($accId) and $accId > 0) {
            $account = $account->load($accId);
        }


        if(!$this->_coreRegistry->registry('gxpress_account'))
            $this->_coreRegistry->register('gxpress_account', $account);

        return $this->_coreRegistry->registry('gxpress_account');
    }

    public function getProfileAttrForAcc($accId = null) {
        $attributeCode = '';
        if($accId > 0) {
            $attributeCode = 'gxpress_profile_' . $accId;
        } else {
            $attributeCode = '';
        }
        return $attributeCode;
    }

    public function getItemIdAttrForAcc($accId = null) {
        $attributeCode = '';
        if($accId > 0) {
            $attributeCode = 'gxpress_item_' . $accId;
        } else {
            $attributeCode = '';
        }
        return $attributeCode;
    }

    public function getProdStatusAttrForAcc($accId = null) {
        $attributeCode = '';
        if($accId > 0) {
            $attributeCode = 'gxpress_prod_status_' . $accId;
        } else {
            $attributeCode = '';
        }
        return $attributeCode;
    }

    public function getProdListingErrorAttrForAcc($accId = null) {
        $attributeCode = '';
        if($accId > 0) {
            $attributeCode = 'gxpress_listing_error_' . $accId;
        } else {
            $attributeCode = '';
        }
        return $attributeCode;
    }

    public function getAccountRegistryByPId($profileId = null) {
        $profile = $this->profileModel->create()->load($profileId);
        $accId = $profile->getAccountId();
        $account = $this->accountModel->create();

        if (isset($accId) and $accId > 0) {
            $account = $account->load($accId);
        }

        if(!$this->_coreRegistry->registry('gxpress_account'))
            $this->_coreRegistry->register('gxpress_account', $account);

        return $this->_coreRegistry->registry('gxpress_account');
    }

    public function getAllAccounts($onlyActive = false) {
        if($onlyActive)
            $accountCollection = $this->accountsCollectionFactory->create()->addFieldToFilter('account_status', 1);
        else
            $accountCollection = $this->accountsCollectionFactory->create();
        return $accountCollection;
    }

    public function getAllProfileAttr() {
        $attributeCodes = array();
        $accounts = $this->accountsCollectionFactory->create();
        foreach ($accounts as $account) {
            $accId = $account->getId();
            if($accId > 0) {
                $attributeCodes[] = 'gxpress_profile_' . $accId;
            }
        }
        return $attributeCodes;
    }
}