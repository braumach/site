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

namespace Ced\Amazon\Ui\Component\Listing\Columns\Product;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Errors
 * @package Ced\Amazon\Ui\Component\Listing\Columns\Product
 */
class Errors extends Column
{
    /**
     * @var SerializerInterface
     */
    public $serializer;

    /** @var \Ced\Amazon\Model\Source\Profile  */
    public $profile;

    /**
     * Profile constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SerializerInterface $serializer
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SerializerInterface $serializer,
        $components = [],
        $data = []
    ) {
        $this->serializer = $serializer;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['validation_errors'])) {
                    $html = [];
                    $errors = json_decode($item['validation_errors'],true);
                    foreach ($errors as $error) {
                        if(isset($error['errors']) && !empty($error['errors']) && is_array($error['errors'])){
                            foreach($error['errors'] as $errKey => $err){
                                $url = "<ul>";
                                $url .= $this->returnArray($err);
                                $url .= "</ul>";
                                $html[] = $url;
                            }
                        }
                    }
                    $item["validation_errors_html"] = $html;
                }
            }
        }
        return $dataSource;
    }

    public function returnArray($err, $html = '')
    {
        if (is_array($err)) {
            foreach ($err as $errKey => $errValue) {
                if (is_array($errValue)) {
                    $html .= '<li><b>' . strtoupper($errKey) . '</b></li>';
                    $html .= $this->returnArray($errValue);
                } else {
                    $html .= '<li><b>'.$errKey .'</b> : '. $errValue.'</li>';
                }
            }

            return $html;
        }
    }
}
