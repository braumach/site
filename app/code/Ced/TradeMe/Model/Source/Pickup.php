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
 * @package   Ced_TradeMe
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\TradeMe\Model\Source;


/**
 * Class ListingDuration
 * @package Ced\TradeMe\Model\Source
 */
class Pickup extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public $_jdecode;

    public function __construct(
        \Magento\Framework\Json\Helper\Data $_jdecode

    )
    {
        $this->_jdecode = $_jdecode;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getAllOptions() as $option) {
            $options[$option['value']] =(string)$option['label'];
        }
        return $options;
    }
    /**
     * @return array
     */

    public function jsonData() {
        $data = $this->getAllOptions();
        $val = $label = [];
        foreach ($data as $item) {
            $val['value'][] = $item['value'];
            $val['label'][] = $item['label'];
        }
        //$finalData = array_merge($val, $label);
        return ($this->_jdecode->jsonEncode($val));

    }

    public function getAllOptions()
    {
        $data = array(

           /* array(
                'value' => 0,
                'label' => __('None')
            ),*/
            array(
                'value' => 1,
                'label' => __('Allow')
            ),
            array(
                'value' => 2,
                'label' => __('Demand')
            ),
            array(
                'value' => 3,
                'label' => __('Forbid')
            )
        );
        return (json_encode($data));
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }

    /**
     * @return array
     */
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * @param int|string $optionId
     * @return mixed|null
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * @return array
     */
    public function getLabel($options = [])
    {
        foreach ($this->getAllOptions() as $option) {
            $options[] =(string)$option['label'];
        }
        return $options;
    }
}
