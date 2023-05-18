<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ResourceModel\Customers;

use Magento\Customer\Model\ResourceModel\Customer\Collection as customerCollection;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\EntityFactory as EntityFactoryAlias;
use Magento\Eav\Model\ResourceModel\Helper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject\Copy\Config as ConfigAlias;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Validator\UniversalFactory;
use Psr\Log\LoggerInterface;

class Collection extends customerCollection
{

    public function __construct(
        EntityFactory          $entityFactory,
        LoggerInterface        $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface       $eventManager,
        Config                 $eavConfig,
        ResourceConnection     $resource,
        EntityFactoryAlias     $eavEntityFactory,
        Helper                 $resourceHelper,
        UniversalFactory       $universalFactory,
        Snapshot               $entitySnapshot,
        ConfigAlias            $fieldsetConfig,
        AdapterInterface       $connection = null,
                               $modelName = self::CUSTOMER_MODEL_NAME
    )
    {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $entitySnapshot,
            $fieldsetConfig,
            $connection,
            $modelName
        );
    }

    public function fetchCustomers(array $customerIds): customerCollection
    {
        return $this->addFieldToSelect('entity_id')
            ->addFieldToFilter('entity_id', ['in' => $customerIds]);
    }
}
