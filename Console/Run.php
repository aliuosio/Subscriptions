<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Console;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Osio\Subscriptions\Model\ReOrder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;

class Run extends Command
{
    /**
     * @throws LocalizedException
     */
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
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('subscription:run')
            ->setDescription('Reorder Items from subscription table that are due');

        parent::configure();
    }

    /**
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $output->writeln(print_r($this->reorder->execute(), true));

        return 1;
    }
}
