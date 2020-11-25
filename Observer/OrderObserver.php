<?php

namespace BlueMedia\BluePayment\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use BlueMedia\BluePayment\Model\ResourceModel\Gateways\Collection as GatewaysCollection;

class OrderObserver implements ObserverInterface
{
    /** @var GatewaysCollection */
    public $gatewayCollection;

    public function __construct(GatewaysCollection $gatewayCollection)
    {
        $this->gatewayCollection = $gatewayCollection;
    }

    public function execute(Observer $observer)
    {
        /** @var DataObject $transportObject */
        $transportObject = $observer->getEvent()->getTransportObject();

        if ($transportObject && $transportObject->getData('order')) {
            /** @var Order $order */
            $order = $transportObject->getData('order');
            /** @var Order\Payment $payment */
            $payment = $order->getPayment();
            if ($payment && $payment->getAdditionalInformation('bluepayment_gateway')) {
                $gatewayId = $payment->getAdditionalInformation('bluepayment_gateway');

                $gateway = $this->gatewayCollection
                    ->addFieldToFilter('gateway_id', $gatewayId)
                    ->addFieldToFilter('gateway_currency', $order->getOrderCurrencyCode())
                    ->getFirstItem();

                $transportObject->setData('payment_channel', $gateway->getData('gateway_name'));
            }
        }
    }
}
