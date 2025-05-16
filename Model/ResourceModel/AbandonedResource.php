<?php

namespace Noteq\AbandonedCart\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Throwable;

class AbandonedResource extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init('wac_abandoned_cart', 'id');
    }

    public function checkIfEmailSent(int $quoteId): bool
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), ['email_sent'])
            ->where('quote_id = :quote_id')
            ->limit(1);

        $bind = ['quote_id' => $quoteId];

        $result = $connection->fetchOne($select, $bind);

        return (bool)$result; // true if email_sent = 1
    }
}
