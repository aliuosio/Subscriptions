<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ReOrder;

use Magento\Quote\Model\Quote;
use Osio\Subscriptions\Helper\Data as Helper;

class Shipping
{
    public function __construct(
        private readonly Helper $helper,
    )
    {
    }

    public function set(Quote $quote): Quote
    {
        $quote->getShippingAddress()->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($this->helper->getShippingMethod());

        return $quote;
    }
}
