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

namespace Ced\GXpress\Block\Adminhtml\Account;

/**
 * Store switcher block
 *
 * @api
 * @since 100.0.2
 */
class Switcher extends \Magento\Backend\Block\Template
{
    /**
     * URL for store switcher hint
     */
    const HINT_URL = 'http://docs.magento.com/m2/ce/user_guide/configuration/scope.html';

    /**
     * Name of website variable
     *
     * @var string
     */
    protected $_defaultWebsiteVarName = 'website';

    /**
     * Name of store group variable
     *
     * @var string
     */
    protected $_defaultStoreGroupVarName = 'group';

    /**
     * Name of store variable
     *
     * @var string
     */
    protected $_defaultStoreVarName = 'account_id';

    /**
     * @var array
     */
    protected $_storeIds;

    /**
     * Url for store switcher hint
     *
     * @var string
     */
    protected $_hintUrl;

    /**
     * @var bool
     */
    protected $_hasDefaultOption = true;

    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'Ced_GXpress::accounts/switcher.phtml';

    /**
     * Website factory
     *
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * Store Group Factory
     *
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $_storeGroupFactory;

    /**
     * Store Factory
     *
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Ced\GXpress\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\GroupFactory $storeGroupFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\GroupFactory $storeGroupFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Ced\GXpress\Helper\MultiAccount $multiAccountHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_websiteFactory = $websiteFactory;
        $this->_storeGroupFactory = $storeGroupFactory;
        $this->_storeFactory = $storeFactory;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setUseConfirm(true);
        $this->setUseAjax(true);

        $this->setShowManageStoresLink(0);

        if (!$this->hasData('switch_websites')) {
            $this->setSwitchWebsites(false);
        }
        if (!$this->hasData('switch_store_groups')) {
            $this->setSwitchStoreGroups(false);
        }
        if (!$this->hasData('switch_store_views')) {
            $this->setSwitchStoreViews(true);
        }
        $this->setDefaultSelectionName(__('All Store Views'));
    }


    public function getAccounts()
    {
        $accounts = $this->multiAccountHelper->getAllAccounts();
        return $accounts;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWebsiteCollection()
    {
        $collections = $this->_websiteFactory->create()->getResourceCollection();

        $websiteId = $this->getWebsiteIds();
        if ($websiteId !== null) {
            $collections->addIdFilter($this->getWebsiteIds());
        }

        return $collections->load();
    }

    /**
     * @return array|\Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getWebsites()
    {
        $website = $this->_storeManager->getWebsites();
        if ($websiteId = $this->getWebsiteIds()) {
            $website = array_intersect_key($website, array_flip($websiteId));
        }
        return $website;
    }

    /**
     * @return bool
     */
    public function isWebsiteSwitchEnabled()
    {
        return (bool)$this->getData('switch_websites');
    }

    /**
     * @param $varName
     * @return $this
     */
    public function setWebsiteVarName($varNames)
    {
        $this->setData('website_var_name', $varNames);
        return $this;
    }

    /**
     * @return string
     */
    public function getWebsiteVarName()
    {
        if ($this->hasData('website_var_name')) {
            return (string)$this->getData('website_var_name');
        } else {
            return (string)$this->_defaultWebsiteVarName;
        }
    }

    /**
     * @param \Magento\Store\Model\Website $website
     * @return bool
     */
    public function isWebsiteSelected(\Magento\Store\Model\Website $websites)
    {
        return $this->getWebsiteId() === $websites->getId() && $this->getStoreId() === null;
    }

    /**
     * @return mixed
     */
    public function getWebsiteId()
    {
        if (!$this->hasData('website_id')) {
            $this->setData('website_id', (int)$this->getRequest()->getParam($this->getWebsiteVarName()));
        }
        return $this->getData('website_id');
    }

    /**
     * @param $website
     * @return \Magento\Store\Model\ResourceModel\Group\Collection
     */
    public function getGroupCollection($websites)
    {
        if (!$websites instanceof \Magento\Store\Model\Website) {
            $websites = $this->_websiteFactory->create()->load($websites);
        }
        return $websites->getGroupCollection();
    }

    /**
     * @param $website
     * @return \Magento\Store\Model\Store[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreGroups($websites)
    {
        if (!$websites instanceof \Magento\Store\Model\Website) {
            $websites = $this->_storeManager->getWebsite($websites);
        }
        return $websites->getGroups();
    }

    /**
     * @return bool
     */
    public function isStoreGroupSwitchEnabled()
    {
        return (bool)$this->getData('switch_store_groups');
    }

    /**
     * @param $varName
     * @return $this
     */
    public function setStoreGroupVarName($varName)
    {
        $this->setData('store_group_var_name', $varName);
        return $this;
    }

    /**
     * @return string
     */
    public function getStoreGroupVarName()
    {
        if ($this->hasData('store_group_var_name')) {
            return (string)$this->getData('store_group_var_name');
        } else {
            return (string)$this->_defaultStoreGroupVarName;
        }
    }

    /**
     * @param \Magento\Store\Model\Group $group
     * @return bool
     */
    public function isStoreGroupSelected(\Magento\Store\Model\Group $groups)
    {
        return $this->getStoreGroupId() === $groups->getId() && $this->getStoreGroupId() === null;
    }

    /**
     * @return mixed
     */
    public function getStoreGroupId()
    {
        if (!$this->hasData('store_group_id')) {
            $this->setData('store_group_id', (int)$this->getRequest()->getParam($this->getStoreGroupVarName()));
        }
        return $this->getData('store_group_id');
    }

    /**
     * @param $group
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    public function getStoreCollection($groups)
    {
        if (!$groups instanceof \Magento\Store\Model\Group) {
            $groups = $this->_storeGroupFactory->create()->load($groups);
        }
        $store = $groups->getStoreCollection();
        $_storeId = $this->getStoreIds();
        if (!empty($_storeId)) {
            $store->addIdFilter($_storeId);
        }
        return $store;
    }

    /**
     * @param $group
     * @return \Magento\Store\Model\ResourceModel\Store\Collection[]
     */
    public function getStores($group)
    {
        if (!$group instanceof \Magento\Store\Model\Group) {
            $group = $this->_storeManager->getGroup($group);
        }
        $stores = $group->getStores();
        if ($storeIds = $this->getStoreIds()) {
            foreach (array_keys($stores) as $storeId) {
                if (!in_array($storeId, $storeIds)) {
                    unset($stores[$storeId]);
                }
            }
        }
        return $stores;
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        if (!$this->hasData('store_id')) {
            $this->setData('store_id', (int)$this->getRequest()->getParam($this->getStoreVarName()));
        }
        return $this->getData('store_id');
    }

    /**
     * @param \Magento\Store\Model\Store $account
     * @return bool
     */
    public function isAccountSelected(\Magento\Store\Model\Store $account)
    {
        return $this->getStoreId() !== null && (int)$this->getStoreId() === (int)$account->getId();
    }

    /**
     * @return bool
     */
    public function isStoreSwitchEnabled()
    {
        return (bool)$this->getData('switch_store_views');
    }

    /**
     * @param $varName
     * @return $this
     */
    public function setStoreVarName($varNames)
    {
        $this->setData('store_var_name', $varNames);
        return $this;
    }

    /**
     * @return string
     */
    public function getStoreVarName()
    {
        if ($this->hasData('store_var_name')) {
            return (string)$this->getData('store_var_name');
        } else {
            return (string)$this->_defaultStoreVarName;
        }
    }

    /**
     * @return mixed|string
     */
    public function getSwitchUrl()
    {
        if ($urls = $this->getData('switch_url')) {
            return $urls;
        }
        return $this->getUrl(
            '*/*/*',
            [
                '_current' => true
            ]
        );
    }

    public function hasScopeSelected()
    {
        return $this->getStoreId() !== null || $this->getStoreGroupId() !== null || $this->getWebsiteId() !== null;
    }

    public function getCurrentSelectionName()
    {
        if (!($names = $this->getCurrentStoreName())) {
            if (!($names = $this->getCurrentStoreGroupName())) {
                if (!($names = $this->getCurrentWebsiteName())) {
                    $names = $this->getDefaultSelectionName();
                }
            }
        }
        return $names;
    }

    public function getCurrentWebsiteName()
    {
        if ($this->getWebsiteId() !== null) {
            $websites = $this->_websiteFactory->create();
            $websites->load($this->getWebsiteId());
            if ($websites->getId()) {
                return $websites->getName();
            }
        }
    }

    public function getCurrentStoreGroupName()
    {
        if ($this->getStoreGroupId() !== null) {
            $groups = $this->_storeGroupFactory->create();
            $groups->load($this->getStoreGroupId());
            if ($groups->getId()) {
                return $groups->getName();
            }
        }
    }

    public function getCurrentStoreName()
    {
        if ($this->getStoreId() !== null) {
            $stores = $this->_storeFactory->create();
            $stores->load($this->getStoreId());
            if ($stores->getId()) {
                return $stores->getName();
            }
        }
    }

    public function setStoreIds($storeId)
    {
        $this->_storeIds = $storeId;
        return $this;
    }

    public function getStoreIds()
    {
        return $this->_storeIds;
    }


    public function isShow()
    {
        return !$this->_storeManager->isSingleStoreMode();
    }


    protected function _toHtml()
    {
        if ($this->isShow()) {
            return parent::_toHtml();
        }
        return '';
    }


    public function hasDefaultOption($hasDefaultOptions = null)
    {
        if (null !== $hasDefaultOptions) {
            $this->_hasDefaultOption = $hasDefaultOptions;
        }
        return $this->_hasDefaultOption;
    }

    public function getHintUrl()
    {
        return self::HINT_URL;
    }

    public function getHintHtml()
    {
        $htm = '';
        $urls = $this->getHintUrl();
        if ($urls) {
            $htm = '<div class="admin__field-tooltip tooltip">' . '<a' . ' href="' . $this->escapeUrl(
                    $urls
                ) . '"' . ' onclick="this.target=\'_blank\'"' . ' title="' . __(
                    'What is this?'
                ) . '"' . ' class="admin__field-tooltip-action action-help"><span>' . __(
                    'What is this?'
                ) . '</span></a></span>' . ' </div>';
        }
        return $htm;
    }

    public function isUsingIframe()
    {
        if ($this->hasData('is_using_iframe')) {
            return (bool)$this->getData('is_using_iframe');
        }
        return false;
    }
}
