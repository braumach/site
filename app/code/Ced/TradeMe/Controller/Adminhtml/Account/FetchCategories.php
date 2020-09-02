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
 * @package   Ced_TradeMe
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\TradeMe\Controller\Adminhtml\Account;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Class FetchCategories
 * @package Ced\TradeMe\Controller\Adminhtml\Account
 */
class FetchCategories extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_TradeMe::TradeMe';
    /**
     * @var \Ced\TradeMe\Helper\Data
     */
    public $dataHelper;
    /**
     * @var \Ced\TradeMe\Helper\Logger
     */
    public $logger;
    /**
     * @var \Ced\TradeMe\Helper\MultiAccount
     */
    public $multiAccountHelper;

    /**
     * @var \Ced\TradeMe\Model\Config\Location
     */
    public $location;
    /**
     * @var Filesystem
     */
    public $filesystem;
    /**
     * @var Filesystem\Io\File
     */
    public $file;
    public $category;


    /**
     * Fetchotherdetails constructor.
     * @param Action\Context $context
     * @param \Ced\TradeMe\Helper\Data $dataHelper
     * @param \Ced\TradeMe\Helper\Logger $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\TradeMe\Helper\Data $dataHelper,
        \Ced\TradeMe\Helper\Logger $logger,
        \Ced\TradeMe\Model\CategoryFactory $category,
        Filesystem $filesystem,
        Filesystem\Io\File $file,
        \Ced\TradeMe\Helper\MultiAccount $multiAccountHelper
    )
    {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->category = $category;
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->multiAccountHelper = $multiAccountHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {

        try {
            $success = $error = [];
            $responseData = [];
            $data = $this->getRequest()->getParams();
            $accountId = '';
            $accName = '';
            if (isset($data['id'])) {
                $account = $this->multiAccountHelper->getAccountRegistry($data['id']);
                if(isset($account) && $account) {
                    $accountId = $account->getId();
                    $accName = $account->getAccountCode();
                }
            }

            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $folderPath = $mediaDirectory->getAbsolutePath('ced/trademe/');

            if (!file_exists($folderPath . $accName )){
                $this->file->mkdir($folderPath . $accName, 0777, true);
            }

            $path = $folderPath . $accName . '/categories.json';
                $response = $this->dataHelper->fetchAllCategories();
                $file   = fopen($path, "w");
                fwrite($file, json_encode($response));
                fclose($file);

            if (isset($response['Name']) == "Root") {

                // Save into the Trademe-Category Table
                if (isset($response['Subcategories'])) {
                    foreach ($response['Subcategories'] as $catLevel0) {
                        $this->saveCategory($catLevel0, 0);
                        if (isset($catLevel0['Subcategories'])) {
                            foreach ($catLevel0['Subcategories'] as $catLevel1) {
                                $this->saveCategory($catLevel1, 1);
                                if (isset($catLevel1['Subcategories'])) {
                                    foreach ($catLevel1['Subcategories'] as $catLevel2) {
                                        $this->saveCategory($catLevel2, 2);
                                        if (isset($catLevel2['Subcategories'])) {
                                            foreach ($catLevel2['Subcategories'] as $catLevel3) {
                                                $this->saveCategory($catLevel3, 3);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $responseData['valid'] = 1;
                $responseData['message'] = "categories successfully imported";
            } else {
                $responseData['valid'] = 0;
                $responseData['message'] = "categories not found";
            }

        } catch (\Exception $e) {
            $error[] = $e->getMessage();
            $this->logger->addError('In Fetch Categories Call: '.$e->getMessage(), ['path' => __METHOD__]);
        }
        if (($responseData['valid'] == 0 )) {
            $this->messageManager->addErrorMessage($responseData['message']);
        }
        if ($responseData['valid'] == 1 ) {
            $this->messageManager->addSuccessMessage($responseData['message']);
        }
        $this->_redirect('trademe/account/index');
    }
    public function saveCategory($catArray, $level)
    {
        $leaf = $catArray['IsLeaf'] ? 1 : 0;
        $categoryObj = $this->category->create()->loadByField('trademe_id', $catArray['Number']);
        $categoryObj->setCode(implode('>>', explode('/', $catArray['Path'])));
        $categoryObj->setTrademeId($catArray['Number']);
        $parentCode = explode('/', $catArray['Path']);
        array_pop($parentCode);
        $categoryObj->setParentCode(implode('>>', $parentCode));
        $categoryObj->setLabel($catArray['Name']);
        $categoryObj->setLevel($level);
        $categoryObj->setIsLeaf($leaf);
        $categoryObj->save();
        return true;
    }
}