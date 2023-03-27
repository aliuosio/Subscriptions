<?php
/**
 * Copyright © DEVHH, Inc. All rights reserved.
 * See LICENSE.TXT for license details.
 */

namespace Osio\Subscriptions\Plugins;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SetSubscribeProductOptions
{


    const TITLE = 'system/subscribable/title';
    const PERIODS = 'system/subscribable/periods';

    public function __construct(
        private readonly ProductCustomOptionInterface $option,
        private readonly ScopeConfigInterface $scopeConfig
    ) {}

    /**
     * @param ProductInterface $product
     * @return array{}
     */
    public function beforeSave(ProductInterface $product): array
    {
        $this->option->addData(
            $this->getCustomOptions($product, $this->getTitle(), $this->getValues())
        );
        $product->addOption($this->option)
            ->setData('has_options', true);

        return [];
    }

    /**
     * @return array
     */
    private function getValues(): array
    {
        return [
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
        ];
    }

    /**
     * @return mixed
     */
    private function getTitle(): mixed
    {
        return $this->scopeConfig->getValue(self::TITLE);
    }

    /**
     * @param ProductInterface $product
     * @param mixed $title
     * @param array $values
     * @return array
     */
    private function getCustomOptions(ProductInterface $product, mixed $title, array $values): array
    {
        return [
            'sort_order' => 1,
            'title' => $title,
            'price_type' => 'fixed',
            'price' => '',
            'type' => 'drop_down',
            'is_require' => false,
            'product_id' => $product->getId(),
            'sku' => $product->getSku(),
            'store_id' => $product->getData('store_id'),
            'values' => $values,
        ];
    }

    /**
     * @return array
     */
    public function getCustomOptionValues(): array
    {
        return explode(',', $this->scopeConfig->getValue(self::PERIODS));
    }
}
