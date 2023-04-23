<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Plugins;

use Magento\Sales\Api\Data\OrderInterface;
use Osio\Subscriptions\Helper\Data as Helper;

class OrderItemsToSubscribeSet
{
    public function __construct(
        private readonly Helper $helper
    ) {
    }

    public function afterSetStatus(OrderInterface $subject, OrderInterface $result)
    {

        if ($this->helper->isEnabled()) {
            $result->getStatus();
        }

        return $result;
    }
}
