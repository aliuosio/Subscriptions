<?php

namespace Osio\Subscriptions\Plugins\Model;

use Magento\Catalog\Api\Data\ProductInterface;

class Product
{

    public function beforeGetOptions(array $subject): array
    {
        return $subject;
    }
}
