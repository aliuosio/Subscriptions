<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ReOrder;

use Magento\Quote\Model\Quote;

class Payment
{
    public function set(Quote $quote, string $method): Quote
    {
        $quote->getPayment()->setMethod($method);
        $quote->setPayment($quote->getPayment());

        return $quote;
    }
}
