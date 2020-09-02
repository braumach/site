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
 * @package   Ced_MPCatch
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\MPCatch\Controller\Adminhtml\Feeds;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Truncate
 *
 * @package Ced\MPCatch\Controller\Adminhtml\Feeds
 */
class Truncate extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var \Ced\MPCatch\Model\Feeds
     */
    public $feeds;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    public $fileIo;

    /**
     * Delete constructor.
     *
     * @param Action\Context                        $context
     * @param PageFactory                           $resultPageFactory
     * @param \Magento\Framework\Filesystem\Io\File $fileIo
     * @param \Ced\MPCatch\Model\Feeds               $mpcatchFeeds
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Filesystem\Io\File $fileIo,
        \Ced\MPCatch\Model\Feeds $mpcatchFeeds
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->fileIo = $fileIo;
        $this->feeds = $mpcatchFeeds;
    }

    public function execute()
    {

        $collection = $this->feeds->getCollection();

        // Remove files
        if (isset($collection) and $collection->getSize() > 0) {
            foreach ($collection as $feed) {
                $feedFile = $feed->getFeedFile();
                if ($this->fileIo->fileExists($feedFile)) {
                    $this->fileIo->rm($feedFile);
                }

                $responseFile = $feed->getResponseFile();
                if ($this->fileIo->fileExists($responseFile)) {
                    $this->fileIo->rm($responseFile);
                }
            }
        }

        // Delete feeds from db
        $collection->walk('delete');
        $this->messageManager->addSuccessMessage('Feed deleted successfully.');
        $this->_redirect('mpcatch/feeds');
    }
}
