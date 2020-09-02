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
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Block\Adminhtml\Strategy;

class Js extends \Magento\Backend\Block\Template
{
    protected $_template = 'Ced_Amazon::strategy/js.phtml';

    /** @var \Magento\Framework\App\RequestInterface  */
    public $request;

    /**
     * Js constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->request = $context->getRequest();
        parent::__construct($context, $data);
    }

    public function getStrategyId()
    {
        $id = $this->request->getParam('id');
        return $id;
    }
}
