<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer;

use Magento\Framework\Event\Observer;

class ContentpageViewed extends AbstractObserver
{
    protected function executeInternal(Observer $observer): void
    {
        $eventName = 'contentPageViewed';
        $eventData = [];
        $this->ccModel->storeCcEvents($eventName, $eventData);
    }
}
