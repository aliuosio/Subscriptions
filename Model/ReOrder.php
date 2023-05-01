<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model;

use Osio\Subscriptions\Model\ResourceModel\Subscribe\Collection as subscriptionCollection;
use Zend_Db_Expr;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Service\QuoteService;
use Magento\Quote\Model\Quote;

class ReOrder
{

    public function __construct(
        private readonly subscriptionCollection $subscriptionCollection,
        private readonly OrderFactory $orderFactory,
        private readonly QuoteService $quoteService
    )
    {
    }

    public function reorderItems(array $itemIds, int $customerId)
    {
        // Load the order items from the database using the item ids
        $orderItems = $this->orderFactory->create()
            ->getCollection()
            ->addFieldToFilter('item_id', ['in' => $itemIds])
            ->getItems();

        // Create a new quote
        $quote = $this->quoteService->createEmptyCart();
        $quote->setCustomer($customerId);

        // Add the order items to the quote
        foreach ($orderItems as $item) {
            $quoteItem = $quote->addProduct(
                $item->getProduct(),
                ['qty' => $item->getQtyOrdered()]
            );
            $quoteItem->setOptions($item->getProductOptions());
        }

        // Set the same shipping and billing addresses from the original order
        $originalOrder = reset($orderItems)->getOrder();
        $shippingAddress = $originalOrder->getShippingAddress()->getData();
        $billingAddress = $originalOrder->getBillingAddress()->getData();
        $quote->getShippingAddress()->addData($shippingAddress);
        $quote->getBillingAddress()->addData($billingAddress);

        // Set the same shipping and payment methods from the original order
        $quote->getShippingAddress()->setShippingMethod($originalOrder->getShippingMethod());
        $quote->getPayment()->setMethod($originalOrder->getPayment()->getMethod());

        // Collect totals and save the quote
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->collectTotals();
        $quote->save();

        // Create a new order based on the quote
        $order = $this->quoteService->submit($quote);
        $order->save();

        return $order;
    }

    public function execute(): array
    {
        return $this->getItemsGroupedByCustomer();
    }

    private function getItemsGroupedByCustomerQuery(): array
    {
        $this->subscriptionCollection->addFieldToFilter('next_order_date', ['lteq' => new Zend_Db_Expr('NOW()')]);
        $this->subscriptionCollection->getSelect()->columns(['item_ids' => new Zend_Db_Expr('GROUP_CONCAT(item_id)')]);
        $this->subscriptionCollection->getSelect()->group('customer_id');

        return $this->subscriptionCollection->toArray(['customer_id', 'item_ids']);
    }

    private function getItemsGroupedByCustomer(): array
    {
        $result = [];
        foreach ($this->getItemsGroupedByCustomerQuery()['items'] as $item) {
            $result[$item['customer_id']] = array_map('intval', explode(',', $item['item_ids']));
        }

        return $result;
    }
}
