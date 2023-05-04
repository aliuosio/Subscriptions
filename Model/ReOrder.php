<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\Order\ItemRepository;
use Osio\Subscriptions\Model\ResourceModel\Subscribe\Collection as subscriptionCollection;
use Zend_Db_Expr;

class ReOrder
{

    public function __construct(
        private readonly subscriptionCollection     $subscriptionCollection,
        private readonly QuoteFactory               $quoteFactory,
        private readonly ItemRepository             $orderItemRepository,
        private readonly ItemFactory                $quoteItemFactory,
        private readonly QuoteManagement            $quoteManagement,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CartRepositoryInterface    $quoteRepository
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
            $result = $this->setCustomerOrder($customerId, $itemIds);
        }

        return $result;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws InputException
     * @throws Exception
     */
    private function setCustomerOrder(int $customerId, array $itemIds): array
    {
        $quote = $this->quoteFactory->create();
        $quote->setCustomerId($customerId);
        $quote->setStoreId($quote->getStore()->getId());

        foreach ($itemIds as $itemId) {
            $orderItem = $this->orderItemRepository->get($itemId);
            $quoteItem = $this->quoteItemFactory->create();
            $quoteItem->setProduct($this->productRepository->getById($orderItem->getProductId()));
            $quoteItem->setQty($orderItem->getQtyOrdered());
            $quoteItem->setPrice($orderItem->getPrice());
            $options = $orderItem->getProductOptions();
            if (isset($options['options'])) {
                foreach ($options['options'] as $option) {
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
            $quote->addItem($quoteItem);
        }

        $quote->getBillingAddress();
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getShippingAddress()->collectShippingRates();
        $quote->setPaymentMethod('checkmo');
        $this->quoteRepository->save($quote);
        $order = $this->quoteManagement->submit($quote);
        $order->save();
        $result[$customerId] = $order->getIncrementId();

        return $result;
    }

    private function getSubscriptionsGroupedByCustomerQuery(): array
    {
        $this->subscriptionCollection->addFieldToFilter('next_order_date', ['lteq' => new Zend_Db_Expr('NOW()')]);
        $this->subscriptionCollection->getSelect()->columns(['item_ids' => new Zend_Db_Expr('GROUP_CONCAT(item_id)')]);
        $this->subscriptionCollection->getSelect()->group('customer_id');

        return $this->subscriptionCollection->toArray(['customer_id', 'item_ids']);
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
