<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ResourceModel\Subscribe;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Osio\Subscriptions\Model\ResourceModel\Subscribe as ResourceModelSubscribe;
use Osio\Subscriptions\Model\Subscribe;

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
