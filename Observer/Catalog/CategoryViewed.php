<?php
declare(strict_types=1);

namespace Convertcart\Analytics\Observer\Catalog;

use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Convertcart\Analytics\Observer\AbstractObserver;
use Convertcart\Analytics\Logger\Logger;
use Convertcart\Analytics\Model\Cc;

class CategoryViewed extends AbstractObserver
{
    private Registry $registry;

    public function __construct(
        Logger $logger,
        Cc $ccModel,
        Registry $registry
    ) {
        parent::__construct($logger, $ccModel);
        $this->registry = $registry;
    }

    protected function executeInternal(Observer $observer): void
    {
        $eventName = 'categoryViewed';
        $eventData = [];
        $category = $this->registry->registry('current_category');
        if (is_object($category)) {
            $eventData['name'] = $category->getName();
            $eventData['id'] = $category->getId();
            $eventData['url'] = $category->getUrl();
        }
        $this->ccModel->storeCcEvents($eventName, $eventData);
    }
}
