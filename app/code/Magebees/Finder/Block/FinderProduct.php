<?php
namespace Magebees\Finder\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

class FinderProduct extends \Magento\Catalog\Block\Product\ListProduct {

	/**
     * Product collection model
     *
     * @var Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $_productCollection;

    /**
     * Initialize
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param array $data
     */
    public function __construct(
    \Magento\Catalog\Block\Product\Context $context, \Magento\Framework\Data\Helper\PostHelper $postDataHelper, \Magento\Catalog\Model\Layer\Resolver $layerResolver, CategoryRepositoryInterface $categoryRepository, \Magento\Framework\Url\Helper\Data $urlHelper, \Magento\Catalog\Model\CategoryFactory $categoryFactory,\Magento\Catalog\Model\Session $catalogSession,\Magebees\Finder\Helper\Data $finderHelper,\Magebees\Finder\Model\YmmvalueFactory $ymmvalueFactory,
	
		array $data = []
    ) {
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
		$this->_catalogSession = $catalogSession;
		$this->finderHelper = $finderHelper;
		$this->_ymmvalueFactory = $ymmvalueFactory;
        $this->pageConfig->getTitle()->set(__($this->getPageTitle()));//set Page title
	}

    
	/**
     * Get product collection
     */
	protected function _getProductCollection()
    {
		if ($this->_productCollection === null) {
            $layer = $this->getLayer();
            /* @var $layer \Magento\Catalog\Model\Layer */
        	$this->_productCollection = $layer->getProductCollection();
		}
		return $this->_productCollection;
    }
	
	/**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    public function getLoadedProductCollection()
    {
        return $this->_getProductCollection();
    }

    /* Get the configured title of section */
    public function getPageTitle() {
		$path = trim($this->getRequest()->getRequestString(),'/');
		$finderId = $this->finderHelper->getFinderId($path);
		$session = $this->_catalogSession;
        $name    = 'mbfinder_' . $finderId;
		$values = $session->getData($name);
		if (!is_array($values)){
            return "Search Result Page";    
        }
		$title = array();
		
		foreach ($values as $key => $value) {
            if (!empty($value) && is_numeric($key)){
                $valueModel =  $this->_ymmvalueFactory->create()->load($value);
				
                if ($valueModel->getId()){
                    $title[] = $valueModel->getValue();                
                }
            }
        }
		$title = implode(', ',$title);
		$res = $this->_scopeConfig->getValue('finder/general/finderpage_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return $res." "."'".$title."'";
	} 
	//public function getProdcutIdsArr($element){return $element['entity_id'];}
	
	public function getCurrentStore(){
		return $this->_storeManager->getStore(); // give the information about current store
	}
}