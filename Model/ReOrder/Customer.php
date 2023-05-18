<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ReOrder;

use Magento\Customer\Api\CustomerRepositoryInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Osio\Subscriptions\Model\ResourceModel\Customers\Collection as CustomerCollection;
use Osio\Subscriptions\Model\ResourceModel\Subscribe\Collection as SubscribeCollection;

class Customer
{

    public function __construct(
        private readonly SubscribeCollection                $subscribeCollection,
        private readonly CustomerCollection                 $customers,
        private readonly CustomerRepositoryInterfaceFactory $customerRepositoryFactory,
    )
    {
    }

    public function getCustomerData(): array
    {
        $customersData = [];

        foreach ($this->customers->fetchCustomers($this->getCustomerIds())->getItems() as $customer) {
            $customersData[$customer->getData('entity_id')] = $customer;
        }

        return $customersData;
    }


    private function getCustomerIds(): array
    {
        return array_keys($this->subscribeCollection->getGroupedByCustomer());
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function get(int $customerId): CustomerInterface
    {
        return $this->customerRepositoryFactory->create()->getById($customerId);
    }

}
