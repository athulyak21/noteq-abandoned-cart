<?php

namespace Noteq\AbandonedCart\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Abandoned extends Action
{
    /**
     * @inheritDoc
     */
    const ADMIN_RESOURCE = 'Noteq_AbandonedCart::AbandonedCart';
}
