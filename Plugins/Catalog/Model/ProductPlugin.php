<?php

namespace Osio\Subscriptions\Plugins\Catalog\Model;

use Magento\Catalog\Model\Product;

class ProductPlugin
{

    public function beforeSave(Product $product): Product
    {
        if ($product->getData('subscribable') == 1) {
            $product->setData('has_options', true);
        }

        return $product;
    }
}
