<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer\Customer;

use Magento\Framework\Event\Observer;
use Convertcart\Analytics\Observer\AbstractObserver;

class CustomerRegistered extends AbstractObserver
{
    protected function executeInternal(Observer $observer): void
    {
        $customer = $observer->getCustomer();
        $eventName = 'registered';
        $eventData = $this->ccModel->getCustomerData($customer);
        $this->ccModel->storeCcEvents($eventName, $eventData);
    }
}
