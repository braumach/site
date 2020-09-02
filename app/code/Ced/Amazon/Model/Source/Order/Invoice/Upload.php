<?php

namespace Ced\Amazon\Model\Source\Order\Invoice;

class Upload implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
['value' => 'magento-default-invoice-upload', 'label' => __('Magento Default Invoice Upload')],
['value' => 'custom-invoice-upload', 'label' => __('Custom Invoice Upload')]
];
    }
}
