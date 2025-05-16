<?php
namespace Noteq\AbandonedCart\Controller\Adminhtml\Abandoned;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Ui\Component\MassAction\Filter;
use Noteq\AbandonedCart\Model\ResourceModel\Abandoned\CollectionFactory;
use Noteq\AbandonedCart\Helper\Data as HelperData;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;

class Send extends Action
{
    protected $filter;
    protected $collectionFactory;
    protected $helperData;
    protected $quoteFactory;
    protected $storeManager;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        HelperData $helperData,
        QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->helperData = $helperData;
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/test.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Abandoned Cart Send Action Executed');

        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $sent = 0;

        foreach ($collection->getItems() as $item) {
            $quote = $this->quoteFactory->create()->loadByIdWithoutStore($item->getQuoteId());
            $store = $this->storeManager->getStore($item->getStoreId());
            $this->helperData->sendEmail($quote, $store);
            $sent++;
        }

        $this->messageManager->addSuccessMessage(__('%1 emails have been sent.', $sent));

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
