<?php


namespace Noteq\AbandonedCart\Logger;


use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    protected $loggerType = Logger::INFO;

    protected $fileName = '/var/log/noteq-abandoned-cart.log';
}
