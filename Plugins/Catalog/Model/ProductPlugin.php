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
            $test = 1;
            $product->setData('has_options', true);
            $this->option->setProductId($product->getId())
                ->setData('store_id', $product->getStoreId())
                ->addData($this->myCustomOptions());
            $product->addOption($this->option);
        }
    }

    private function myCustomOptions(): array
    {
        $values = [
            [
                'title' => 'Red',
                'price' => 10,
                'price_type' => "fixed",
                'sort_order' => 1,
                'is_delete' => 0,
                'option_type_id' => -1,
            ],
            [
                'title' => 'White',
                'price' => 10,
                'price_type' => "fixed",
                'sort_order' => 1,
                'is_delete' => 0,
                'option_type_id' => -1,
            ],
            [
                'title' => 'Black',
                'price' => 10,
                'price_type' => "fixed",
                'sort_order' => 1,
                'is_delete' => 0,
                'option_type_id' => -1,
            ]
        ];

        return [
            [
                "sort_order" => 2,
                "title" => "period",
                "price_type" => "fixed",
                "price" => "",
                "type" => "drop_down",
                "is_require" => 0,
                "values" => $values
            ]
        ];
    }
}
