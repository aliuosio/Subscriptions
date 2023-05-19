<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ReOrder;

use Magento\Sales\Api\Data\OrderInterface;
use Osio\Subscriptions\Model\ResourceModel\Subscribe\Collection as SubscribeCollection;

class History
{
    private function setHistory(OrderInterface $order, array $itemIds): array
    {
        return array_map(function ($itemId) use ($order) {
            return ['item_id' => $itemId, 'new_order_id' => $order->getEntityId()];
        }, $itemIds);
    }

    public function save(SubscribeCollection $subscribeCollection, OrderInterface $order, array $itemIds): void
    {
        $subscribeCollection->getConnection()
            ->insertMultiple('subscriptions_history', $this->setHistory($order, $itemIds));
    }
}
