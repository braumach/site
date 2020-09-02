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

namespace Ced\Amazon\Model\Source\Config\Strategy;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Ced\Amazon\Model\Source\Strategy\Type as StrategyType;

/**
 * Class Type
 * @package Ced\Amazon\Model\Source
 */
class Type extends AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        return StrategyType::getTypeList();
    }
}
