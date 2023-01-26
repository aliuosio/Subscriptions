<?php

namespace Osio\Subscriptions\Observers\Backend\Catalog;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;

class ProductSaveBefore
{
    private Product $product;

    public function execute(Observer $observer): void
    {
        $this->product = $observer->getEvent()->getProduct();
        $options = $this->product->getOptions();
    }
}
