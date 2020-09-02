<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCT_MASS_ACTIONS
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\ProductMassActions\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $settings = [];
    protected $messageManager;
    protected $_storeManager;
    protected $_objectManager;
    public $_productIndexColumn = 'entity_id';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Backend\App\ConfigInterface $backendConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        $this->messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;
        $this->_backendConfig = $backendConfig;
        $this->_localeDate = $localeDate;
        $this->productMetadata = $this->_objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $this->_productIndexColumn = $this->productMetadata->getEdition() == 'Enterprise' ? 'row_id' : 'entity_id';
        parent::__construct($context);
    }

    public function isEnabled() {
        return (int)$this->_backendConfig->getValue('itoris_productmassaction/general/enabled') &&
                count(explode('|', $this->_backendConfig->getValue('itoris_core/installed/Itoris_ProductMassActions'))) == 2;
    }
    
    public function getSqlString($con, $_pairs) {
        $pairs = [];
        foreach($_pairs as $key => $value) $pairs[] = '`'.$key.'`='.(is_null($value) ? 'NULL' : $con->quote($value));
        return implode(',', $pairs);
    }
}