<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ReOrder;

use Magento\Quote\Model\Quote;

class Address
{

    public function __construct(
        private readonly Customer $customer
    )
    {

    }

    public function set(Quote $quote, int $customerId): Quote
    {
        $quote->getBillingAddress()->addData(
            $this->customer->getCustomerData()[$customerId]->getDefaultBillingAddress()->toArray()
        );

        $quote->getShippingAddress()->addData(
            $this->customer->getCustomerData()[$customerId]->getDefaultShippingAddress()->toArray()
        );

        return $quote;
    }
}
