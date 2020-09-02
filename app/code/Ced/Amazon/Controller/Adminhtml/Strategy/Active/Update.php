<?php
namespace Ced\Amazon\Controller\Adminhtml\Strategy\Active;

/**
 * Class Update
 * @package Ced\Amazon\Controller\Adminhtml\Strategy\Status
 */
class Update extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /** @var \Ced\Amazon\Model\ResourceModel\Strategy\CollectionFactory  */
    public $strategy;

    /**
     * MassStatus constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Ced\Amazon\Model\ResourceModel\Strategy\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\Amazon\Model\ResourceModel\Strategy\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->strategy = $collectionFactory;
    }

    public function execute()
    {
        $filters = $this->getRequest()->getParam('filters');
        $active = $this->getRequest()->getParam('active', 0);
        if (isset($filters)) {
            $active = $active == 1 ? $active : 0;
            /** @var \Ced\Amazon\Model\ResourceModel\Strategy\Collection $collection */
            $collection = $this->filter->getCollection($this->strategy->create());

            /** @var \Ced\Amazon\Model\Strategy $item */
            foreach ($collection as $item) {
                $item->setData(\Ced\Amazon\Model\Strategy::COLUMN_ACTIVE, $active);
            }

            $collection->save();

            $this->messageManager
                ->addSuccessMessage(__('Activated/Deactivated %1 record(s).', $collection->getSize()));
        }

        return $this->_redirect('*/*/index');
    }
}
