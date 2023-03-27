<?php
/**
 * Copyright © DEVHH, Inc. All rights reserved.
 * See LICENSE.TXT for license details.
 */

namespace Osio\Subscriptions\Plugins;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class SetSubscribeProductOptions
{
    private ProductCustomOptionInterface $option;

    /**
     * @param ProductCustomOptionInterface $option
     */
    public function __construct(ProductCustomOptionInterface $option)
    {
        $this->option = $option;
    }

    /**
     * @param ProductInterface $product
     * @return array{}
     */
    public function beforeSave(ProductInterface $product): array
    {
        $this->option->addData($this->getCustomOptions($product));
        $product->addOption($this->option);
        $product->setData('has_options', true);

        return [];
    }

    /**
     * @param ProductInterface $product
     * @return array{sort_order: integer}
     */
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
