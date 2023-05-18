<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ReOrder;

use Osio\Subscriptions\Model\ResourceModel\Customers\Collection as CustomerCollection;
use Osio\Subscriptions\Model\ResourceModel\Subscribe\Collection as SubscribeCollection;

class Customer
{

    public function __construct(
        private readonly SubscribeCollection $subscribeCollection,
        private readonly CustomerCollection  $customers,

    )
    {
    }

    public function getCustomerData(): array
    {
        $customersData = [];

        foreach ($this->customers->fetchCustomers($this->getCustomerIds())->getItems() as $customer) {
            $customersData[$customer->getData('entity_id')] = $customer;
        }

        return $customersData;
    }


    private function getCustomerIds(): array
    {
        return array_keys($this->subscribeCollection->getGroupedByCustomer());
    }

}
