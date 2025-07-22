<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer\Cart;

use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session;
use Convertcart\Analytics\Observer\AbstractObserver;
use Convertcart\Analytics\Logger\Logger;
use Convertcart\Analytics\Model\Cc;

class CartViewed extends AbstractObserver
{
    private StoreManagerInterface $storeManager;
    private Session $checkoutSession;

    public function __construct(
        Logger $logger,
        Cc $ccModel,
        StoreManagerInterface $storeManager,
        Session $checkoutSession
    ) {
        parent::__construct($logger, $ccModel);
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
    }

    protected function executeInternal(Observer $observer): void
    {
        $eventName = 'cartViewed';
        $eventData = [];
        if (!empty($this->checkoutSession) && is_object($this->checkoutSession)) {
            $store = $this->storeManager->getStore();
            $currency = is_object($store) ? $store->getCurrentCurrencyCode() : null;
            $eventData = $this->ccModel->getCartEventData($this->checkoutSession->getQuote(), $currency);
        }
        $this->ccModel->storeCcEvents($eventName, $eventData);
    }
}
