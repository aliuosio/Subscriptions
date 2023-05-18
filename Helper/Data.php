<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Helper;

use DateTime;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class   Data extends AbstractHelper
{
    const TITLE = 'system/subscribable/title';
    const CODE = 'system/subscribable/code';
    const FIELDSET = 'system/subscribable/fieldset';
    const PERIODS = 'system/subscribable/periods';
    const ENABLED = 'system/subscribable/enabled';
    const DELIMITER = ',';
    const CUSTOMER_NOTE = 'system/subscribable/customer_note';
    const SALES_NOTE = 'system/subscribable/sales_note';
    const PAYMENT_METHOD = 'system/subscribable/payment_method';
    const SHIPPING_METHOD = 'system/subscribable/shipping_method';

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function getCustomerNote(): mixed
    {
        return $this->scopeConfig->getValue($this::CUSTOMER_NOTE);
    }

    public function getShippingMethod(): mixed
    {
        return $this->scopeConfig->getValue($this::SHIPPING_METHOD);
    }

    public function getPaymentMethod(): mixed
    {
        return $this->scopeConfig->getValue($this::PAYMENT_METHOD);
    }

    public function getSalesNote(): mixed
    {
        return $this->scopeConfig->getValue($this::SALES_NOTE);
    }

    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue($this::ENABLED);
    }

    public function getTitle(): mixed
    {
        return $this->scopeConfig->getValue($this::TITLE);
    }

    public function getCode(): mixed
    {
        return $this->scopeConfig->getValue($this::CODE);
    }

    public function getFieldset(): mixed
    {
        return $this->scopeConfig->getValue($this::FIELDSET);
    }

    public function getPeriod(): array
    {
        return explode($this::DELIMITER, $this->scopeConfig->getValue($this::PERIODS));
    }

    public function getNextDate(int $period): DateTime
    {
        return new DateTime("+$period week");
    }

    public function getNextDateTime(int $period): string
    {
        return $this->getNextDate($period)
            ->format('Y-m-d H:i:s');
    }
}
