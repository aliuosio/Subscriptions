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
        private readonly CustomerCollection                 $customers,
        private readonly CustomerRepositoryInterfaceFactory $customerRepositoryFactory,
    )
    {
    }

    public function getCustomerData(SubscribeCollection $subscribeCollection): array
    {
        $customersData = [];
        foreach ($this->customers->fetchCustomers($this->getCustomerIds($subscribeCollection))->getItems() as $customer) {
            $customersData[$customer->getData('entity_id')] = $customer;
        }

        return $customersData;
    }


    private function getCustomerIds(SubscribeCollection $subscribeCollection): array
    {
        return array_keys($subscribeCollection->getGroupedByCustomer());
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
