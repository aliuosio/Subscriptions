<?php

namespace Osio\Subscriptions\Plugins\Catalog\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\OptionFactory;

class ProductPlugin
{
    private OptionFactory $optionFactory;

    public function __construct(OptionFactory $optionFactory)
    {
        $this->optionFactory = $optionFactory;
    }

    private function getOption()
    {
        return $this->optionFactory->create();
    }

    public function beforeSave(Product $product): void
    {
        $this->getOption()->addData($this->getCustomOptions($product));
        $product->addOption($this->getOption());
        $product->setData('has_options', true);
    }

    private function getCustomOptions(Product $product): array
    {
        return [
            "sort_order" => 1,
            "title" => "Custom Options",
            "price_type" => "fixed",
            "price" => "",
            "type" => "drop_down",
            "is_require" => false,
            "product_id" => $product->getData('id'),
            "sku" => $product->getData('sku'),
            "store_id" => $product->getData('store_id'),
            "values" => [
                ["title" => "Option 1", "price" => 10, "price_type" => "fixed", "sort_order" => 1],
                ["title" => "Option 2", "price" => 20, "price_type" => "fixed", "sort_order" => 2],
                ["title" => "Option 3", "price" => 30, "price_type" => "fixed", "sort_order" => 30]
            ]
        ];
    }

}
