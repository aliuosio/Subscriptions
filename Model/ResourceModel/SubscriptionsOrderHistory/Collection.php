<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ResourceModel\SubscriptionsOrderHistory;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Osio\Subscriptions\Model\ResourceModel\SubscriptionsOrderHistory as ResourceSubscriptionsOrderHistory;
use Osio\Subscriptions\Model\SubscriptionsOrderHistory;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    protected string $idFieldName = 'id';
    protected string $eventPrefix = 'subscriptions_order_history_collection';
    protected string $eventObject = 'subscriptions_order_history_collection';
    private Helper $helper;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface        $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface       $eventManager,
        AdapterInterface       $connection = null,
        AbstractDb             $resource = null
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(SubscriptionsOrderHistory::class, ResourceSubscriptionsOrderHistory::class);
    }
}
