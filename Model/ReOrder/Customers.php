<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ReOrder;

use Magento\Customer\Model\ResourceModel\Customer\Collection as customerCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as customerCollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;

class Customers
{

    public function __construct(
        private readonly customerCollectionFactory $customerCollectionFactory
    ) {
    }

    private function getCustomerCollection(): customerCollection
    {
        return $this->customerCollectionFactory->create();
    }

    public function fetchCustomers(array $customerIds): AbstractDb|customerCollection
    {
        return $this->getCustomerCollection()->addFieldToFilter('entity_id', ['in' => $customerIds]);
    }
}
