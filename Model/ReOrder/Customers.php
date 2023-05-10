<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ReOrder;

use Magento\Customer\Model\ResourceModel\Customer\Collection as customerCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as customerCollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;

class Customers
{

    public function __construct(
        private readonly customerCollectionFactory $customerCollectionFactory,
    ) {
    }

    private function getCustomerCollection(): customerCollection
    {
        $collection = $this->customerCollectionFactory->create();
        $collection->getSelect()->join(
            ['address' => 'customer_address_entity'],
            'e.entity_id = address.parent_id',
            ['address.*']
        );
        $collection->addFieldToSelect('entity_id');

        return $collection;
    }

    public function fetchCustomers(array $customerIds): AbstractDb|customerCollection
    {
        $collection = $this->getCustomerCollection();
        $collection->addFieldToSelect('entity_id');
        $collection->addFieldToFilter('entity_id', ['in' => $customerIds]);

        return $collection;
    }
}
