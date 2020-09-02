<?php

namespace Ced\MPCatch\Ui\Component\Profile\Form\Categories;


use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    public $category;

    public function __construct
    (
        \Ced\MPCatch\Helper\Category $category
    )
    {
        $this->category = $category;
    }

    public function toOptionArray()
    {
        return $this->category->getCategoriesTree();
    }
}