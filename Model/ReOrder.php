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
use Magento\Sales\Api\Data\OrderItemInterface;
use Osio\Subscriptions\Helper\Data as Helper;
use Osio\Subscriptions\Model\ResourceModel\Subscribe\Collection as SubscribeCollection;
use Osio\Subscriptions\Model\ResourceModel\Customers\Collection as CustomerCollection;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartManagementInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterfaceFactory;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;
use Magento\Quote\Api\PaymentMethodManagementInterfaceFactory;

class ReOrder
{

    private array $customersData;

    const PAYMENT_METHOD = 'checkmo';
    const SHIPPING_METHOD = 'flatrate_flatrate';

    public function __construct(
        private readonly ProductRepositoryInterfaceFactory       $productRepositoryFactory,
        private readonly Helper                                  $helper,
        private readonly SubscribeCollection                     $subscribeCollection,
        private readonly CustomerCollection                      $customers,
        private readonly CustomerRepositoryInterfaceFactory      $customerRepositoryFactory,
        private readonly CartInterfaceFactory                    $quoteFactory,
        private readonly OrderItemRepositoryInterfaceFactory     $orderItemRepositoryFactory,
        private readonly CartItemInterfaceFactory                $quoteItemFactory,
        private readonly CartRepositoryInterfaceFactory          $quoteRepositoryFactory,
        private readonly OrderRepositoryInterfaceFactory         $orderRepositoryFactory,
        private readonly CartManagementInterfaceFactory          $quoteManagementFactory,
        private readonly PaymentMethodManagementInterfaceFactory $paymentMethodManagementFactory
    )
    {
    }


    /**
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws LocalizedException
     */
    public function execute(): array
    {
        $this->getCustomerData();

        return $this->run();
    }

    /**
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws LocalizedException
     * @throws Exception
     */
    private function run(): array
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

    private function getCustomerData(): void
    {
        foreach ($this->customers->fetchCustomers($this->getCustomerIds())->getItems() as $customer) {
            $this->customersData[$customer->getEntityId()] = $customer;
        }
    }

    private function getCustomerIds(): array
    {
        return array_keys($this->subscribeCollection->getGroupedByCustomer());
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
            $orderItem = $this->getOrderItem()->get($itemId);
            $quoteItem = $this->getQuoteItem()
                ->setProduct($this->getProductForItem($orderItem))
                ->setQty($orderItem->getQtyOrdered())
                ->setPrice($orderItem->getPrice());
            $quote = $this->getQuote($customerId)->addItem(
                $this->setOptionsAndAttributes($orderItem, $quoteItem)
            );
        }

        return (isset($quote)) ? $quote : null;
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
        $quote = $this->setOrderItems($itemIds, $customerId);

        if (isset($quote) && isset($this->customersData[$customerId])) {
            $quote = $this->setAddress($quote, $this->getCustomer($customerId));
            $quote = $this->setShippingMethod($quote);
            $quote = $this->setPayment($quote);
            $quote->assignCustomer($this->getCustomer($customerId))
                ->setStoreId($this->customersData[$customerId]->getStoreId());

            $this->getQuoteRepository()->save($quote);
            $this->getOrderRepository()->save(
                $this->getQuoteManagement()->submit($quote)
            );

            return array_merge($result, $itemIds);
        }

        return $result;
    }

    private function setAddress(Quote $quote, CustomerInterface $customer): Quote
    {
        $quote->getBillingAddress()->setId($customer->getDefaultBilling());
        $quote->getShippingAddress()->setId($customer->getDefaultShipping());

        return $quote;
    }

    private function setShippingMethod(Quote $quote): Quote
    {
        $quote->getShippingAddress()->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($this::SHIPPING_METHOD);

        return $quote;
    }

    private function setPayment(Quote $quote): Quote
    {
        $quote->getPayment()->setMethod(ReOrder::PAYMENT_METHOD);
        $quote->setPayment($quote->getPayment());

        return $quote;
    }

    private function getCustomer(int $customerId): CustomerInterface
    {
        return $this->customerRepositoryFactory->create()->getById($customerId);
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

}
