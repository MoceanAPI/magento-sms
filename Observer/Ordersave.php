<?php
namespace Mocean\Sms\Observer;

use Magento\Framework\Event\ObserverInterface;

class Ordersave implements ObserverInterface
{
    public $moceanHelper;
    public function __construct(\Mocean\Sms\Helper\Data $moceanHelper)
    {
        $this->moceanHelper = $moceanHelper;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getData('order');
            if ($order && $order->getId() > 0) {
                if ($this->moceanHelper->getIsMoceanapiSent($order->getId()) != 1) {
                    if ($this->moceanHelper->getStoreConfig('mocean/messages/sendsmsonneworder') == 1) {
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
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
		throw new \Magento\Framework\Exception\LocalizedException($e->getMessage());
        }
    }
}
