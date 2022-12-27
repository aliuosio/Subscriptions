<?php

namespace Osio\Subscriptions\CommandLine\Console;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Osio\Subscriptions\Model\ResourceModel\Subscribe as SubscribeResource;
use Osio\Subscriptions\Model\Subscribe;
use Osio\Subscriptions\Model\SubscribeFactory;
use Osio\Subscriptions\Logger\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Runner extends Command
{

    public function __construct(
        private readonly SubscribeFactory  $subscribeFactory,
        private readonly SubscribeResource $subscribeResource,
        private readonly LoggerInterface   $logger,
        string                             $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('subscriptions:insert');
        $this->setDescription('Add Subscription');
        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dataSet = [
            'item_id' => 1,
            'period' => 14,
            'next_order_date' => '2022-11-09 00:01:00',
            'last_order_date' => '2021-11-05 00:01:00'
        ];

        try {
            $this->subscribeResource->save(
                $this->getSubscribe()->setData($dataSet)
            );
        } catch (AlreadyExistsException|Exception $e) {
            $output->writeln($e->getMessage());
            $this->logger->critical($e->getTraceAsString());
        }
    }

    protected function getSubscribe(): Subscribe
    {
        return $this->subscribeFactory->create();
    }
}
