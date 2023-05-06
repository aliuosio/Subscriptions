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
use Osio\Subscriptions\Helper\Data as Helper;
use Osio\Subscriptions\Model\ResourceModel\Subscribe\CollectionFactory as subscriptionCollectionFactory;
use Zend_Db_Expr;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\OrderRepositoryInterface;

class ReOrder
{

    public function __construct(
        private readonly subscriptionCollectionFactory $subscriptionCollectionFactory,
        private readonly QuoteFactory                  $quoteFactory,
        private readonly ItemRepository                $orderItemRepository,
        private readonly ItemFactory                   $quoteItemFactory,
        private readonly QuoteManagement               $quoteManagement,
        private readonly ProductRepositoryInterface    $productRepository,
        private readonly CartRepositoryInterface       $quoteRepository,
        private readonly Helper                        $helper,
        private readonly ResourceConnection            $resource,
        private readonly OrderRepositoryInterface      $orderRepository
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


    /**
     * @throws Exception
     */
    private function updateSubscriptionsAfterReOrder(array $result): void
    {

        $collection = $this->subscriptionCollectionFactory->create();
        $collection->addFieldToSelect(['item_id', 'period']);
        $collection->addFieldToFilter('item_id', ['in' => $result]);
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
     * @throws LocalizedException
     * @throws InputException
     * @throws Exception
     */
    private function setCustomerOrder(int $customerId, array $itemIds): array
    {
        $result = [];
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
            $quote->addItem($quoteItem);
        }

        /*
         $quote->getBillingAddress();
         $quote->getShippingAddress()->setCollectShippingRates(true);
         $quote->getShippingAddress()->collectShippingRates();
         $quote->setPaymentMethod('checkmo');
        */

        $this->quoteRepository->save($quote);
        $this->orderRepository->save(
            $this->quoteManagement->submit($quote)
        );

        return array_merge($result, $itemIds);
    }

    private function getSubscriptionsGroupedByCustomerQuery(): array
    {
        $collection = $this->subscriptionCollectionFactory->create();
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
