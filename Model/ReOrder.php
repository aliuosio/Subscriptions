<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartManagementInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterfaceFactory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Osio\Subscriptions\Helper\Data as Helper;
use Osio\Subscriptions\Model\ResourceModel\Subscribe\Collection as subscriptionCollection;
use Osio\Subscriptions\Model\ResourceModel\Subscribe\CollectionFactory as subscriptionCollectionFactory;
use Zend_Db_Expr;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;

class ReOrder
{

    public function __construct(
        private readonly subscriptionCollectionFactory       $subscriptionCollectionFactory,
        private readonly CartInterfaceFactory                $quoteFactory,
        private readonly OrderItemRepositoryInterfaceFactory $orderItemRepositoryFactory,
        private readonly CartItemInterfaceFactory            $quoteItemFactory,
        private readonly CartManagementInterfaceFactory      $quoteManagementFactory,
        private readonly ProductRepositoryInterfaceFactory   $productRepositoryFactory,
        private readonly CartRepositoryInterfaceFactory      $quoteRepositoryFactory,
        private readonly OrderRepositoryInterfaceFactory     $orderRepositoryFactory,
        private readonly Helper                              $helper,
        private readonly ResourceConnection                  $resource,
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
        foreach ($this->getGroupedByCustomer() as $customerId => $itemIds) {
            $result = array_merge($result, $this->setCustomerOrder($customerId, $itemIds));
        }

        if (!empty($result)) {
            $this->updateSubscriptionsAfterReOrder($result);
        }

        return $result;
    }

    private function getSubscriptionCollection(): subscriptionCollection
    {
        return $this->subscriptionCollectionFactory->create();
    }

    /**
     * @throws Exception
     */
    private function updateSubscriptionsAfterReOrder(array $result): void
    {
        $collection = $this->getSubscriptionCollection()->addFieldToSelect(['item_id', 'period'])
            ->addFieldToFilter('item_id', ['in' => $result]);
        $connection = $this->resource->getConnection();
        try {
            $connection->beginTransaction();
            foreach ($collection->getItems() as $item) {
                $connection->update(
                    'subscriptions',
                    [
                        'next_order_date' => $this->helper->getNextDateTime((int)$item->getData('period')),
                        'last_order_date' => date('Y-m-d H:i:s', time())
                    ],
                    ['item_id IN (?)' => $item->getData('item_id')]
                );
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * @throws NoSuchEntityException
     */
    private function getProductForItem($orderItem): ProductInterface
    {
        return $this->productRepositoryFactory->create()->getById($orderItem->getProductId());
    }

    private function getQuote(int $customerId): Quote
    {
        return $this->quoteFactory->create()->setCustomerId($customerId);
    }

    private function getOrderItem(): OrderItemRepositoryInterface
    {
        return $this->orderItemRepositoryFactory->create();
    }

    private function getQuoteItem(): CartItemInterface
    {
        return $this->quoteItemFactory->create();
    }

    private function getQuoteRepository(): CartRepositoryInterface
    {
        return $this->quoteRepositoryFactory->create();
    }

    private function getOrderRepository(): OrderRepositoryInterface
    {
        return $this->orderRepositoryFactory->create();
    }

    private function getQuoteManagement(): CartManagementInterface
    {
        return $this->quoteManagementFactory->create();
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
            $orderItem = $this->getOrderItem()->get($itemId);
            $quoteItem = $this->getQuoteItem()
                ->setProduct($this->getProductForItem($orderItem))
                ->setQty($orderItem->getQtyOrdered())
                ->setPrice($orderItem->getPrice());
            $quoteItem = $this->setOptions($orderItem, $quoteItem);
            $quote = $this->getQuote($customerId)->addItem($quoteItem);
        }

        /*
         $quote->getBillingAddress();
         $quote->getShippingAddress()->setCollectShippingRates(true);
         $quote->getShippingAddress()->collectShippingRates();
         $quote->setPaymentMethod('checkmo');
        */

        if (isset($quote)) {
            $this->getQuoteRepository()->save($quote);
            $this->getOrderRepository()->save(
                $this->getQuoteManagement()->submit($quote)
            );
        }

        return array_merge($result, $itemIds);
    }

    private function getSubscriptionsGroupedByCustomerQuery(): array
    {
        $collection = $this->getSubscriptionCollection();
        $collection->addFieldToFilter('next_order_date', ['lteq' => new Zend_Db_Expr('NOW()')]);
        $collection->getSelect()->columns(['item_ids' => new Zend_Db_Expr('GROUP_CONCAT(item_id)')]);
        $collection->getSelect()->group('customer_id');

        return $collection->toArray(['customer_id', 'item_ids']);
    }

    private function getCustomerItems(): array
    {
        return $this->getSubscriptionsGroupedByCustomerQuery()['items'];
    }

    private function getGroupedByCustomer(): array
    {
        $result = [];
        foreach ($this->getCustomerItems() as $item) {
            $result[$item['customer_id']] = array_map('intval', explode(',', $item['item_ids']));
        }

        return $result;
    }
}
