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
 
class Delete extends \Magento\Backend\App\Action
{
    /** @var \Ced\Amazon\Model\ResourceModel\Strategy\CollectionFactory  */
    public $strategy;

    /** @var \Ced\Amazon\Repository\Strategy  */
    public $repository;

    /** @var \Magento\Ui\Component\MassAction\Filter  */
    public $filter;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\Amazon\Repository\Strategy $repository,
        \Ced\Amazon\Model\ResourceModel\Strategy\CollectionFactory $strategy
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->strategy = $strategy;
        $this->filter = $filter;
    }

    /**
     * Delete the Attribute
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $isFilter = $this->getRequest()->getParam('filters');
        $ids = [];
        $message = "No strategy available to delete.";
        if (isset($id) && !empty($id)) {
            $ids[] = $id;
        } elseif (isset($isFilter)) {
            $collection = $this->filter->getCollection($this->strategy->create());
            $ids = $collection->getAllIds();
        }

        if (!empty($ids) && is_array($ids)) {
            $count = count($ids);
            foreach ($ids as $id) {
                $this->repository->delete($id);
            }

            $message = "{$count} Strategy(s) deleted successfully.";
        }

        $this->messageManager->addSuccessMessage($message);

        return $this->_redirect('amazon/strategy/index');
    }
}
