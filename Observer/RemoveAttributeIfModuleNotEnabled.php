<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Osio\Subscriptions\Helper\Data as Helper;

class RemoveAttributeIfModuleNotEnabled implements ObserverInterface
{
    public function __construct(
        private readonly Helper $helper
    ) {
    }

    public function execute(Observer $observer): void
    {
        $observer->getForm()
            ->getElement($this->helper->getFieldset())
            ->removeField($this->helper->getCode());
    }
}
