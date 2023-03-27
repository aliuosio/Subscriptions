<?php
/**
 * Copyright © dev-hh, Inc. All rights reserved.
 * See LICENSE.TXT for license details.
 */

namespace Osio\Subscriptions\Plugins;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;

class SetSubscribeProductOptions
{


    private ProductCustomOptionInterface $option;

    public function __construct(ProductCustomOptionInterface $option)
    {
        $this->option = $option;
    }

    public function beforeSave(ProductInterface $product): array
    {
        $this->option->addData($this->getCustomOptions($product));
        $product->addOption($this->option);
        $product->setData('has_options', true);

        return [];
    }

    private function getCustomOptions(ProductInterface $product): array
    {
        return [
            'sort_order' => 1,
            'title' => 'Custom Options',
            'price_type' => 'fixed',
            'price' => '',
            'type' => 'drop_down',
            'is_require' => false,
            'product_id' => $product->getId(),
            'sku' => $product->getSku(),
            'store_id' => $product->getData('store_id'),
            'values' => [
                [
                    'title' => 'Option 1',
                    'price' => 10,
                    'price_type' => 'fixed',
                    'sort_order' => 1,
                ],
                [
                    'title' => 'Option 2',
                    'price' => 20,
                    'price_type' => 'fixed',
                    'sort_order' => 2,
                ],
                [
                    'title' => 'Option 3',
                    'price' => 30,
                    'price_type' => 'fixed',
                    'sort_order' => 30,
                ],
            ],
        ];
    }
}
