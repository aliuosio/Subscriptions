<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Osio\Subscriptions\Helper\Data as Helper;
use Osio\Subscriptions\Model\ResourceModel\Subscribe\Collection as SubscribeCollection;
use Magento\Quote\Api\CartManagementInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterfaceFactory;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Sales\Api\OrderItemRepositoryInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class ReOrder
{

    public function __construct(
        private readonly ProductRepositoryInterfaceFactory   $productRepositoryFactory,
        private readonly Helper                              $helper,
        private readonly SubscribeCollection                 $subscribeCollection,
        private readonly CustomerRepositoryInterfaceFactory  $customerRepositoryFactory,
        private readonly CartInterfaceFactory                $quoteFactory,
        private readonly OrderItemRepositoryInterfaceFactory $orderItemRepositoryFactory,
        private readonly CartItemInterfaceFactory            $quoteItemFactory,
        private readonly CartRepositoryInterfaceFactory      $quoteRepositoryFactory,
        private readonly OrderRepositoryInterfaceFactory     $orderRepositoryFactory,
        private readonly CartManagementInterfaceFactory      $quoteManagementFactory,
        private readonly OrderStatusHistoryInterfaceFactory  $orderStatusHistoryFactory,
        private readonly OrderSender                         $orderSender,
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
        foreach ($this->subscribeCollection->getGroupedByCustomer() as $customerId => $itemIds) {
            $result = array_merge($result, $this->setCustomerOrder($customerId, $itemIds));
        }

        if (!empty($result)) {
            $this->subscribeCollection->updateSubscriptionsAfterReOrder($result);
        }

        return $result;
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
     * @throws LocalizedException
     */
    private function setOrderItems(array $itemIds, int $customerId): ?Quote
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
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function setCustomerOrder(int $customerId, array $itemIds): array
    {
        $result = [];
        $quote = $this->setOrderItems($itemIds, $customerId);

        if (isset($quote) && isset($this->customersData[$customerId])) {
            $quote = $this->setAddress($quote, $customerId);
            $quote = $this->setShippingMethod($quote);
            $quote = $this->setPayment($quote);
            $quote->assignCustomer($this->getCustomer($customerId))
                ->setStoreId($this->customersData[$customerId]->getStoreId());

            $this->quoteRepositoryFactory->create()
                ->save($quote);
            $order = $this->quoteManagementFactory->create()
                ->submit($quote);
            $this->getOrderRepository()->save($order);

            $this->orderSender->send($order);
            $this->addReorderNoteToOrder($order);

            return array_merge($result, $itemIds);
        }

        return $result;
    }

    private function addReorderNoteToOrder(OrderInterface $order): void
    {
        $order->addStatusHistory(
            $this->orderStatusHistoryFactory->create()->setComment($this->helper->getSalesNote())
                ->setEntityName(OrderStatusHistoryInterface::ENTITY_NAME)
                ->setStatus('pending')
                ->setIsCustomerNotified(false)
        );

        $this->getOrderRepository()->save($order);
    }

    private function setShippingMethod(Quote $quote): Quote
    {
        $quote->getShippingAddress()->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($this->helper->getShippingMethod());

        return $quote;
    }

    private function setPayment(Quote $quote): Quote
    {
        $quote->getPayment()->setMethod($this->helper->getPaymentMethod());
        $quote->setPayment($quote->getPayment());

        return $quote;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function getCustomer(int $customerId): CustomerInterface
    {
        return $this->customerRepositoryFactory->create()->getById($customerId);
    }

    private function getOrderRepository(): OrderRepositoryInterface
    {
        return $this->orderRepositoryFactory->create();
    }

}
