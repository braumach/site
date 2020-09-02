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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Strategy;

/**
 * Class Validate
 * @package Ced\Amazon\Controller\Adminhtml\Strategy
 */
class Validate extends \Ced\Amazon\Controller\Adminhtml\Strategy\Base
{
    public function execute()
    {
        $error = [
            'error' => false
        ];

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);

        if (!$this->validate()) {
            $error['error'] = true;
            $fields = implode("|", $this->invalid);
            $fields = !empty($fields) ? " Fields are invalid: [{$fields}]" : '';
            $messages[] = 'Invalid credentials. Unable to save the account.' . $fields;
            $error['messages'] = $messages;
        }

        $result->setData($error);
        return $result;
    }
}
