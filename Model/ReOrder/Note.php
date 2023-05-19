<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ReOrder;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;

class Note
{
    public function __construct(
        private readonly OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory,
    )
    {
    }

    public function add(OrderInterface $order, string $message, string $status): OrderInterface
    {
        return $order->addStatusHistory(
            $this->orderStatusHistoryFactory->create()
                ->setComment($message)
                ->setEntityName(OrderStatusHistoryInterface::ENTITY_NAME)
                ->setStatus($status)
                ->setIsCustomerNotified(false)
        );
    }
}
