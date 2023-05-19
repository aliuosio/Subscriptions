<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ReOrder;

use Magento\Quote\Model\Quote;

class Address
{
    public function set(Customer $customer, Quote $quote, int $customerId): Quote
    {
        $quote->getBillingAddress()->addData(
            $customer->getCustomerData()[$customerId]->getDefaultBillingAddress()->toArray()
        );

        $quote->getShippingAddress()->addData(
            $customer->getCustomerData()[$customerId]->getDefaultShippingAddress()->toArray()
        );

        return $quote;
    }
}
