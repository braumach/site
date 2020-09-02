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
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Observer\Quote;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class TotalsCollector implements ObserverInterface
{
     public function execute(Observer $observer)
     {
         $quote = $observer->getQuote();

//         $quote->setForcedCurrency(null);
//         $quote->setCurrency(null);

         if ($quote->getAmazonOrderId() && $observer->getTotal()->getAppliedTaxes()) {
             $observer->getTotal()->setAppliedTaxes(null);
             $observer->getTotal()->setItemsAppliedTaxes(null);
//             $appliedTaxes = json_decode('{"Ced_Amazon":{"amount":5,"base_amount":0,"percent":20,"id":"Ced_Amazon","rates":[{"percent":20,"code":"Ced_Amazon","title":"Ced_Amazon"}],"item_id":null,"item_type":"shipping","associated_item_id":null,"process":0}}', true);
//             $observer->getTotal()->setAppliedTaxes($appliedTaxes);
         }
     }
}