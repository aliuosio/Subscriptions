<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ReOrder;

use Magento\Quote\Model\Quote;
use Osio\Subscriptions\Helper\Data as Helper;

class Payment
{

    public function __construct(
        private readonly Helper $helper,
    )
    {
    }

    public function set(Quote $quote): Quote
    {
        $quote->getPayment()->setMethod($this->helper->getPaymentMethod());
        $quote->setPayment($quote->getPayment());

        return $quote;
    }
}
