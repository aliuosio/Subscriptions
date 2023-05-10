<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Model\ResourceModel\Subscribe;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Osio\Subscriptions\Helper\Data as Helper;
use Osio\Subscriptions\Model\ResourceModel\Subscribe as ResourceModelSubscribe;
use Osio\Subscriptions\Model\Subscribe;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;
use Exception;

class Collection extends AbstractCollection
{
    protected string $idFieldName = 'id';
    protected string $eventPrefix = 'osio_subscriptions_subscribe_collection';
    protected string $eventObject = 'subscribe_collection';
    private Helper $helper;

    public function __construct(
        Helper                 $helper,
        EntityFactoryInterface $entityFactory,
        LoggerInterface        $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface       $eventManager,
        AdapterInterface       $connection = null,
        AbstractDb             $resource = null
    ) {
        $this->helper = $helper;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(Subscribe::class, ResourceModelSubscribe::class);
    }

    private function getSubscriptionsGroupedByCustomerQuery(): array
    {
        $this->addFieldToFilter('next_order_date', ['lteq' => new Zend_Db_Expr('NOW()')]);
        $this->getSelect()->columns(['item_ids' => new Zend_Db_Expr('GROUP_CONCAT(item_id)')]);
        $this->getSelect()->group('customer_id');

        return $this->toArray(['customer_id', 'item_ids']);
    }

    private function getCustomerItems(): array
    {
        return $this->getSubscriptionsGroupedByCustomerQuery()['items'];
    }

    public function getGroupedByCustomer(): array
    {
        $result = [];
        foreach ($this->getCustomerItems() as $item) {
            $result[$item['customer_id']] = array_map('intval', explode(',', $item['item_ids']));
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    public function updateSubscriptionsAfterReOrder(array $result): void
    {
        try {
            $this->getConnection()->beginTransaction();
            foreach ($this->getSubscriptionsToUpdate($result)->getItems() as $item) {
                $this->getConnection()->update(
                    'subscriptions',
                    [
                        'next_order_date' => $this->helper->getNextDateTime((int)$item->getData('period')),
                        'last_order_date' => date('Y-m-d H:i:s', time())
                    ],
                    ['item_id IN (?)' => $item->getData('item_id')]
                );
            }
            $this->getConnection()->commit();
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

    private function getSubscriptionsToUpdate(array $result): Collection
    {
        return $this->addFieldToSelect(['item_id', 'period'])
            ->addFieldToFilter('item_id', ['in' => $result]);
    }
}
