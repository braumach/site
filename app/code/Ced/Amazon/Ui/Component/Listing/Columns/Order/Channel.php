<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 2/3/20
 * Time: 3:06 PM
 */

namespace Ced\Amazon\Ui\Component\Listing\Columns\Order;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Channel extends Column
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
            $fieldName = 'fulfillment_channel';
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$fieldName])) {
                    if ($item[$fieldName] == \Ced\Amazon\Model\Source\Order\Channel::TYPE_AFN) {
                        $image = $this->assetRepo->getUrl("Ced_Amazon::images/order/type/amazon_fba.png");
                        $html = "<img src='" . $image . "' style='max-width:100px; width:100px'/>";
                        $item[$fieldName . '_html'] = $html;
                    } elseif ($item[$fieldName] == \Ced\Amazon\Model\Source\Order\Channel::TYPE_MFN) {
                        $image = $this->assetRepo->getUrl("Ced_Amazon::images/order/type/amazon_fbm.png");
                        $html = "<img src='" . $image . "' style='max-width:100px; width:100px'/>";
                        $item[$fieldName . '_html'] = $html;
                    }
                }
            }
//
        }
        return $dataSource;
    }
}