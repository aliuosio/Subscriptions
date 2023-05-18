<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ReOrder;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;
use Osio\Subscriptions\Helper\Data as Helper;

class Note
{

    public function __construct(
        private readonly Helper                             $helper,
        private readonly OrderStatusHistoryInterfaceFactory $orderStatusHistoryFactory,
        private readonly OrderRepositoryInterfaceFactory     $orderRepositoryFactory,
    )
    {
    }

    public function add(OrderInterface $order): void
    {
        $order->addStatusHistory(
            $this->orderStatusHistoryFactory->create()->setComment($this->helper->getSalesNote())
                ->setEntityName(OrderStatusHistoryInterface::ENTITY_NAME)
                ->setStatus('pending')
                ->setIsCustomerNotified(false)
        );

        $this->getOrderRepository()->save($order);
    }


    private function getOrderRepository(): OrderRepositoryInterface
    {
        return $this->orderRepositoryFactory->create();
    }
}
