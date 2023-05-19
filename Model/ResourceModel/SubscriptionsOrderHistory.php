<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class SubscriptionsOrderHistory extends AbstractDb
{
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init('subscriptions_order_history', 'id');
    }
}
