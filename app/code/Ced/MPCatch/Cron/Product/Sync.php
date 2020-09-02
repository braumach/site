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

namespace Ced\MPCatch\Cron\Product;

class Sync
{
    public $logger;
    public $profileProduct;
    public $product;
    public $systemLogger;

    /**
     * @param \Ced\MPCatch\Helper\Logger $logger
     */
    public function __construct(
        \Ced\MPCatch\Model\ProfileProduct $profileProduct,
        \Ced\MPCatch\Helper\Logger $logger,
        \Ced\MPCatch\Helper\Product $product,
        \Psr\Log\LoggerInterface $systemLogger,
        \Ced\MPCatch\Helper\Config $config
    ) {
        $this->profileProduct = $profileProduct;
        $this->logger = $logger;
        $this->product = $product;
        $this->systemLogger = $systemLogger;
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function execute()
    {
        $fullOfferCron = $this->config->getFullOfferSyncCron();
        if($fullOfferCron == '1') {
            $productIds = $this->product->getProductIdsFromOfferAPI();
            $response = $this->product->updatePriceInventory($productIds);
            $this->logger->info('Full Offer Sync Cron Response',
                ['path' => __METHOD__, 'ProductIds' => json_encode($productIds), 'SyncReponse' => var_export($response)]);
            return $response;
        } else {
            $this->logger->info('Full Offer Sync Cron Disabled', ['path' => __METHOD__, 'Cron Status' => 'Disable']);
        }
        return false;
    }
}
