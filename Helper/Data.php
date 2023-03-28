<?php
/**
 * Copyright © DEVHH, Inc. All rights reserved.
 * See LICENSE.TXT for license details.
 */

namespace Osio\Subscriptions\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    const TITLE = 'system/subscribable/title';
    const PERIODS = 'system/subscribable/periods';
    const ENABLED = 'system/subscribable/enabled';
    const DELIMITER = ',';

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->getValue(self::ENABLED) === 1;
    }

    public function getTitle(): mixed
    {
        return $this->scopeConfig->getValue(self::TITLE);
    }

    public function getPeriod(): array
    {
        return explode(self::DELIMITER, $this->scopeConfig->getValue(self::PERIODS));
    }
}