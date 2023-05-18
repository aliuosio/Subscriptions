<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Cron;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Osio\Subscriptions\Model\ReOrder;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;

class Run
{
    public function __construct(
        private readonly ReOrder $reorder,
        private readonly State   $state,
        string                   $name = null
    )
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        parent::__construct($name);
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
