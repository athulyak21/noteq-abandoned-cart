<?php

namespace Noteq\AbandonedCart\Model\Commands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Noteq\AbandonedCart\Model\CronProcessor as SendEmailToCustomer;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;

class SendNotification extends Command
{
    protected $appState;
    protected $SendEmailToCustomer;

    public function __construct(
        SendEmailToCustomer $SendEmailToCustomer,
        State $appState,

    ){
        $this->appState = $appState;
        $this->SendEmailToCustomer = $SendEmailToCustomer;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('abandonedcart:notification')
             ->setDescription('Send Notification for Customer Abandoned Cart');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode(Area::AREA_GLOBAL);

        $this->SendEmailToCustomer->runCronjobTask();

        return 0;
    }
}