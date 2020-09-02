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
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Controller\Adminhtml\Strategy;

/**
 * Class Save
 *
 * @package Ced\Amazon\Controller\Adminhtml\Strategy
 */
class Save extends \Ced\Amazon\Controller\Adminhtml\Strategy\Base
{
    public function execute()
    {
        $response = [];
        $error = true;
        $strategyId = null;
        $type = null;

        $back = $this->getRequest()->getParam('back');
        $isAjax = $this->getRequest()->getParam('isAjax', false);

        if ($this->validate()) {
            $this->repository->save($this->strategy);
            $strategyId = $this->strategy->getId();

            $type = $this->strategy->getType();
            $this->save($type, $strategyId);

            $error = false;
            $response = [
                'id' => $this->strategy->getId(),
                'name' => $this->strategy->getData(\Ced\Amazon\Model\Strategy::COLUMN_NAME) .
                    " | id:" . $this->strategy->getId()
            ];
        }

        if (empty($isAjax)) {
            if ($strategyId) {
                $this->messageManager->addSuccessMessage('Amazon strategy saved successfully.');
            } else {
                $this->messageManager->addWarningMessage('Amazon strategy saving failed. Invalid data.');
            }

            $redirect = $this->resultRedirectFactory->create();
            if (isset($back) && $back == 'edit') {
                if ($strategyId) {
                    $redirect->setPath(
                        'amazon/strategy/edit',
                        ['id' => $strategyId, '_current' => true, 'type' => $type]
                    );
                } else {
                    $redirect->setPath(
                        'amazon/strategy/edit',
                        ['_current' => true, 'type' => $type]
                    );
                }
            } else {
                $redirect->setPath('amazon/strategy/index');
            }

            return $redirect;
        } else {
            /** @var \Magento\Framework\Controller\Result\Json  $result */
            $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
            $result->setData([
                'messages' => '',
                'error' => $error,
                'strategy' => $response,
            ]);

            return $result;
        }
    }
}
