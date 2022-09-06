<?php
namespace Mocean\MagentoSms\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class OrderSave implements ObserverInterface
{
    public $moceanHelper;
    private $logger;
    public function __construct(\Mocean\MagentoSms\Helper\Data $moceanHelper, \Psr\Log\LoggerInterface $logger)
    {
        $this->moceanHelper = $moceanHelper;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getData('order');
        if ($order && $order->getId() > 0) {
            if ($this->moceanHelper->getIsMoceanapiSent($order->getId()) != 1) {
                if ($this->moceanHelper->getStoreConfig('mocean/messages/sendsmsonneworder') == 1) {
                    $this->logger->info("We're in if condition");
                    $countryCode = $this->moceanHelper->getStoreConfig('general/country/default');
                    $body = $this->moceanHelper->getNewOrderMessage($order);
                    $to = $this->moceanHelper->getStoreConfig('mocean/settings/adminphone');
                    $this->moceanHelper->sendSMS($to, $body, 'New_Order', $order->getId(), $countryCode);
                    $this->moceanHelper->setIsMoceanapiSent($order->getId());
                }
            } elseif ($order->getOrigData('status') != $order->getStatus()) {
                $cpath = 'mocean/messages/sendsmsonship';
                $sendsmsonship = $this->moceanHelper->getStoreConfig($cpath);
                if ($order->hasShipments() && $sendsmsonship== 1) {
                    $address = $order->getShippingAddress();
                    if ($address) {
                        $countryCode = $address->getCountryId();
                        $to = $address->getTelephone();
                    } else {
                        $address = $order->getBillingAddress();
                        $countryCode = $address->getCountryId();
                        $to = $address->getTelephone();
                    }
                    $body = $this->moceanHelper->getShippedStatusMessage($order);
                    $this->moceanHelper->sendSMS($to, $body, 'Shipped_Status', $order->getId(), $countryCode);
                }
            }
        }
    }
}
