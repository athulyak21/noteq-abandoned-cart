<?php

namespace Noteq\AbandonedCart\Controller\Adminhtml\Abandoned;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Noteq\AbandonedCart\Controller\Adminhtml\Abandoned;

class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = Abandoned::ADMIN_RESOURCE;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute method required for all controllers
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Noteq_AbandonedCart::abandoned_cart');
        $resultPage->getConfig()->getTitle()->prepend(__('Notified Abandoned Carts'));
        return $resultPage;
    }
}
