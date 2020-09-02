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
 * @category Ced
 * @package Ced_Amazon
 * @author CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright © 2019 CedCommerce. All rights reserved.
 * @license EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Ui\DataProvider\Product\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class Status implements ModifierInterface
{
    public $request;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    public function modifyData(array $data)
    {

    }

    public function modifyMeta(array $meta)
    {

    }
}
