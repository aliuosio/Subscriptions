<?php
declare(strict_types=1);

namespace Osio\Subscriptions\Plugins\Catalog\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductSave
{
    public function beforeSave(
        ProductRepositoryInterface $subject,
        ProductInterface $product,
        $saveOptions = false
    ): array {
        return [$product, $saveOptions];
    }
}
