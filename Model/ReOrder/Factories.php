<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ReOrder;

use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartManagementInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterfaceFactory;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;

class Factories
{

    public function __construct(
        private readonly CartInterfaceFactory                $quoteFactory,
        private readonly OrderItemRepositoryInterfaceFactory $orderItemRepositoryFactory,
        private readonly CartItemInterfaceFactory            $quoteItemFactory,
        private readonly CartRepositoryInterfaceFactory      $quoteRepositoryFactory,
        private readonly OrderRepositoryInterfaceFactory     $orderRepositoryFactory,
        private readonly CartManagementInterfaceFactory      $quoteManagementFactory,
    )
    {
    }
    public function getQuote(int $customerId): Quote
    {
        return $this->quoteFactory->create()->setCustomerId($customerId);
    }

    public function getOrderItem(): OrderItemRepositoryInterface
    {
        return $this->orderItemRepositoryFactory->create();
    }

    public function getQuoteItem(): CartItemInterface
    {
        return $this->quoteItemFactory->create();
    }

    public function getQuoteRepository(): CartRepositoryInterface
    {
        return $this->quoteRepositoryFactory->create();
    }

    public function getOrderRepository(): OrderRepositoryInterface
    {
        return $this->orderRepositoryFactory->create();
    }

    public function getQuoteManagement(): CartManagementInterface
    {
        return $this->quoteManagementFactory->create();
    }
}
