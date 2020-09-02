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
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Ui\Component\Listing\Columns\Product\Search;

use Magento\Ui\Component\Listing\Columns\Column;

class Image extends Column
{
    public $urlList = [
        "ATVPDKIKX0DER" => "https://amazon.com/dp/",
        "A2EUQ1WTGCTBG2" => "https://amazon.ca/dp/",
        "A1AM78C64UM0Y8" => "https://amazon.com.mx/dp/",
        "A1RKKUPIHCS9HS" => "https://amazon.es/dp/",
        "A1F83G8C2ARO7P" => "https://amazon.co.uk/dp/",
        "A13V1IB3VIYZZH" => "https://amazon.fr/dp/",
        "A1PA6795UKMFR9" => "https://amazon.de/dp/",
        "APJ6JRA9NG5V4" => "https://amazon.it/dp/",
        "A2Q3Y263D00KWC" => "https://amazon.com/dp/",
        "A21TJRUUN4KGV" => "https://amazon.in/dp/",
        "AAHKV2X7AFYLW" => "https://amazon.com.cn/dp/",
        "A1VC38T7YXB528" => "https://amazon.jp/dp/",
        "A39IBJ37TRP1C6" => "https://amazon.com.au/dp/",
        "A2VIGQ35RCS4UG" => "https://amazon.com.ae/dp/"
    ];

    /**
     * Prepare Data Source
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['image'])) {
                    $data = $item;
                    if (isset($data['marketplace_id'])) {
                        $amazonProductUrl = $this->urlList[$data['marketplace_id']] . $data['asin'];
                    } else {
                        $amazonProductUrl = "www.amazon.com";
                    }

                    $item[$fieldName . '_src'] = $data['image'];
                    $item[$fieldName . '_alt'] = isset($data['title']) ?: "";
                    $item[$fieldName . '_link'] = $amazonProductUrl;
                    $item[$fieldName . '_orig_src'] = $data['image'];
                }
            }
        }

        return $dataSource;
    }
}
