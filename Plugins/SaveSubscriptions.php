<?php

namespace Osio\Subscriptions\Plugins;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Psr\Log\LoggerInterface;

class SaveSubscriptions
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function afterPlace(
        OrderManagementInterface $orderManagement,
        OrderInterface $order
    ): OrderInterface {
        $this->logger->info('TEST BEFORE');
        if ($order->getIncrementId()) {
            $this->logger->info('TEST AFTER');
            $this->logger->info($order->getIncrementId());
        }
        return $order;
    }
}
