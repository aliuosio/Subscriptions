<?php

namespace Osio\Subscriptions\Plugins\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductRepositoryPlugin
{
    /**
     * @throws NoSuchEntityException
     */
    public function beforeSave(
        ProductRepositoryInterface $productRepository,
        ProductInterface           $product
    ): array {
        $productRepository->get('subscribable');
        return [$product];
    }
}
