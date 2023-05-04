<?php

declare(strict_types=1);

namespace vendor\Model\ResourceModel\Subscribe;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use vendor\Model\ResourceModel\Subscribe as ResourceModelSubscribe;
use vendor\Model\Subscribe;

class Collection extends AbstractCollection
{
    protected string $idFieldName = 'id';
    protected string $eventPrefix = 'osio_subscriptions_subscribe_collection';
    protected string $eventObject = 'subscribe_collection';

    protected function _construct(): void
    {
        $this->_init(Subscribe::class, ResourceModelSubscribe::class);
    }
}
