<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Cron;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Osio\Subscriptions\Model\ReOrder;
use Psr\Log\LoggerInterface;

class Run
{
    public function __construct(
        private readonly ReOrder         $reOrder,
        private readonly LoggerInterface $logger
    )
    {
    }


    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws InputException
     */
    protected function execute(): Run
    {
        $this->reOrder->execute();
        $this->logger->debug('Cron: Osio\Subscriptions\Cron ran successfully');

        return $this;
    }
}
