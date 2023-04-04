<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Plugins;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Option;
use Osio\Subscriptions\Helper\Data as Helper;

class SetSubscribeProductOptions
{
    public function __construct(
        private readonly ProductCustomOptionInterface $option,
        private readonly Helper                       $helper
    )
    {
    }

    public function beforeSave(ProductInterface $product): bool
    {
        if (!$this->helper->isEnabled()) {
            return false;
        }

        if ($this->isOptionFlagSet($product) && !$this->hasThisOption($product->getOptions())) {
            $this->addOption($product);
        }

        if (!$this->isOptionFlagSet($product) && $this->hasThisOption($product->getOptions())) {
            $this->removeOption($product);
        }

        return false;
    }

    private function isOptionFlagSet(ProductInterface $product): bool
    {
        return (bool)$product->getData(e);
    }

    private function hasThisOption($options): bool
    {
        /** @var Option $option */
        foreach ($options as $option) {
            if ($option->getTitle() == $this->helper->getTitle()) {
                return true;
            }
        }

        return false;
    }

    private function removeOption($options): void
    {
        /** @var Option $option */
        foreach ($options as $option) {
            if ($option->getTitle() == $this->helper->getTitle()) {
                $option->delete();
                break;
            }
        }
    }

    private function addOption(ProductInterface $product): void
    {
        $this->option->addData(
            $this->getCustomOptions($product, $this->helper->getTitle(), $this->getValues())
        );
        $product->addOption($this->option)->setData('has_options', true);
    }

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
}
