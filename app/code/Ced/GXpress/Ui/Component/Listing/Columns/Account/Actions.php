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
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GXpress\Ui\Component\Listing\Columns\Account;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class Actions
 * @package Ced\GXpress\Ui\Component\Listing\Columns\Profile
 */
class Actions extends Column
{
    const URL_PATH_EDIT = 'gxpress/account/edit';
    const URL_PATH_FETCHTOKEN = 'gxpress/account/fetchtoken';
    const URL_PATH_FETCHOTHERDETAILS = 'gxpress/account/fetchotherdetails';
    const URL_PATH_IMPORTITEMIDS = 'gxpress/account/importItemIds';

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
    )
    {
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
                    $item[$name]['download'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_FETCHTOKEN, ['id' => $item['id']]),
                        'label' => __('Fetch Token'),
                        'class' => 'cedcommerce actions download'
                    ];
                }
            }
        }
        return $dataSource;
    }
}
