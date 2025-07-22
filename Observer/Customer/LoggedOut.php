<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer\Customer;

use Magento\Framework\Event\Observer;
use Convertcart\Analytics\Observer\AbstractObserver;

class LoggedOut extends AbstractObserver
{
    protected function executeInternal(Observer $observer): void
    {
        $customer = $observer->getCustomer();
        $eventName = 'loggedOut';
        $eventData = $this->ccModel->getCustomerData($customer);
        $this->ccModel->storeCcEvents($eventName, $eventData);
    }
}
