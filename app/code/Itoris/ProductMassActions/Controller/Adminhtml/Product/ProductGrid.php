<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCT_MASS_ACTIONS
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\ProductMassActions\Controller\Adminhtml\Product;

class ProductGrid extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_view->loadLayout('productmassactions_product_grid');
        $html = $this->_view->getLayout()->getBlock('pmassactions')->getHtml();
        $html .= '<script type="text/javascript">
                jQuery(\'#mass_actions_product_grid input\').each(function(index, inp){
                    jQuery(inp).on(\'keypress\', function(ev){
                        if (ev.keyCode == 13) ev.preventDefault();
                    });
                });
            </script>';
        $this->getResponse()->setBody($html);
    }
}