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
 * @package     Ced_TradeMe
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\TradeMe\Ui\Component\Listing\Columns\Account;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class Actions
 * @package Ced\TradeMe\Ui\Component\Listing\Columns\Profile
 */
class Actions extends Column
{
    const URL_PATH_EDIT = 'trademe/account/edit';
    const URL_PATH_FETCHTOKEN = 'trademe/account/fetchtoken';
    const URL_PATH_FETCHOTHERDETAILS = 'trademe/account/fetchcategories';
    const URL_PATH_IMPORTITEMIDS = 'trademe/account/importItemIds';

    /**
     * @var UrlInterface
     */
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
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['account_code'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_EDIT, ['id' => $item['id']]),
                        'label' => __('Edit'),
                        'class' => 'cedcommerce actions edit'
                    ];
                    if (isset($item['outh_token_secret']) && $item['outh_token_secret'] != null) {
                        $item[$name]['sync'] = [
                            'href' => $this->urlBuilder->getUrl(self::URL_PATH_FETCHOTHERDETAILS, ['id' => $item['id']]),
                            'label' => __('Fetch Categories'),
                            'class' => 'cedcommerce actions sync'
                        ];

                    } else {
                        $item[$name]['download'] = [
                            'href' => $this->urlBuilder->getUrl(self::URL_PATH_FETCHTOKEN, ['id' => $item['id']]),
                            'label' => __('Fetch Token'),
                            'class' => 'cedcommerce actions download'
                        ];
                    }
                }
            }
        }
        return $dataSource;
    }
}
