<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer;

use Magento\Framework\Event\Observer;

class HomepageViewed extends AbstractObserver
{
    protected function executeInternal(Observer $observer): void
    {
        $eventName = 'homepageViewed';
        $eventData = [];
        $this->ccModel->storeCcEvents($eventName, $eventData);
    }
}
