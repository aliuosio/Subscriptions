<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model;

use Exception;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Osio\Subscriptions\Model\ReOrder\Address;
use Osio\Subscriptions\Model\ReOrder\Items;
use Osio\Subscriptions\Model\ReOrder\Note;
use Osio\Subscriptions\Model\ReOrder\Payment;
use Osio\Subscriptions\Model\ReOrder\Shipping;
use Magento\Quote\Api\CartManagementInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Osio\Subscriptions\Model\ReOrder\Customer;
use Osio\Subscriptions\Model\ResourceModel\Subscribe\Collection as SubscribeCollection;

class ReOrder
{

    public function __construct(
        private readonly CartRepositoryInterfaceFactory  $quoteRepositoryFactory,
        private readonly OrderRepositoryInterfaceFactory $orderRepositoryFactory,
        private readonly CartManagementInterfaceFactory  $quoteManagementFactory,
        private readonly OrderSender                     $orderSender,
        private readonly Address                         $address,
        private readonly Shipping                        $shipping,
        private readonly Payment                         $payment,
        private readonly Note                            $note,
        private readonly Items                           $items,
        private readonly Customer                        $customer,
        private readonly SubscribeCollection             $subscribeCollection,
    )
    {
    }


    /**
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws LocalizedException
     * @throws Exception
     */
    public function execute(): array
    {
        $result = [];
        foreach ($this->subscribeCollection->getGroupedByCustomer() as $customerId => $itemIds) {
            $result = array_merge($result, $this->set($customerId, $itemIds));
        }

        if (!empty($result)) {
            $this->subscribeCollection->updateSubscriptionsAfterReOrder($result);
        }

        return $result;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function set(int $customerId, array $itemIds): array
    {
        $result = [];
        $quote = $this->items->set($itemIds, $customerId);


        if (isset($quote) && isset($this->customer->getCustomerData()[$customerId])) {
            $quote = $this->address->set($quote, $customerId);
            $quote = $this->shipping->set($quote);
            $quote = $this->payment->set($quote);

            $quote->assignCustomer($this->customer->get($customerId))
                ->setStoreId($this->customer->getCustomerData()[$customerId]->getStoreId());

            $this->quoteRepositoryFactory->create()
                ->save($quote);
            $order = $this->quoteManagementFactory->create()
                ->submit($quote);
            $this->orderSender->send($order);
            $this->note->add($order);
            $this->orderRepositoryFactory->create()
                ->save($order);

            return array_merge($result, $itemIds);
        }

        return $result;
    }

}
