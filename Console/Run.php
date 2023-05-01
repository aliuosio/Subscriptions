<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Console;

use Osio\Subscriptions\Model\ReOrder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Run extends Command
{
    public function __construct(
        private readonly ReOrder $reorder,
        string                   $name = null
    )
    {
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
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->reorder->execute();
        $output->writeln(print_r($result, true));

        return 1;
    }
}
