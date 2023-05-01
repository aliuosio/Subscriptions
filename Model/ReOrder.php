<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model;

use Osio\Subscriptions\Model\ResourceModel\Subscribe\Collection as subscriptionCollection;

class ReOrder
{

    public function __construct(
        private readonly subscriptionCollection $subscriptionCollection
    )
    {
    }

    public function execute()
    {
        $this->subscriptionCollection->getSelect()
            ->where('next_order_date <= NOW()')
            ->group('customer_id')
            ->columns(array('item_ids' => new \Zend_Db_Expr('GROUP_CONCAT(item_id)')));
        $data = [];
        foreach ($this->subscriptionCollection as $subscription) {
            $data[$subscription->getData('customer_id')]['item_ids'] = $subscription->getData('item_ids');
        }

        return $data;
    }
}
