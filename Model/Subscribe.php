<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Osio\Subscriptions\Model\ResourceModel\Subscribe as ResourceModelSubscribe;

class Subscribe extends AbstractModel implements IdentityInterface
{
    public const CACHE_TAG = 'osio_subscriptions_subscribe';
    protected string $cacheTag = 'osio_subscriptions_subscribe';
    protected string $eventPrefix = 'osio_subscriptions_subscribe';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModelSubscribe::class);
    }

    public function getIdentities(): array
    {
        return [Subscribe::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues(): array
    {
        return [];
    }
}
