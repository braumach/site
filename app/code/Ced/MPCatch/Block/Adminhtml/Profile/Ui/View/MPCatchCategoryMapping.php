<?php

namespace Ced\MPCatch\Block\Adminhtml\Profile\Ui\View;

class MPCatchCategoryMapping extends \Magento\Backend\Block\Template
{
     /**
     * @var string
     */
    public $_template = 'Ced_MPCatch::profile/category/mpcatch_category_mapping.phtml';

    public $_objectManager;

    public $_coreRegistry;

    public $request;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Ced\MPCatch\Model\Profile $profile,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;
        $this->request = $request;
        parent::__construct($context, $data);
    }
    public function getCurrentProfile()
    {
        return $this->request->getParam('id', 0);
    }
    public function getMPCatchCategoryUrl()
    {
        return $this->_objectManager->get('\Magento\Backend\Model\UrlInterface')->getUrl('bonanza/category/fetch');
    }
    
    /**
     * Render form element as HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
}
