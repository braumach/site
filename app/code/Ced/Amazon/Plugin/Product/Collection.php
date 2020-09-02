<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 10/10/19
 * Time: 1:55 PM
 */

namespace Ced\Amazon\Plugin\Product;

class Collection
{
    public function afterGetSelectCountSql(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $subject,
        $result
    ) {
        $selectSql = $result->__toString();
        $subjectSql = $subject->getSelect()->__toString();
        if (strpos($selectSql, 'ced_amazon')!== false || strpos($subjectSql, 'ced_amazon')!== false) {
            $result->reset(\Zend_Db_Select::GROUP);
        }
        return $result;
    }
}