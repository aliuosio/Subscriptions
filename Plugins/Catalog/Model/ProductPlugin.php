<?php

namespace Osio\Subscriptions\Plugins\Catalog\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\Product\Option\Repository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Osio\Subscriptions\Setup\Patch\Data\IsProductSubscribable;

class ProductPlugin
{
    const ENABLED = 'system/subscribable/active';
    const PERIODS = 'system/subscribable/periods';

    private ScopeConfigInterface $scopeConfig;
    private OptionFactory $optionFactroy;
    private Repository $optionRepository;

    public function __construct(ScopeConfigInterface $scopeConfig, OptionFactory $optionFactory, Repository $optionRepository)
    {
        $this->scopeConfig = $scopeConfig;
        $this->optionFactroy = $optionFactory;
        $this->optionRepository = $optionRepository;
    }


    /**
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function beforeSave(Product $product): void
    {
        if ($product->getData(IsProductSubscribable::NAME) && $this->scopeConfig->getValue(ProductPlugin::ENABLED)) {
            $this->addCustomOption($product);
        }
    }

    private function getOption()
    {
        return $this->optionFactroy->create();
    }

    /**
     * @param Product $product
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addCustomOption(Product $product)
    {

        foreach ($this->getOptionsArray() as $optionValue) {
            $this->getOption()->setProductId($product->getId())
                ->setIsRequire(false)
                ->addData($optionValue);
            $this->optionRepository->save($this->getOption());

            $product->addOption($this->getOption());
        }
    }

    private function getOptionsArray(): array
    {
        return [
            [
                'title' => 'Select option',
                'type' => 'drop_down',
                'is_require' => 0,
                'sort_order' => 1,
                'values' => [
                    [
                        'title' => 'Option 100',
                        'price' => 10,
                        'price_type' => 'fixed',
                        'sku' => 'Option 1 sku',
                        'sort_order' => 1,
                    ],
                    [
                        'title' => 'Option 200',
                        'price' => 10,
                        'price_type' => 'fixed',
                        'sku' => 'Option 2 sku',
                        'sort_order' => 2,
                    ],
                    [
                        'title' => 'Option 300',
                        'price' => 10,
                        'price_type' => 'fixed',
                        'sku' => 'Option 3 sku',
                        'sort_order' => 3,
                    ],
                ],
            ]
        ];

    }
}
