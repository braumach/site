<?php
namespace Magebees\Finder\Model\ResourceModel\Fulltext;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Search\Adapter\Mysql\Adapter;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\Search\Response\Aggregation\Value;
use Magento\Framework\Search\Response\QueryResponse;

/**
 * Fulltext Collection
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
{
    /** @var  QueryResponse */
    protected $queryResponse;

    /**
     * Catalog search data
     *
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory = null;

    /**
     * @var \Magento\Framework\Search\Request\Builder
     */
    private $requestBuilder;

    /**
     * @var \Magento\Search\Model\SearchEngine
     */
    private $searchEngine;

    /** @var string */
    private $queryText;

    /** @var string|null */
    private $order = null;

    /** @var string */
    private $searchRequestName;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory
     */
    private $temporaryStorageFactory;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Search\Model\QueryFactory $catalogSearchData,
        \Magento\Framework\Search\Request\Builder $requestBuilder,
        \Magento\Search\Model\SearchEngine $searchEngine,
        \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory,
        \Magento\Framework\App\Request\Http $urlInterfaceObj,
        \Magebees\Finder\Helper\Data $finderHelper,
        \Magebees\Finder\Model\FinderFactory $finderFactory,
        \Magebees\Finder\Model\MapvalueFactory $mapvalueFactory,
        \Magebees\Finder\Model\YmmvalueFactory $ymmvalueFactory,
        \Magebees\Finder\Model\UniversalProduct $universalProduct,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        $searchRequestName = 'catalog_view_container'
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $catalogSearchData,
            $requestBuilder,
            $searchEngine,
            $temporaryStorageFactory,
            $connection
        );
        $this->requestBuilder = $requestBuilder;
        $this->urlInterfaceObj = $urlInterfaceObj;
        $this->_finderHelper = $finderHelper;
        $this->_finderFactory = $finderFactory;
        $this->_ymmvalueFactory = $ymmvalueFactory;
        $this->_mapvalueFactory = $mapvalueFactory;
        $this->universalProduct = $universalProduct;
        $this->scopeConfig = $scopeConfig;
    }
        
    public function getProdcutSkusArr($element)
    {
        return $element['sku'];
    }
    
    /**
     * @inheritdoc
     */
    protected function _renderFiltersBefore()
    {
        $urlstring = $this->urlInterfaceObj->getRequestString();
        $search = strpos($urlstring, 'finder');
        $reset = $this->_finderHelper->resetFinder($urlstring);
        $currentEngine=$this->_finderHelper->getCurrentSearchEngine();
        $IsElasticSearchEnabled=$this->_finderHelper->IsElasticSearch();

        if ($search) {
            if (!$reset) {
                $skus = ['0'=>''];
            } else {
                $finderId = $this->_finderHelper->getFinderId($urlstring);
                $finder = $this->_finderFactory->create()->load($finderId);
                $path = $finder->getYmmValueFromPath($urlstring);

                $current  = $finder->getSavedValue('current');
                if ($path!=$current) {
                    $dropdowns = $finder->getDropdownsByCurrent($path);
                    $finder->saveDropDownValues($dropdowns);
                }

                $skus = $product_skus = [];
                $last = $finder->getSavedValue('last');
                $product_string = "";
                /*if ($last) {
                    $product_string = $this->_mapvalueFactory->create()->load($last, 'ymm_value_id')->getSku();
                    $product_skus = explode("|", $product_string);
                } elseif ($finder->getSavedValue('current')) {
                    $this->getValues($finder->getSavedValue('current'));// for get sku if all dropdown not selected
                    foreach ($this->_parent_ids as $ymm_value_id) {
                        $new_skus = $this->_mapvalueFactory->create()->load($ymm_value_id, 'ymm_value_id')->getSku();
                        $new = explode("|", $new_skus);
                        $product_skus = array_merge($product_skus, $new);
                    }
                }*/


                if ($last) {
                    if($IsElasticSearchEnabled)
                    {
                         $product_string = $this->_mapvalueFactory->create()->load($last, 'ymm_value_id')->getProductId();
                    }
                    else
                    {
                         $product_string = $this->_mapvalueFactory->create()->load($last, 'ymm_value_id')->getSku();
                    }
                    $product_skus = explode("|", $product_string);
                } elseif ($finder->getSavedValue('current')) {
                    $this->getValues($finder->getSavedValue('current'));// for get sku if all dropdown not selected
                    foreach ($this->_parent_ids as $ymm_value_id) {
                          if($IsElasticSearchEnabled) {
                            $new_skus = $this->_mapvalueFactory->create()->load($ymm_value_id, 'ymm_value_id')->getProductId();
                          } else {
                             $new_skus = $this->_mapvalueFactory->create()->load($ymm_value_id, 'ymm_value_id')->getSku();

                          }
                       
                        $new = explode("|", $new_skus);
                        $product_skus = array_merge($product_skus, $new);
                    }
                }

                $skus = $product_skus;
				
                //set sort orders
                $enable_universal = $this->scopeConfig->getValue('finder/general/universal_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($enable_universal) {
                    $universal_collection = $this->universalProduct->getCollection()->addFieldToFilter('finder_id', $finderId);
                    $universal_products_skus = array_map([$this,"getProdcutSkusArr"], $universal_collection->getData());
                    if (!empty($universal_products_skus)) {
                        $skus = array_unique(array_merge($skus, $universal_products_skus));
                        $search_skus = array_diff($skus, $universal_products_skus);
                        if (!$this->urlInterfaceObj->getParam('product_list_order')) {
                        //Setting Sort order which sort based on the array elements order
                            $sort_order = $this->scopeConfig->getValue('finder/general/sort_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                            if ($sort_order == 2) {
                                $this->getSelect()->order("find_in_set(e.sku,'".implode(',', $universal_products_skus)."')");//display universal products last
                            } elseif ($sort_order == 3) {
                                $this->getSelect()->order("find_in_set(e.sku,'".implode(',', $search_skus)."')");//display search products last
                            }
                        }
                    }
                }
            }
            
            if($IsElasticSearchEnabled) {
                $skus=array_values($skus);
                $this->requestBuilder->bind('finder', $skus);
            } else {
                $skus=array_values($skus);
                $this->requestBuilder->bind('sku', $skus);
            }
        }
        return parent::_renderFiltersBefore();
    }
    
    public function getValues($parentId)
    {
        $collection = $this->_ymmvalueFactory->create()->getCollection()->addFieldToFilter('parent_id', $parentId);
        $data = $collection->getData();
        if (!$data) {
            $this->_parent_ids[] = $parentId;
        }
           
       /* if($this->getSort()=='asc') {
			$order = 'value ASC'; 
		}else{
            $order = 'value DESC'; 
		} */
        //$collection->getSelect()->order($order);
        
        foreach ($collection as $option) {
            $this->getValues($option->getYmmValueId());
        }
    }
    
    public function setOrder($attribute, $dir = Select::SQL_DESC)
    {
        $this->order = ['field' => $attribute, 'dir' => $dir];
        if ($attribute != 'relevance') {
            parent::setOrder($attribute, $dir);
        }
        return $this;
    }
}
