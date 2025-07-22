<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer\Cart;

use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\OrderFactory;
use Convertcart\Analytics\Observer\AbstractObserver;
use Convertcart\Analytics\Logger\Logger;
use Convertcart\Analytics\Model\Cc;

class OrderCompleted extends AbstractObserver
{
    private StoreManagerInterface $storeManager;
    private OrderFactory $salesOrderFactory;

    public function __construct(
        Logger $logger,
        Cc $ccModel,
        StoreManagerInterface $storeManager,
        OrderFactory $salesOrderFactory
    ) {
        parent::__construct($logger, $ccModel);
        $this->storeManager = $storeManager;
        $this->salesOrderFactory = $salesOrderFactory;
    }

    protected function executeInternal(Observer $observer): void
    {
        $orderIds = $observer->getData('order_ids');
        if (!is_array($orderIds) || empty($orderIds[0])) {
            return;
        }
        $eventData = [];
        $eventData['items'] = [];
        $store = $this->storeManager->getStore();
        $currency = is_object($store) ? $store->getCurrentCurrencyCode() : null;
        $order = $this->salesOrderFactory->create()->load($orderIds[0]);
        if (!is_object($order)) {
            return;
        }
        foreach ($order->getAllVisibleItems() as $item) {
            $orderItem = [];
            $orderItem['name'] = str_replace("'", "", $item->getName());
            $orderItem['price'] = $item->getPrice();
            $orderItem['currency'] = $currency;
            $orderItem['quantity'] = $item->getQtyOrdered();
            $orderItem['id'] = $item->getProductId();
            $orderItem['sku'] = $item->getSku();
            $product = $item->getProduct();
            if (is_object($product)) {
                $orderItem['url'] = $product->getProductUrl();
            }
            $eventData['items'][] = $orderItem;
        }
        $eventData['orderId'] = $order->getIncrementId();
        $eventData['order_email'] = $order->getCustomerEmail();
        $eventData['currency'] = $currency;
        $eventData['is_guest'] = $order->getCustomerIsGuest();
        $eventData['coupon_code'] = $order->getCouponCode();
        $eventData['shipping_method'] = $order->getShippingDescription();
        $eventData['payment_method'] = $order->getPayment()->getMethod();
        $eventData['status'] = $order->getStatus();
        $eventData['total'] = $order->getGrandTotal();

        $eventName = 'orderCompleted';
        $this->ccModel->storeCcEvents($eventName, $eventData);
    }
}
