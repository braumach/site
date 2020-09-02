<?php
namespace Magebees\Finder\Controller\Adminhtml\Import;

use Magento\Framework\App\Filesystem\DirectoryList;

class Import extends \Magento\Backend\App\Action
{
    protected $_coreRegistry = null;
    protected $resultPageFactory;
 
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        parent::__construct($context);
    }
 
    public function execute()
    {
		$result = array();
        $post = $this->getRequest()->getPost()->toArray();
		
		$dropdownsCol = $this->_objectManager->create('Magebees\Finder\Model\Dropdowns')->getCollection();
        $dropdownsCol->addFieldToFilter('finder_id', $post['finder_id']);
        $dropdown_ids = $dropdownsCol->load()->getColumnValues('dropdown_id');
		
		
		if(isset($post['import_file'])){
			$filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
			$reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
			$file = $reader->getAbsolutePath("import/ymm/".urldecode($post['import_file']));
			$handle = fopen($file,'r');
			
			if(isset($post['pointer_next']) && $post['pointer_next']!=1){
				$flag = false;
				fseek($handle,$post['pointer_next']);
			}else{
				$flag = true;
			}
  
           	$insertData = array();
			
			if ($flag) { //skip first row of headers
				$checkcsv = fgetcsv($handle);
				$dataincsv = count($checkcsv) - 1;
				if ($dataincsv != count($dropdown_ids)) {
					$this->messageManager->addError(__('CSV columns mismatch. Please check CSV file and upload again.'));
					$result['fail'] = 'CSV columns mismatch. Please check CSV file and import again.';
					$this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result));
					return;
				}
				$flag = false;
			}
			
			$count = 1;
			while (($data = fgetcsv($handle, ",")) !== false) {
				if($count > 500){
					break;
				}

				$fid= 1;
				$parent_id = 0;
				$value_data = [];
				try {
					foreach ($dropdown_ids as $dropdown_id) {
						$valueModel = $this->_objectManager->create('Magebees\Finder\Model\Ymmvalue');
						$exits = $valueModel->getCollection()
							->addFieldToFilter('value', trim($data[$fid]))
							->addFieldToFilter('parent_id', $parent_id)
							->addFieldToFilter('dropdown_id', $dropdown_id);
						$isExists = $exits->getData();
						if (empty($isExists)) {
							$value_data['dropdown_id'] = $dropdown_id;
							$value_data['parent_id'] = $parent_id;
							$value_data['value'] = trim($data[$fid]);
							$valueModel->setData($value_data)->save();
							$parent_id = $valueModel->getId();
						} else {
							$parent_id = $exits->getFirstItem()->getYmmValueId();
						}
						$fid++;
					}
				} catch (\Exception $e) {
					//$this->messageManager->addException($e, __('Something went wrong while saving the product.'));
					$this->messageManager->addError($e->getMessage());
					$result['fail'] = $e->getMessage();
					$this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result));
					return;
				}

				//Updated for solve import issue from v1.5.1
				$ymm_value_id = $parent_id;
				$mapModel = $this->_objectManager->create('Magebees\Finder\Model\Mapvalue')->load($ymm_value_id, 'ymm_value_id');
				$map_value = array();
				$map_value['ymm_value_id'] = $ymm_value_id;
				$map_value['sku'] = $data[0];
				$post_value = explode("|",$data[0]);
				$product_ids = array();
				$check = array_map(array($this,"getYmmValueIds"), $insertData);
				$flag_new = false;

				if(in_array($map_value['ymm_value_id'],$check)){
					$flag_new = true;
					$check_flip = array_flip($check);
					$new_key = $check_flip[$map_value['ymm_value_id']];
					$map_value['sku'] = $insertData[$new_key]['sku']."|".$map_value['sku'];
				}else{
					$map_value['sku'] = $mapModel->getSku()."|".$map_value['sku'];
					$map_value['sku']= implode("|", array_unique(explode("|", $map_value['sku'])));
				}
				$map_value['sku'] = array_unique(explode("|",$map_value['sku']));

				foreach($map_value['sku'] as $sku){
					$product_ids[] = $this->_objectManager->create('Magento\Catalog\Model\Product')->getIdBySku(trim($sku));
				}
				$map_value['sku'] = implode("|",$map_value['sku']);
				$map_value['sku'] = trim($map_value['sku'],"|");
				$map_value['product_id'] = implode("|",$product_ids);
				$map_value['product_id'] = trim($map_value['product_id'],"|");

				if($mapModel->getId()){
					if($flag_new){
						$insertData[$new_key] = array('map_value_id' => $mapModel->getId(),'ymm_value_id' => $map_value['ymm_value_id'], 'sku' => $map_value['sku'],'product_id' => $map_value['product_id']);
					}else{
						$insertData[] = array('map_value_id' => $mapModel->getId(),'ymm_value_id' => $map_value['ymm_value_id'], 'sku' => $map_value['sku'],'product_id' => $map_value['product_id']);
					}
				}else{
					if($flag_new){
						$insertData[$new_key] = array('map_value_id' => '','ymm_value_id' => $map_value['ymm_value_id'], 'sku' => $map_value['sku'],'product_id' => $map_value['product_id']);
					}else{
						$insertData[] = array('map_value_id' => '','ymm_value_id' => $map_value['ymm_value_id'], 'sku' => $map_value['sku'],'product_id' => $map_value['product_id']);
					}
				}

				
				//if (count($insertData) == 500) {
				if ($count == 500) {
					try {
						$this->connection->beginTransaction();
						$this->connection->insertOnDuplicate($this->resource->getTableName('magebees_finder_map_value'), $insertData);
						//insertOnDuplicate => update if duplicate key found, also can use insertMultiple
						$this->connection->commit();
						$result['count'] = $count;
						$result['pointer_last'] = ftell($handle);
						$next = fgets($handle);
						if($next){
							$result['no_more'] =false;
						}else{
							$this->messageManager->addSuccess(__('YMM product(s) has been imported.'));
							$result['no_more'] =true;
						}
						$insertData = array();

					} catch (\Exception $e) {
						$this->connection->rollBack();
						$result['fail'] = $e->getMessage();
						$this->messageManager->addError($e->getMessage());
					}
					$this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result));
					return;
				}
				$count++;

			}
			
			
			if (!empty($insertData)) {
				try {
					$this->connection->beginTransaction();
					$this->connection->insertOnDuplicate($this->resource->getTableName('magebees_finder_map_value'), $insertData);
					//insertOnDuplicate => update if duplicate key found, also can use insertMultiple
					$this->connection->commit();
					$this->messageManager->addSuccess(__('YMM product(s) has been imported.'));
					$result['count'] = $count-1;
					$result['pointer_last'] = ftell($handle);
					$result['no_more'] = true;
				} catch (\Exception $e) {
					$this->connection->rollBack();
					$this->messageManager->addError($e->getMessage());
					$result['fail'] = $e->getMessage();
				}
				$this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result));
			}
		}
		   
    }
   
	public function getYmmValueIds($insertData) { return $insertData["ymm_value_id"]; }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Finder::finder_content');
    }
}
