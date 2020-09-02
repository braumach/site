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
 * @package     Ced_GXpress
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GXpress\Ui\Component\Listing\Columns\Order;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class Actions
 * @package Ced\GXpress\Ui\Component\Listing\Columns\Order
 */
class Actions extends Column
{
    const URL_PATH_EDIT = 'sales/order/view';
    const URL_PATH_VIEW = 'gxpress/order/view';
    const URL_PATH_DELETE = 'gxpress/order/delete';
    const URL_PATH_SYNC = 'gxpress/order/sync';

    /** @var UrlInterface */
    public $urlBuilder;

    /**
     * Actions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    )
    {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $response = isset($item['order_data']) ? $item['order_data'] :  json_encode(array('Response' => 'Order Data Not Found'));
                if (isset($item['id'])) {
                    $item[$name]['view'] = [
                        'label' => __('View Order'),
                        'class' => 'cedcommerce actions view',
                        'popup' => [
                            'title' => __("GXpress Order #{$item['magento_order_id']}"),
                            'message' => $response,
                            'type' => 'json',
                            'render' => 'html',
                        ],
                    ];
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::URL_PATH_EDIT,
                            [
                                'order_id' => $item['magento_id']
                            ]
                        ),
                        'label' => __('Edit'),
                        'class' => 'cedcommerce actions edit'
                    ];
                    $item[$name]['sync'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::URL_PATH_SYNC,
                            [
                                'account_id' => $item['account_id'],
                                'order_id' => $item['gxpress_order_id'],
                                'id' => $item['id']
                            ]
                        ),
                        'label' => __('Sync'),
                        'class' => 'cedcommerce actions sync'
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_DELETE, ['id' => $item['id']]),
                        'label' => __('Delete'),
                        'class' => 'cedcommerce actions delete'
                    ];
                }
            }
        }
        return $dataSource;
    }
}
