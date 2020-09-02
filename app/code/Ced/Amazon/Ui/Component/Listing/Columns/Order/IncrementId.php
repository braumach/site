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
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Ui\Component\Listing\Columns\Order;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class IncrementId
 * @package Ced\Amazon\Ui\Component\Listing\Columns\Order
 */
class IncrementId extends Column
{
    /** Url path */
    const URL_PATH_EDIT = 'sales/order/view';

    public $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        $components = [],
        $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = \Ced\Amazon\Model\Order::COLUMN_MAGENTO_INCREMENT_ID;
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$fieldName], $item[\Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID]) &&
                    !empty($item[\Ced\Amazon\Model\Order::COLUMN_MAGENTO_ORDER_ID])) {
                    $url = $this->urlBuilder->getUrl(
                        self::URL_PATH_EDIT,
                        [
                            'order_id' => $item['magento_order_id']
                        ]
                    );
                    $html = "<a href='" . $url. "' target='_blank'>";
                    $html .= $item[$fieldName];
                    $html .= "</a>";
                    $item[$fieldName . '_html'] = $html;
                } else {
                    $item[$fieldName. '_html'] = "NA";
                }
            }
        }
        return $dataSource;
    }
}
