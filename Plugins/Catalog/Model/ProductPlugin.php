<?php

namespace Osio\Subscriptions\Plugins\Catalog\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ProductPlugin
{
    const ENABLED = 'system/subscribable/active';
    const PERIODS = 'system/subscribable/periods';

    private ScopeConfigInterface $scopeConfig;
    private Option $option;

    public function __construct(ScopeConfigInterface $scopeConfig, Option $option)
    {
        $this->scopeConfig = $scopeConfig;
        $this->option = $option;
    }


    public function beforeSave(Product $product): void
    {
        if ($product->getData('subscribable')) {
            $this->addCustomOption($product);
        }
    }

    public function addCustomOption(Product $product)
    {
        $optionsArray = [
            [
                'title' => 'Select option',
                'type' => 'drop_down',
                'is_require' => 1,
                'sort_order' => 1,
                'values' => [
                    [
                        'title' => 'Option 1',
                        'price' => 10,
                        'price_type' => 'fixed',
                        'sku' => 'Option 1 sku',
                        'sort_order' => 1,
                    ],
                    [
                        'title' => 'Option 2',
                        'price' => 10,
                        'price_type' => 'fixed',
                        'sku' => 'Option 2 sku',
                        'sort_order' => 2,
                    ],
                    [
                        'title' => 'Option 3',
                        'price' => 10,
                        'price_type' => 'fixed',
                        'sku' => 'Option 3 sku',
                        'sort_order' => 3,
                    ],
                ],
            ]
        ];

        foreach ($optionsArray as $optionValue) {
            $option = $this->option
                ->setProductId($product->getId())
                ->setIsRequire(false)
                ->addData($optionValue);
            $option->save();
            $product->addOption($option);
        }
    }
}
