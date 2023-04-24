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
    )
    {
    }

    public function afterSetStatus(OrderInterface $subject, OrderInterface $result): OrderInterface
    {
        if ($this->helper->isEnabled() && $result->getStatus() == 'complete') {

            $data = [];

            foreach ($result->getItems() as $item) {
                foreach ($item->getData('product_options') as $options) {
                    if (is_array($options)) {
                        foreach ($options as $option) {
                            if (isset($option['label']) && $option['label'] == $this->helper->getTitle()) {
                                $data[$item->getItemId()]['item_id'] = $item->getItemId();
                                $data[$item->getItemId()]['customer_id'] = $result->getCustomerId();
                                $data[$item->getItemId()]['period'] = $option['value'];
                            }
                        }
                    }
                }
            }

            if (!empty($data)) {
                $this->insertMultiple($data);
            }
        }

        return $result;
    }

    public function insertMultiple(array $data): int
    {
        return $this->resource->getConnection()
            ->insertMultiple('subscriptions', $data);
    }
}
