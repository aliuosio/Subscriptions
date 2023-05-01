<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Plugins;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\OrderInterface;
use Osio\Subscriptions\Helper\Data as Helper;

class OrderItemsToSubscribeSet
{
    public function __construct(
        private readonly Helper             $helper,
        private readonly ResourceConnection $resource
    ) {
    }

    public function afterSetStatus(OrderInterface $subject, OrderInterface $result): OrderInterface
    {
        if (!$this->helper->isEnabled() || $result->getStatus() !== 'complete') {
            return $result;
        }

        $data = [];
        foreach ($result->getItems() as $item) {
            $options = $item->getData('product_options');

            if (!is_array($options)) {
                continue;
            }

            $period = $this->getSubscriptionPeriod($options);
            if (!$period) {
                continue;
            }

            $data[] = [
                'item_id'        => $item->getItemId(),
                'customer_id'    => $result->getCustomerId(),
                'period'         => (int) $period,
                'next_order_date' => $this->helper->getNextDateTime((int) $period),
            ];
        }

        if (!empty($data)) {
            $this->insertMultiple($data);
        }

        return $result;
    }

    private function getSubscriptionPeriod(array $options): ?string
    {
        foreach ($options as $option) {
            if (isset($option['label']) && $option['label'] === $this->helper->getTitle() && isset($option['value'])) {
                return $option['value'];
            }
        }
        return null;
    }

    private function insertMultiple(array $data): void
    {
        $this->resource->getConnection()->insertMultiple('subscriptions', $data);
    }
}
