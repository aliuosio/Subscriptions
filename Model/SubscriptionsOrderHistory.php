<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Osio\Subscriptions\Model\ResourceModel\SubscriptionsOrderHistory as ResourceModelSubscriptionsOrderHistory;

class SubscriptionsOrderHistory extends AbstractModel implements IdentityInterface
{
    public const CACHE_TAG = 'subscribtions_order_history';
    protected string $cacheTag = 'subscribtions_order_history';
    protected string $eventPrefix = 'subscribtions_order_history';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModelSubscriptionsOrderHistory::class);
    }

    public function getIdentities(): array
    {
        return [SubscriptionsOrderHistory::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues(): array
    {
        return [];
    }
}
