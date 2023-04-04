<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    const TITLE = 'system/subscribable/title';
    const CODE = 'system/subscribable/code';
    const PERIODS = 'system/subscribable/periods';
    const ENABLED = 'system/subscribable/enabled';
    const DELIMITER = ',';

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function isEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::ENABLED);
    }

    public function getTitle(): mixed
    {
        return $this->scopeConfig->getValue(self::TITLE);
    }

    public function getCode(): mixed
    {
        return $this->scopeConfig->getValue(self::CODE);
    }

    public function getPeriod(): array
    {
        return explode(self::DELIMITER, $this->scopeConfig->getValue(self::PERIODS));
    }
}
