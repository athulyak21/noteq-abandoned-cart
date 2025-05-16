<?php


namespace Noteq\AbandonedCart\Model\ResourceModel\Abandoned;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
	protected $_eventPrefix = 'Wac_AbandonedCart_Abandoned';
	protected $_eventObject = 'abandoned_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Noteq\AbandonedCart\Model\AbandonedModel',
            'Noteq\AbandonedCart\Model\ResourceModel\AbandonedResource'
        );
    }
}
