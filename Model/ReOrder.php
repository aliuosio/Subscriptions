<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteManagement;
use Osio\Subscriptions\Model\ResourceModel\Subscribe\Collection as subscriptionCollection;
use Zend_Db_Expr;
use Magento\Framework\App\ObjectManager;

class ReOrder
{

    public function __construct(
        private readonly subscriptionCollection $subscriptionCollection
    )
    {
    }

    /**
     * @throws LocalizedException
     * @throws Exception
     */
    public function execute(): array
    {
        $result = [];
        foreach ($this->getGroupedByCustomer() as $customerId => $itemIds) {
            $quote = ObjectManager::getInstance()->create(Quote::class);
            $quote->setCustomerById($customerId);
            $quote->setStoreId($quote->getStore()->getId());
            foreach ($itemIds as $itemId) {
                $item = ObjectManager::getInstance()->create(Product::class)->load($itemId);
                $quoteItem = ObjectManager::getInstance()->create(Item::class);
                $quoteItem->setProduct($item);
                $quoteItem->setQty(1);
                $quoteItem->setPrice($item->getFinalPrice());
                $quote->addItem($quoteItem);
            }
            $quote->getBillingAddress();
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->getShippingAddress()->collectShippingRates();
            $quote->setPaymentMethod('checkmo');
            $quote->setInventoryProcessed(false);
            $quote->save();
            $order = ObjectManager::getInstance()->create(QuoteManagement::class)->submit($quote);
            $order->setEmailSent(0);
            $order->save();
            $result[$customerId] = $order->getIncrementId();
        }
        return $result;
    }

    private function getSubscriptionsGroupedByCustomerQuery(): array
    {
        $this->subscriptionCollection->addFieldToFilter('next_order_date', ['lteq' => new Zend_Db_Expr('NOW()')]);
        $this->subscriptionCollection->getSelect()->columns(['item_ids' => new Zend_Db_Expr('GROUP_CONCAT(item_id)')]);
        $this->subscriptionCollection->getSelect()->group('customer_id');

        return $this->subscriptionCollection->toArray(['customer_id', 'item_ids']);
    }

    private function getOnlyItems(): array
    {
        return $this->getSubscriptionsGroupedByCustomerQuery()['items'];
    }

    private function getGroupedByCustomer(): array
    {
        $result = [];
        foreach ($this->getOnlyItems() as $item) {
            $result[$item['customer_id']] = array_map('intval', explode(',', $item['item_ids']));
        }

        return $result;
    }
}
