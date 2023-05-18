<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ReOrder;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterfaceFactory;
use Osio\Subscriptions\Helper\Data as Helper;

class Items
{

    public function __construct(
        private readonly CartInterfaceFactory                $quoteFactory,
        private readonly ProductRepositoryInterfaceFactory   $productRepositoryFactory,
        private readonly OrderItemRepositoryInterfaceFactory $orderItemRepositoryFactory,
        private readonly CartItemInterfaceFactory            $quoteItemFactory,
        private readonly Helper                              $helper
    )
    {
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function set(array $itemIds, int $customerId): ?Quote
    {
        foreach ($itemIds as $itemId) {
            $orderItem = $this->orderItemRepositoryFactory->create()->get($itemId);
            $quoteItem = $this->quoteItemFactory->create()
                ->setProduct($this->getProductForItem($orderItem))
                ->setQty($orderItem->getQtyOrdered())
                ->setPrice($orderItem->getPrice());
            $quote = $this->quoteFactory->create()
                ->setCustomerId($customerId)
                ->addItem(
                    $this->setOptionsAndAttributes($orderItem, $quoteItem)
                );
        }

        return (isset($quote)) ? $quote : null;
    }

    /**
     * @throws LocalizedException
     */
    private function setOptionsAndAttributes(
        OrderItemInterface $orderItem,
        CartItemInterface  $quoteItem
    ): CartItemInterface
    {
        $quoteItem = $this->setOptions($quoteItem, $orderItem->getProductOptions());

        return $this->setAttributes($quoteItem, $orderItem->getProductOptions());
    }

    /**
     * @throws NoSuchEntityException
     */
    private function getProductForItem($orderItem): ProductInterface
    {
        return $this->productRepositoryFactory->create()->getById($orderItem->getProductId());
    }

    /**
     * @throws LocalizedException
     */
    private function setOptions(CartItemInterface $quoteItem, array $options): CartItemInterface
    {
        if (isset($options['options'])) {
            foreach ($options['options'] as $option) {
                if ($this->helper->getTitle() == $option['label']) {
                    continue;
                }
                $quoteItem->addOption([
                    'label' => $option['label'],
                    'value' => $option['value']
                ]);
            }
        }

        return $quoteItem;
    }

    /**
     * @throws LocalizedException
     */
    private function setAttributes(CartItemInterface $quoteItem, array $options): CartItemInterface
    {
        if (isset($options['attributes_info'])) {
            foreach ($options['attributes_info'] as $attribute) {
                $quoteItem->addOption([
                    'label' => $attribute['label'],
                    'value' => $attribute['value']
                ]);
            }
        }

        return $quoteItem;
    }
}
