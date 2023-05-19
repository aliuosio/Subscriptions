<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ReOrder;

use Magento\Quote\Model\Quote;
use Osio\Subscriptions\Model\ResourceModel\Subscribe\Collection as SubscribeCollection;

class Address
{
    public function set(
        SubscribeCollection $subscribeCollection,
        Customer            $customer,
        Quote               $quote,
        int                 $customerId
    ): Quote
    {
        $quote->getBillingAddress()->addData(
            $customer->getCustomerData($subscribeCollection)[$customerId]->getDefaultBillingAddress()->toArray()
        );

        $quote->getShippingAddress()->addData(
            $customer->getCustomerData($subscribeCollection)[$customerId]->getDefaultShippingAddress()->toArray()
        );

        return $quote;
    }
}
