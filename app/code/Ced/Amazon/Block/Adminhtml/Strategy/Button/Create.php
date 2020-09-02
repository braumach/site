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
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Block\Adminhtml\Strategy\Button;

use Magento\Backend\Block\Widget\Context;

class Create extends \Magento\Backend\Block\Widget\Container implements
    \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    public $strategy;

    public function __construct(
        Context $context,
        \Ced\Amazon\Model\Source\Strategy\Type $strategy,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->strategy = $strategy;
    }

    public function getButtonData()
    {
        $button = [
            'id' => 'amazon_strategy_create',
            'label' => __('Add Strategy'),
            'class' => 'add',
            'class_name' => \Magento\Backend\Block\Widget\Button\SplitButton::class,
            'options' => $this->buttonOptions(),
        ];
        return $button;
    }

    public function buttonOptions()
    {
        $options = [];

        $default = true;
        foreach ($this->strategy->getAllOptions() as $strategy) {
            $options[$strategy['value']] = [
                'label' => $strategy['label'],
                'onclick' => "setLocation('" . $this->getUrl("amazon/strategy/edit/type/{$strategy['value']}") . "')",
                'default' => $default,
            ];
            $default = false;
        }

        return $options;
    }
}
