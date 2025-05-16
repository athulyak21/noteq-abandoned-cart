<?php

namespace Noteq\AbandonedCart\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Store\Api\Data\StoreInterface;
use Noteq\AbandonedCart\Model\ResourceModel\AbandonedResource;
use Noteq\AbandonedCart\Model\AbandonedModelFactory;
use Throwable;
use Noteq\AbandonedCart\Logger\Logger;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * @var StateInterface
     */
    private StateInterface $inlineTranslation;

    /**
     * @var AbandonedResource
     */
    private AbandonedResource $abandonedResource;

    /**
     * @var AbandonedModelFactory
     */
    private AbandonedModelFactory $abandonedModelFactory;

    /**
     * @var Logger
     */
    private Logger $logger;

    protected $scopeConfig;

    /**
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $state
     * @param AbandonedResource $abandonedResource
     * @param AbandonedModelFactory $abandonedModelFactory
     * @param Logger $logger
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        StateInterface $state,
        AbandonedResource $abandonedResource,
        AbandonedModelFactory $abandonedModelFactory,
        Logger $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $state;
        $this->abandonedModelFactory = $abandonedModelFactory;
        $this->abandonedResource = $abandonedResource;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param StoreInterface $store
     * @param Collection $collection
     * @param $supportEmail
     * @param $supportEmailName
     *
     * @return void
     */
    public function sendEmails(StoreInterface $store, Collection $collection): void
    {
        /** @var Quote $cart */
        foreach ($collection as $cart) {
            try {
                $alreadySent = $this->abandonedResource->checkIfEmailSent($cart->getId());
                if ($alreadySent) {
                    continue;
                }
                $this->sendEmail($cart, $store);
                $abandonedCartModel = $this->abandonedModelFactory
                    ->create()
                    ->setQuoteId($cart->getId())
                    ->setStoreId($cart->getStoreId())
                    ->setEmail($cart->getCustomerEmail())
                    ->setCustomerId((int) $cart->getCustomerId())
                    ->setEmailSent(1)
                    ->setLastSentAt($this->dateTime->date('Y-m-d H:i:s'))
                    ->setCreatedAt($this->dateTime->date('Y-m-d H:i:s'));
                $this->abandonedResource->save($abandonedCartModel);
            } catch (Throwable $e) {
                $this->logger->info('Error in AbandonedHelper: ' . $e->getMessage());
                continue;
            }
        }
    }

    /**
     * @param Quote $cart
     * @param StoreInterface $store
     * @param $supportEmail
     * @param $supportEmailName
     *
     * @return void
     *
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendEmail(Quote $cart, StoreInterface $store): void
    {
        $this->inlineTranslation->suspend();
        $transport = $this->transportBuilder
            ->setTemplateIdentifier(
                'abandoned_cart_email_template'
            )
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $store->getId()
                ]
            )
            ->setTemplateVars(
                [
                    'store_name' => $store->getName(),
                    'cart_url' => $store->getUrl('checkout/cart'),
                    'items_count' => $cart->getItemsCount(),
                    'cart_id' => $cart->getId()
                ]
            )
            ->setFrom($this->getEmailSenderData())
            ->addTo($cart->getCustomerEmail())
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }

    public function getEmailSenderData()
    {

        $senderName = $this->scopeConfig->getValue(
            'trans_email/ident_sales/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $senderEmail = $this->scopeConfig->getValue(
            'trans_email/ident_sales/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return ['name' => $senderName, 'email' => $senderEmail];
    }
}
