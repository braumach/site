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

namespace Ced\GXpress\Block\Adminhtml\Product;

/**
 * Class Massupload
 * @package Ced\GXpress\Block\Adminhtml\Product
 */
class Massupload extends \Magento\Backend\Block\Widget\Container
{
    /**
     * Massupload constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->setTemplate('Ced_GXpress::product/massupload.phtml');
    }

    /**
     * @return int
     */
    public function totalcount()
    {
        $totalChunk = $this->_backendSession->getUploadChunks();
        return count($totalChunk);
    }

    public function getAccountId()
    {
        return $this->_backendSession->getAccountId();
    }
}
