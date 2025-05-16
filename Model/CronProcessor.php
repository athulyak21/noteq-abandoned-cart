<?php

namespace Noteq\AbandonedCart\Model;

use Noteq\AbandonedCart\Helper\Data as AbandonedHelper;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as CronCollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use DateInterval;
use DateTime;
use DateTimeZone;
use Throwable;
use Noteq\AbandonedCart\Logger\Logger;

class CronProcessor
{
    /**
     * @var QuoteCollectionFactory
     */
    private QuoteCollectionFactory $quoteCollectionFactory;

    /**
     * @var AbandonedHelper
     */
    private AbandonedHelper $abandonedHelper;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;
    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @param AbandonedHelper $abandonedHelper
     * @param QuoteCollectionFactory $quoteCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        AbandonedHelper $abandonedHelper,
        QuoteCollectionFactory $quoteCollectionFactory,
        StoreManagerInterface $storeManager,
        Logger $logger
    ) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->abandonedHelper = $abandonedHelper;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function runCronjobTask(): void
    {
        try {
            foreach ($this->storeManager->getStores() as $store) {
                $cartAbandonmentPeriod = $store->getConfig('wac_abandoned_configuration/abandoned_cart/duration');
                $isEnabled = $store->getConfig('wac_abandoned_configuration/abandoned_cart/enabled');
                if (empty($cartAbandonmentPeriod) || ($isEnabled != 1)) {
                    return;
                }

                $quoteCollection = $this->getActiveCartsForStore($store, $cartAbandonmentPeriod);
                if ($quoteCollection && $quoteCollection->getSize()) {
                    $this->abandonedHelper->sendEmails($store, $quoteCollection);
                }
            }
        } catch (Throwable $e) {
            $this->logger->info('Error in CronProcessor: ' . $e->getMessage());
        }
    }

    /**
     * @return QuoteCollection
     */
    private function getQuoteCollection(): QuoteCollection
    {
        return $this->quoteCollectionFactory->create();
    }

    /**
     * @param StoreInterface $store
     * @param string $cartAbandonmentPeriod
     *
     * @return Collection|boolean
     */
    private function getActiveCartsForStore(
        StoreInterface $store,
        string $cartAbandonmentPeriod
    ): bool|Collection {
        try {
            $cutoffTime = (new DateTime('now', new DateTimeZone('UTC')))
                ->sub(new DateInterval(sprintf('PT%sM', $cartAbandonmentPeriod)));

            $collection = $this->getQuoteCollection()
                ->addFieldToFilter('main_table.store_id', $store->getId())
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('items_count', ['gt' => 0])
                ->addFieldToFilter('customer_email', ['notnull' => true])
                ->addFieldToFilter(
                    'main_table.updated_at',
                    ['lteq' => $cutoffTime->format('Y-m-d H:i:s')]
                );
            return $collection;
        } catch (Throwable $e) {
            $this->logger->info('Error in getActiveCartsForStore: ' . $e->getMessage());
            return false;
        }
    }
}
