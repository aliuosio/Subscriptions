<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Cron;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use vendor\Model\ReOrder;

class Run
{
    public function __construct(
        private readonly ReOrder $reOrder
    )
    {
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws InputException
     */
    protected function execute(): void
    {
        $this->reOrder->execute();
    }
}
