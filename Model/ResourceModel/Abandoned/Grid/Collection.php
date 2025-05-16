<?php

namespace Noteq\AbandonedCart\Model\ResourceModel\Abandoned\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 */
class Collection extends SearchResult
{
    /**
     * Constructor
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'wac_order_review',
        $resourceModel = \Noteq\AbandonedCart\Model\ResourceModel\AbandonedCart::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
    
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['so' => $this->getTable('sales_order')],
            'main_table.quote_id = so.quote_id',
            ['is_purchased' => new \Zend_Db_Expr('IF(so.entity_id IS NOT NULL, 1, 0)')]
        );
        
        $this->getSelect()->joinLeft(
            ['q' => $this->getTable('quote')],
            'main_table.quote_id = q.entity_id',
            [
                'items_qty' => 'q.items_qty',
                'grand_total' => 'q.grand_total'
            ]
        );
        return $this;
    }

}
