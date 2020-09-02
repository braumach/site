<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 2/3/20
 * Time: 5:45 PM
 */

namespace Ced\Amazon\Ui\Component\Listing\Columns\Order;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
class Country extends Column
{
    public $assetRepo;

    public $marketplace;

    const COUNTRY_FLAG = [
        "US" => "usa.jpg",
        "CA" => "canada.jpeg",
        "MX" => "mexico.png",
        "ES" => "spain.png",
        "UK" => "uk.png",
        "FR" => "france.png",
        "DE" => "germany.png",
        "IT" => "italy.png",
        "TR" => "turkey.png",
        "BR" => "brazil.png",
        "AE" => "uae.png",
        "IN" => "india.png",
        "CN" => "china.png",
        "JP" => "japan.png",
        "AU" => "australia.png",
        "SG" => "singapore.jpeg",
        "AT" => "austria.png",
        "NL" => "netherland.png"

    ];

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Amazon\Sdk\Marketplace $marketplace,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->assetRepo = $assetRepo;
        $this->marketplace = $marketplace;
    }

    public function prepareDataSource(array $dataSource)
    {
        $marketplace = $this->marketplace->getCollection();
        if (isset($dataSource['data']['items'])) {
            $fieldName = 'marketplace_id';
            foreach ($dataSource['data']['items'] as &$item) {
//                if (isset($item[$fieldName])) {
//                    if (isset(self::COUNTRY_FLAG[$marketplace[$item[$fieldName]]['code']])) {
//                        $image = $this->assetRepo->getUrl("Ced_Amazon::images/order/country/".self::COUNTRY_FLAG[$marketplace[$item[$fieldName]]['code']]);
//                        $html = "<img src='" . $image . "' width='20px' />&nbsp;". $marketplace[$item[$fieldName]]['code'];
//                        $item[$fieldName . '_html'] = $html;
//                    } else {
//                        $html = $marketplace[$item[$fieldName]]['code'];
//                        $item[$fieldName . '_html'] = $html;
//                    }
//                }
            }
        }
        return $dataSource;
    }
}
