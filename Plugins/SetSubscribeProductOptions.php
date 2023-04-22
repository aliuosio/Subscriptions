<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Plugins;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Osio\Subscriptions\Helper\Data as Helper;

class SetSubscribeProductOptions
{
    public function __construct(
        private readonly productCustomOptionInterfaceFactory $optionInterfaceFactory,
        private readonly Helper                              $helper
    )
    {
    }

    public function beforeSave(ProductInterface $product): bool
    {
        if (!$this->helper->isEnabled()) {
            return false;
        }

        if ($this->isOptionFlagSet($product) && !$this->hasThisOption($product)) {
            $this->addOption($product);
        }

        if (!$this->isOptionFlagSet($product) && $this->hasThisOption($product)) {
            $this->resetOptions($product);
        }

        return false;
    }

    private function isOptionFlagSet(ProductInterface $product): bool
    {
        return (bool)$product->getData($this->helper->getCode());
    }

    private function hasThisOption(ProductInterface $product): bool
    {
        $titles = array_map(function ($option) {
            return $option->getTitle();
        }, $product->getOptions());

        return in_array($this->helper->getTitle(), $titles, true);
    }

    private function resetOptions(ProductInterface $product): void
    {
        $optionsReset = array_filter($product->getOptions(), function ($option) {
            return $option->getTitle() !== $this->helper->getTitle();
        });

        $product->setOptions(array_values($optionsReset));
    }

    private function getOption(): ProductCustomOptionInterface
    {
        return $this->optionInterfaceFactory->create();
    }

    private function addOption(ProductInterface $product): void
    {
        $this->getOption()->setData(
            $this->getCustomOptions(
                $product,
                $this->helper->getTitle(),
                $this->getValues($this->helper->getPeriod())
            )
        );
        $product->addOption($this->getOption())->setData('has_options', true);
    }

    private function getValues(array $titles): array
    {
        $options = [];

        foreach ($titles as $index => $title) {
            $options[] = [
                'title' => $title,
                'price' => 0,
                'price_type' => 'fixed',
                'sort_order' => $index + 1,
            ];
        }

        return $options;
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
