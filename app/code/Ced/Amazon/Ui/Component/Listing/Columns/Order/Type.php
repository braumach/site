<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 29/2/20
 * Time: 12:30 PM
 */

namespace Ced\Amazon\Ui\Component\Listing\Columns\Order;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Type extends Column
{
    public $assetRepo;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->assetRepo = $assetRepo;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $isPrimeFieldName = 'is_prime';
            $isBusinessFieldname = 'is_business';
            $isPremiumFieldName = 'is_premium';
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$isPrimeFieldName])) {
                    if ($item[$isPrimeFieldName]) {
                        $image = $this->assetRepo->getUrl("Ced_Amazon::images/order/type/amazon_prime.png");
                        $html = "<img src='" . $image . "' style='max-width:100px; width:100px' />";
                        $item[$isPrimeFieldName . '_html'] = $html;
                    } else {
                        $html = "";
                    }
                    $item[$isPrimeFieldName . '_html'] = $html;
                }
                if (isset($item[$isBusinessFieldname])) {
                    if ($item[$isBusinessFieldname]) {
                        $image = $this->assetRepo->getUrl("Ced_Amazon::images/order/type/amazon_business.png");
                        $html = "<img src='" . $image . "' style='max-width:100px; width:100px'/>";

                    } else {
                        $html = "";
                    }
                    $item[$isBusinessFieldname . '_html'] = $html;
                }
                if (isset($item[$isPrimeFieldName])) {
                    if ($item[$isPremiumFieldName]) {
                        $image = $this->assetRepo->getUrl("Ced_Amazon::images/order/type/amazon_premium.png");
                        $html = "<img src='" . $image . "' style='max-width:100px; width:100px' />";

                    } else {
                        $html = "";
                    }
                    $item[$isPremiumFieldName . '_html'] = $html;
                }

            }
        }
        return $dataSource;
    }
}