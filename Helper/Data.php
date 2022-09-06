<?php
namespace Mocean\MagentoSms\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Monolog\Handler\StreamHandler;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $logger;
    private $_resource;
    private $_pricingHelper;

    public function __construct(
        Context $context,
        DirectoryList $directory_list,
        ResourceConnection $resource,
        PricingHelper $pricingHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_resource = $resource;
        $this->_pricingHelper = $pricingHelper;
        $this->logger = $logger;
        parent::__construct($context);
    }
    public function getStoreConfig($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getVariables()
    {
        $variables = ['{ORDER-NUMBER}', '{ORDER-TOTAL}', '{ORDER-STATUS}', '{CARRIER-NAME}', '{PAYMENT-NAME}','{CUSTOMER-NAME}', '{CUSTOMER-EMAIL}'];
        return $variables;
    }
    public function getIsMoceanapiSent($order_id)
    {
        $connection= $this->_resource->getConnection();
        $table = $this->_resource->getTableName('sales_order');
        $query = "select is_mocean_send from {$table} where entity_id = ".(int)($order_id);
        return (int)($connection->fetchOne($query));
    }
    public function setIsMoceanapiSent($order_id)
    {
        $connection= $this->_resource->getConnection();
        $table = $this->_resource->getTableName('sales_order');
        $query = "update {$table} set is_mocean_send=1 where entity_id = ".(int)($order_id);
        $connection->query($query);
    }
    public function getIncrementId($order)
    {
        $incrementId = $order->getOriginalIncrementId();
        if ($incrementId == null || empty($incrementId) || !$incrementId) {
            $incrementId = $order->getIncrementId();
        }
        return $incrementId;
    }
    public function getNewOrderMessage($order)
    {

        $variables = $this->getVariables();
        $values =  [ $this->getIncrementId($order), strip_tags($this->_pricingHelper->currency($order->getGrandTotal())), $order->getStatus(), $order->getShippingDescription(), $order->getPayment()->getMethodInstance()->getTitle(), $order->getCustomerFirstname().' '.$order->getCustomerLastname(), $order->getCustomerEmail() ];
        $message = $this->getStoreConfig('mocean/messages/sendsmsonnewordermessage');
        return  str_replace($variables, $values, $message);
    }
    public function getShippedStatusMessage($order)
    {
        $variables = $this->getVariables();
        $values =  [ $this->getIncrementId($order), strip_tags($this->_pricingHelper->currency($order->getGrandTotal())), $order->getStatus(), $order->getShippingDescription(), $order->getPayment()->getMethodInstance()->getTitle(), $order->getCustomerFirstname().' '.$order->getCustomerLastname(), $order->getCustomerEmail() ];
        $message = $this->getStoreConfig('mocean/messages/sendsmsonshipmessage');
        return  str_replace($variables, $values, $message);
    }
    public function sendSMS($to, $body, $action, $id_order, $countryCode)
    {
        $source = 'magentov2';
        $from = $this->getStoreConfig('mocean/settings/sender');
        $from = preg_replace('/\s+/', '', $from);
        $from = substr($from, 0, 11);

        $username = $this->getStoreConfig('mocean/settings/username');
        $password = $this->getStoreConfig('mocean/settings/password');
        $message = ['mocean-medium' => $source, 'mocean-api-key' => $username, 'mocean-api-secret' => $password, 'mocean-from' => $from, 'mocean-to' => $to, 'mocean-text' => $body, 'mocean-resp-format' => "JSON"];

        // set URL and other appropriate options
        $options = [CURLOPT_URL => "https://rest.moceanapi.com/rest/2/sms", CURLOPT_HEADER => false, CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($message), CURLOPT_RETURNTRANSFER => true ];
        // create a new cURL resource
        // problem here
        $ch = curl_init();
        curl_setopt_array($ch, $options);

        // grab URL and pass it to the browser
        $response = curl_exec($ch);
        $this->logger->info(print_r($response, 1));
        if (!$response) {
            throw new \Magento\Framework\Exception\LocalizedException(curl_error($ch));
        }
        curl_close($ch);
    }
}
