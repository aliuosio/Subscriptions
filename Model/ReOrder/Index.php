<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ReOrder;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Osio\Subscriptions\Helper\Data as Helper;
use Osio\Subscriptions\Model\ResourceModel\Subscribe\CollectionFactory;
use Osio\Subscriptions\Model\ResourceModel\Subscribe\Collection;
use Magento\Customer\Model\ResourceModel\Customer\Collection as customerCollection;

class Index
{
    private array $customersData;

    public function __construct(
        private readonly ProductRepositoryInterfaceFactory $productRepositoryFactory,
        private readonly Helper                            $helper,
        private readonly Factories                         $reOrderfactories,
        private readonly CollectionFactory                 $collectionFactory,
        private readonly Customers                         $customers
    )
    {
    }

    /**
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws LocalizedException
     * @throws Exception
     */
    public function execute(): array
    {
        $result = [];
        $this->getCustomerData();

        foreach ($this->getCollection()->getGroupedByCustomer() as $customerId => $itemIds) {
            $result = array_merge($result, $this->setCustomerOrder($customerId, $itemIds));
        }

        if (!empty($result)) {
            $this->getCollection()->updateSubscriptionsAfterReOrder($result);
        }

        return $result;
    }

    private function getCustomerData(): void
    {
        foreach ($this->customers->fetchCustomers($this->getCustomerIds())->getItems() as $customer) {
            $this->customersData[$customer->getEntityId()] = $customer;
        }
    }

    private function getCustomerIds(): array
    {
        return array_keys($this->getCollection()->getGroupedByCustomer());
    }

    private function getCollection(): Collection
    {
        return $this->collectionFactory->create();
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
    private function setOptions(OrderItemInterface $orderItem, CartItemInterface $quoteItem): CartItemInterface
    {
        $options = $orderItem->getProductOptions();
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

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws InputException
     * @throws Exception
     */
    private function setCustomerOrder(int $customerId, array $itemIds): array
    {
        $result = [];

        foreach ($itemIds as $itemId) {
            $orderItem = $this->reOrderfactories->getOrderItem()->get($itemId);
            $quoteItem = $this->reOrderfactories->getQuoteItem()
                ->setProduct($this->getProductForItem($orderItem))
                ->setQty($orderItem->getQtyOrdered())
                ->setPrice($orderItem->getPrice());
            $quoteItem = $this->setOptions($orderItem, $quoteItem);
            $quote = $this->reOrderfactories->getQuote($customerId)->addItem($quoteItem);
        }

        if (isset($quote) && isset($this->customersData[$customerId])) {
            //$quote->setCustomer($this->customersData[$customerId]);
            $this->reOrderfactories->getQuoteRepository()->save($quote);
            $this->reOrderfactories->getOrderRepository()->save(
                $this->reOrderfactories->getQuoteManagement()->submit($quote)
            );
        }

        return array_merge($result, $itemIds);
    }

}
